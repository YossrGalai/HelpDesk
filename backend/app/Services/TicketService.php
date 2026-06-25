<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TicketService
{
    /**
     * @var TicketHistoryService
     */
    private $historyService;

    public function __construct(TicketHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Liste paginée avec scoping par rôle.
     * Admin → tous les tickets.
     * Agent → tickets qui lui sont assignés.
     * User  → tickets qu'il a créés.
     */
    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['creator', 'assignee']);

        if ($user->hasRole(UserRole::ADMIN)) {
            // admin voit tout
        } elseif ($user->hasRole(UserRole::AGENT)) {
            $query->where('assigned_to', $user->id);
        } else {
            $query->where('created_by', $user->id);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Trouver un ticket par ID (avec relations).
     */
    public function find(int $id): Ticket
    {
        return Ticket::with([
            'creator',
            'assignee',
            'comments.user',
            'histories.changedBy',
        ])->findOrFail($id);
    }

    /**
     * Créer un ticket pour l'utilisateur connecté.
     */
    public function create(User $user, array $data): Ticket
    {
        $ticket = Ticket::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'priority'    => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'created_by'  => $user->id,
            'status'      => TicketStatus::OPEN,
        ]);

        return $ticket->load(['creator', 'assignee']);
    }

    /**
     * Mettre à jour un ticket (partial update).
     */
    public function update(User $user, Ticket $ticket, array $data): Ticket
    {
        $this->authorizeModify($user, $ticket);

        $watchedFields = ['title', 'description', 'priority', 'status', 'assigned_to'];
        $original      = $ticket->only($watchedFields);

        // Agent : peut seulement changer le status
        if ($user->hasRole(UserRole::AGENT) && !$user->hasRole(UserRole::ADMIN)) {
            $data = array_intersect_key($data, array_flip(['status']));
        }

        $fields = ['title', 'description', 'priority', 'status'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $ticket->$field = $data[$field];
            }
        }

        if (array_key_exists('assigned_to', $data)) {
            $ticket->assigned_to = $data['assigned_to'];
        }

        $ticket->save();
        $this->historyService->record($user, $ticket, $original, $watchedFields);

        return $ticket->fresh(['creator', 'assignee']);
    }

    /**
     * Fermer un ticket (status → CLOSED).
     *
     * Règles :
     * - Seuls admin et agent peuvent fermer un ticket.
     * - Le dernier commentaire (ou la création du ticket) doit dater
     *   d'au moins MIN_DAYS_BEFORE_CLOSE jours.
     *
     * @throws AuthorizationException
     */
    public function close(User $user, Ticket $ticket): Ticket
    {
        // ── Règle 1 : seuls admin et agent peuvent fermer ────────────────────
        if (!$user->hasRole(UserRole::ADMIN) && !$user->hasRole(UserRole::AGENT)) {
            throw new AuthorizationException(
                'Seuls les agents et les administrateurs peuvent fermer un ticket.'
            );
        }

        // ── Règle 2 : l'agent doit être assigné au ticket ────────────────────
        if ($user->hasRole(UserRole::AGENT) && !$user->hasRole(UserRole::ADMIN)) {
            if ($ticket->assigned_to !== $user->id) {
                throw new AuthorizationException(
                    'Vous pouvez seulement fermer les tickets qui vous sont assignés.'
                );
            }
        }

        // ── Règle 3 : délai minimum de 3 jours depuis la dernière activité ───
        $this->enforceMinInactivityBeforeClose($ticket);

        $original = $ticket->only(['status']);

        $ticket->status = TicketStatus::CLOSED;
        $ticket->save();

        $this->historyService->record($user, $ticket, $original, ['status']);

        return $ticket->fresh(['creator', 'assignee']);
    }

    /**
     * Assigner un ticket à un utilisateur.
     */
    public function assign(User $user, Ticket $ticket, int $assigneeId): Ticket
    {
        $original = $ticket->only(['assigned_to', 'status']);

        $ticket->assigned_to = $assigneeId;

        if ($ticket->status === TicketStatus::OPEN) {
            $ticket->status = TicketStatus::IN_PROGRESS;
        }

        $ticket->save();
        $this->historyService->record($user, $ticket, $original, ['assigned_to', 'status']);

        return $ticket->fresh(['creator', 'assignee']);
    }

    /**
     * Changer la priorité d'un ticket.
     */
    public function setPriority(User $user, Ticket $ticket, string $priority): Ticket
    {
        $original = $ticket->only(['priority']);

        $ticket->priority = $priority;
        $ticket->save();

        $this->historyService->record($user, $ticket, $original, ['priority']);

        return $ticket->fresh(['creator', 'assignee']);
    }

    /**
     * Fermeture automatique des tickets inactifs depuis AUTO_CLOSE_DAYS jours.
     * À appeler depuis un scheduler (ex: artisan schedule:run).
     *
     * Retourne le nombre de tickets fermés automatiquement.
     */
    public function autoCloseInactiveTickets(): int
    {
        $cutoff = Carbon::now()->subDays(self::AUTO_CLOSE_DAYS);
        $closed = 0;

        // Tickets ouverts ou en cours dont la dernière activité dépasse le seuil
        $tickets = Ticket::whereIn('status', [
                TicketStatus::OPEN,
                TicketStatus::IN_PROGRESS,
            ])
            ->where('updated_at', '<=', $cutoff)
            ->get();

        foreach ($tickets as $ticket) {
            // Vérifier aussi la date du dernier commentaire
            $lastActivity = $this->getLastActivityDate($ticket);

            if ($lastActivity->lte($cutoff)) {
                $ticket->status = TicketStatus::CLOSED;
                $ticket->save();
                $closed++;
            }
        }

        return $closed;
    }

    // ── Constantes ────────────────────────────────────────────────────────────

    /** Nombre de jours d'inactivité minimum avant fermeture manuelle */
    const MIN_DAYS_BEFORE_CLOSE = 3;

    /** Nombre de jours d'inactivité avant fermeture automatique */
    const AUTO_CLOSE_DAYS = 7;

    // ── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Retourne la date de la dernière activité (dernier commentaire ou création).
     */
    private function getLastActivityDate(Ticket $ticket): Carbon
    {
        // Charger le dernier commentaire si pas déjà chargé
        $lastComment = $ticket->comments()->latest()->first();

        if ($lastComment) {
            return Carbon::parse($lastComment->created_at);
        }

        return Carbon::parse($ticket->created_at);
    }

    /**
     * Vérifie que la dernière activité date d'au moins MIN_DAYS_BEFORE_CLOSE jours.
     *
     * @throws AuthorizationException
     */
    private function enforceMinInactivityBeforeClose(Ticket $ticket): void
    {
        $lastActivity  = $this->getLastActivityDate($ticket);
        $daysSinceLast = $lastActivity->diffInDays(Carbon::now());

        if ($daysSinceLast < self::MIN_DAYS_BEFORE_CLOSE) {
            $remaining = self::MIN_DAYS_BEFORE_CLOSE - $daysSinceLast;
            $word      = $remaining > 1 ? 'jours' : 'jour';

            throw new AuthorizationException(
                "Ce ticket ne peut pas encore être fermé. " .
                "Il faut attendre encore {$remaining} {$word} depuis la dernière activité " .
                "(minimum " . self::MIN_DAYS_BEFORE_CLOSE . " jours requis)."
            );
        }
    }

    /**
     * Règles d'autorisation de modification :
     * Admin  → tous les tickets.
     * Agent  → seulement ses tickets assignés.
     * User   → seulement ses propres tickets.
     *
     * @throws AuthorizationException
     */
    private function authorizeModify(User $user, Ticket $ticket): void
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return;
        }

        if ($user->hasRole(UserRole::AGENT) && $ticket->assigned_to === $user->id) {
            return;
        }

        if ($ticket->created_by === $user->id) {
            return;
        }

        throw new AuthorizationException('You are not allowed to modify this ticket.');
    }
}

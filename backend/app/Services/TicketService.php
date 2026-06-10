<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketService
{
    /**
     * Paginated list of tickets.
     * Admin → tous les tickets.
     * Utilisateur → seulement les siens.
     *
     * @param  User   $user
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['creator', 'assignee']);

        if (! $this->isAdmin($user)) {
            $query->where('created_by', $user->id);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Trouver un ticket par ID (avec relations).
     *
     * @param  int  $id
     * @return Ticket
     */
    public function find(int $id): Ticket
    {
        return Ticket::with(['creator', 'assignee', 'comments.user'])->findOrFail($id);
    }

    /**
     * Créer un ticket pour l'utilisateur connecté.
     *
     * @param  User   $user
     * @param  array  $data
     * @return Ticket
     */
    public function create(User $user, array $data): Ticket
    {
        $ticket = Ticket::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'priority'    => $data['priority'],
            'assigned_to' => isset($data['assigned_to']) ? $data['assigned_to'] : null,
            'created_by'  => $user->id,
            'status'      => TicketStatus::OPEN,
        ]);

        return $ticket->load(['creator', 'assignee']);
    }

    /**
     * Mettre à jour un ticket (partial update).
     * Seul le créateur ou un admin peut modifier.
     *
     * @param  User    $user
     * @param  Ticket  $ticket
     * @param  array   $data
     * @return Ticket
     *
     * @throws AuthorizationException
     */
    public function update(User $user, Ticket $ticket, array $data): Ticket
    {
        $this->authorizeModify($user, $ticket);

        $fields = ['title', 'description', 'priority', 'status'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $ticket->$field = $data[$field];
            }
        }

        // assigned_to peut être null (désassigner) — on vérifie la présence de la clé
        if (array_key_exists('assigned_to', $data)) {
            $ticket->assigned_to = $data['assigned_to'];
        }

        $ticket->save();

        return $ticket->fresh(['creator', 'assignee']);
    }

    /**
     * Fermer un ticket (status → CLOSED).
     *
     * @param  User    $user
     * @param  Ticket  $ticket
     * @return Ticket
     *
     * @throws AuthorizationException
     */
    public function close(User $user, Ticket $ticket): Ticket
    {
        $this->authorizeModify($user, $ticket);

        $ticket->status = TicketStatus::CLOSED;
        $ticket->save();

        return $ticket->fresh(['creator', 'assignee']);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * @param  User  $user
     * @return bool
     */
    private function isAdmin(User $user): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }

    /**
     * @param  User    $user
     * @param  Ticket  $ticket
     * @return void
     *
     * @throws AuthorizationException
     */
    private function authorizeModify(User $user, Ticket $ticket): void
    {
        if ($ticket->created_by !== $user->id && ! $this->isAdmin($user)) {
            throw new AuthorizationException('You are not allowed to modify this ticket.');
        }
    }
}

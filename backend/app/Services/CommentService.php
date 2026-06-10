<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    /**
     * Retourner tous les commentaires d'un ticket,
     * triés du plus ancien au plus récent (timeline).
     *
     * @param  Ticket  $ticket
     * @return LengthAwarePaginator
     */
    public function list(Ticket $ticket): LengthAwarePaginator
    {
        return $ticket->comments()
            ->with('user')
            ->oldest()
            ->paginate(50);
    }

    /**
     * Ajouter un commentaire à un ticket.
     *
     * @param  User    $user
     * @param  Ticket  $ticket
     * @param  string  $comment
     * @return TicketComment
     */
    public function create(User $user, Ticket $ticket, string $comment): TicketComment
    {
        $ticketComment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'comment'   => $comment,
        ]);

        return $ticketComment->load('user');
    }
}

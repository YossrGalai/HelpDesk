<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;

class TicketHistoryService
{
    /**
     * Comparer les champs avant/après et enregistrer les changements.
     *
     * @param  User    $user
     * @param  Ticket  $ticket
     * @param  array   $original   — snapshot AVANT la modification
     * @param  array   $fields     — champs surveillés
     * @return void
     */
    public function record(User $user, Ticket $ticket, array $original, array $fields): void
    {
        foreach ($fields as $field) {
            $oldValue = $original[$field] ?? null;
            $newValue = $ticket->$field instanceof \BackedEnum
                ? $ticket->$field->value
                : (string) ($ticket->$field ?? '');

            $oldValue = $oldValue instanceof \BackedEnum
                ? $oldValue->value
                : (string) ($oldValue ?? '');

            if ($oldValue !== $newValue) {
                TicketHistory::create([
                    'ticket_id'   => $ticket->id,
                    'changed_by'  => $user->id,
                    'field'       => $field,
                    'old_value'   => $oldValue ?: null,
                    'new_value'   => $newValue ?: null,
                ]);
            }
        }
    }
}

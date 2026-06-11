<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;

class SetPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $valid = implode(',', TicketPriority::values());

        return [
            'priority' => 'required|string|in:' . $valid,
        ];
    }

    public function messages(): array
    {
        $valid = implode(', ', TicketPriority::values());

        return [
            'priority.required' => 'La priorité est obligatoire.',
            'priority.in'       => 'Priorité invalide. Valeurs acceptées : ' . $valid,
        ];
    }
}

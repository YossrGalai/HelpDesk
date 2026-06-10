<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorisation gérée dans TicketService
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'min:5', 'max:255'],
            'description' => ['sometimes', 'string', 'min:10'],
            'priority'    => ['sometimes', Rule::in(TicketPriority::values())],
            'status'      => ['sometimes', Rule::in(TicketStatus::values())],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.min'          => 'Title must be at least 5 characters.',
            'description.min'    => 'Description must be at least 10 characters.',
            'priority.in'        => 'Priority must be one of: ' . implode(', ', TicketPriority::values()) . '.',
            'status.in'          => 'Status must be one of: '   . implode(', ', TicketStatus::values())   . '.',
            'assigned_to.exists' => 'The assigned user does not exist.',
        ];
    }
}

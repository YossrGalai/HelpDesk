<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route déjà protégée par auth:sanctum
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'priority'    => ['required', Rule::in(TicketPriority::values())],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'A ticket title is required.',
            'title.min'            => 'Title must be at least 5 characters.',
            'description.required' => 'A description is required.',
            'description.min'      => 'Description must be at least 10 characters.',
            'priority.required'    => 'A priority level is required.',
            'priority.in'          => 'Priority must be one of: ' . implode(', ', TicketPriority::values()) . '.',
            'assigned_to.exists'   => 'The assigned user does not exist.',
        ];
    }
}

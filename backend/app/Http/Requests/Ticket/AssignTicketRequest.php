<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // contrôle admin fait dans le controller
    }

    public function rules(): array
    {
        return [
            'assigned_to' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.required' => 'Un utilisateur doit être sélectionné.',
            'assigned_to.integer'  => 'L\'identifiant utilisateur doit être un entier.',
            'assigned_to.exists'   => 'Cet utilisateur n\'existe pas.',
        ];
    }
}

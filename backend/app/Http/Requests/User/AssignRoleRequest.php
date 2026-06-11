<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $valid = implode(',', UserRole::values());

        return [
            'role' => 'required|string|in:' . $valid,
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Le rôle est obligatoire.',
            'role.in'       => 'Rôle invalide. Valeurs acceptées : ' . implode(', ', UserRole::values()),
        ];
    }
}

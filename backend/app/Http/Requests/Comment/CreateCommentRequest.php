<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route déjà protégée par auth:sanctum
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'A comment is required.',
            'comment.min'      => 'Comment must be at least 1 character.',
            'comment.max'      => 'Comment cannot exceed 5000 characters.',
        ];
    }
}

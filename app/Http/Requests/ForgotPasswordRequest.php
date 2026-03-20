<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Votre adresse email est nécessaire.',
            'email.email'    => 'Le format de l\'email est invalide.',
            'email.exists'   => 'Aucun compte n\'est associé à cet email.',
        ];
    }
}

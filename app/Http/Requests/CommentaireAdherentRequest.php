<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentaireAdherentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commentaire' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'commentaire.required' => 'Le commentaire est obligatoire.',
            'commentaire.max'      => 'Le commentaire ne peut pas dépasser 2000 caractères.',
        ];
    }
}

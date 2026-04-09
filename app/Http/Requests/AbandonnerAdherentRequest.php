<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbandonnerAdherentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif_sortie' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'motif_sortie.required' => 'Le motif de sortie est obligatoire.',
            'motif_sortie.max'      => 'Le motif ne peut pas dépasser 255 caractères.',
        ];
    }
}

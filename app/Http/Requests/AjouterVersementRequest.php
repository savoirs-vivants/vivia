<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjouterVersementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant_versement' => ['required', 'numeric', 'min:0.01'],
            'source'            => ['nullable', 'string', 'max:100'],
            'date_paiement'     => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'montant_versement.required' => 'Le montant est obligatoire.',
            'montant_versement.numeric'  => 'Le montant doit être un nombre.',
            'montant_versement.min'      => 'Le montant doit être supérieur à 0.',
            'date_paiement.date'         => 'La date de paiement est invalide.',
        ];
    }
}

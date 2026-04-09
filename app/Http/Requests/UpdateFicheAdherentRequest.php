<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFicheAdherentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prenom'                    => 'required|string|max:255',
            'nom'                       => 'required|string|max:255',
            'communication'             => 'boolean',
            'bulletin'                  => 'boolean',
            'manif'                     => 'boolean',
            'date_naiss'                => 'nullable|date',
            'genre'                     => 'nullable|string|max:255',
            'adresse'                   => 'nullable|string|max:255',
            'code_postal'               => 'nullable|string|max:20',
            'ville'                     => 'nullable|string|max:255',
            'tel'                       => 'nullable|string|max:50',
            'mail'                      => 'nullable|email|max:255',
            'occupation'                => 'nullable|string|max:255',
            'etablissement'             => 'nullable|string|max:255',
            'regime_social'             => 'nullable|string|max:255',
            'idee_metier'               => 'nullable|string|max:1000',
            'decouverte_metier'         => 'nullable|string|max:1000',
            'problemes_sante'           => 'nullable|string|max:1000',
            'allergies'                 => 'nullable|string|max:1000',
            'conduite_a_tenir'          => 'nullable|string|max:1000',
            'restrictions_alimentaires' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'prenom.required' => 'Le prénom est obligatoire.',
            'nom.required'    => 'Le nom est obligatoire.',
            'mail.email'      => 'L\'adresse email est invalide.',
            'date_naiss.date' => 'La date de naissance est invalide.',
        ];
    }
}

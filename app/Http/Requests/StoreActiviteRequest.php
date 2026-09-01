<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActiviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'              => 'required|in:activite,stage,club_maker',
            'nom'               => 'required|string|max:255',
            'tarif'             => 'nullable|numeric|min:0',
            'adresse'           => 'nullable|string|max:255',
            'ville'             => 'nullable|string|max:255',
            'jours.*'           => 'nullable|string',
            'debuts.*'          => 'nullable|date_format:H:i',
            'fins.*'            => 'nullable|date_format:H:i',
            'date_debut_club_maker' => 'nullable|date',
            'date_fin_club_maker'   => 'nullable|date|after_or_equal:date_debut_club_maker',
            'gestionnaires'     => 'nullable|array',
            'gestionnaires.*'   => 'exists:users,id',
            'max_eleves'        => 'nullable|integer|min:1',
            'classes'           => 'nullable|array',
            'classes.*'         => 'nullable|string',
            'dossier_action'    => 'nullable|in:none,existing,new',
            'id_dossier'        => 'nullable|exists:dossiers_activite,id',
            'nouveau_dossier'   => 'nullable|string|max:255',
            'sans_horaires'     => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'           => 'Le type est obligatoire.',
            'type.in'                 => 'Le type doit être "activite", "stage" ou "club_maker".',
            'nom.required'            => 'Le nom est obligatoire.',
            'nom.max'                 => 'Le nom ne peut pas dépasser 255 caractères.',
            'tarif.numeric'           => 'Le tarif doit être un nombre.',
            'tarif.min'               => 'Le tarif ne peut pas être négatif.',
            'gestionnaires.*.exists'  => 'Un gestionnaire sélectionné est invalide.',
            'id_dossier.exists'       => 'Le dossier sélectionné est invalide.',
        ];
    }
}

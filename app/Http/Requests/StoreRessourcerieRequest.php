<?php

namespace App\Http\Requests;

use App\Models\Ressourcerie;
use Illuminate\Foundation\Http\FormRequest;

class StoreRessourcerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'                => 'required|string|max:255',
            'description'        => 'nullable|string',
            'condition_location' => 'nullable|string',
            'prix'               => 'nullable|numeric|min:0',
            'type_tarif'         => 'required|in:' . implode(',', array_keys(Ressourcerie::TYPES_TARIF)),
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'      => 'Le nom est obligatoire.',
            'nom.max'           => 'Le nom ne peut pas dépasser 255 caractères.',
            'prix.numeric'      => 'Le prix doit être un nombre.',
            'prix.min'          => 'Le prix ne peut pas être négatif.',
            'type_tarif.required' => 'Le type de tarif est obligatoire.',
            'type_tarif.in'     => 'Le type de tarif sélectionné est invalide.',
        ];
    }
}

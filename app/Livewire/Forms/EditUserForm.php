<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Illuminate\Validation\Rule;

class EditUserForm extends Form
{
    public ?User $user;

    public string $name = '';
    public string $firstname = '';
    public string $email = '';
    public string $structure = '';
    public string $role = '';

    /**
     * Initialise le formulaire avec les données de l'utilisateur à éditer.
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name ?? '';
        $this->firstname = $user->firstname ?? '';
        $this->email = $user->email ?? '';
        $this->structure = $user->structure ?? '';
        $this->role = $user->role ?? '';
    }

    /**
     * Règles de validation.
     */
    protected function rules(): array
    {
        return [
            'name'      => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'structure' => 'nullable|string|max:255',
            'role'      => 'required|string|in:travailleur,gestionnaire,admin',
        ];
    }

    /**
     * Messages d'erreur personnalisés (Optionnel mais recommandé)
     */
    protected function messages(): array
    {
        return [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email'    => 'Le format de l\'email est invalide.',
            'email.unique'   => 'Cette adresse e-mail est déjà utilisée.',
        ];
    }

    /**
     * Met à jour l'utilisateur en base de données.
     */
    public function updateUser(): void
    {
        $this->validate();

        $this->user->update([
            'name'      => $this->name,
            'firstname' => $this->firstname,
            'email'     => $this->email,
            'structure' => $this->structure,
            'role'      => $this->role,
        ]);
    }
}

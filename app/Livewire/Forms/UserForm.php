<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use App\Mail\InvitationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;

class UserForm extends Form
{
    public $name;
    public $firstname;
    public $email;
    public $role;
    public $structure;

    protected function rules(): array
    {
        return [
            'name'      => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'role'      => 'required|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique'   => 'Cette adresse e-mail est déjà utilisée.',
            'role.required'  => 'Le rôle de l\'utilisateur est obligatoire.',
        ];
    }

    public function store()
    {
        $this->validate();

        $token = Str::uuid()->toString();

        $user = User::create([
            'name'             => $this->name,
            'firstname'        => $this->firstname,
            'email'            => $this->email,
            'role'             => $this->role,
            'password'         => Hash::make(Str::random(32)),
            'invitation_token' => $token,
            'is_registered'    => false,
        ]);

        Mail::to($user->email)->send(new InvitationMail($user, $token));

        return $user;
    }
}

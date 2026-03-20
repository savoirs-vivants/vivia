<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Livewire\Forms\EditUserForm;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Vivia - Modifier l\'utilisateur')]
class EditUser extends Component
{
    public User $user;
    public EditUserForm $form;

    public function mount(\App\Models\User $user): void
    {

        $this->user = $user;
        $this->form->setUser($user);
    }

    public function save(): void
    {
        $this->form->updateUser();

        session()->flash('toast_message', 'Utilisateur mis à jour avec succès');
        session()->flash('toast_type', 'success');

        $this->redirect(route('backoffice'));
    }

    public function render()
    {
        return view('livewire.edit-user');
    }
}

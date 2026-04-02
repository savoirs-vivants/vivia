<?php

namespace App\Livewire;

use Livewire\Component;
use App\Livewire\Forms\UserForm;
use Illuminate\Support\Facades\Auth;

class CreateUser extends Component
{
    public UserForm $form;
    public $isOpen = false;

    public function openModal()
    {
        $this->form->reset();
        $this->resetValidation();

        $currentUser = Auth::user();

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->form->reset();
    }

    public function save()
    {
        $user = $this->form->store();

        $email = $user->email;
        $this->closeModal();

        session()->flash('toast_message', 'Invitation envoyée à ' . $email);
        session()->flash('toast_type', 'success');

        return redirect()->route('backoffice');
    }

    public function render()
    {
        return view('livewire.create-user');
    }
}

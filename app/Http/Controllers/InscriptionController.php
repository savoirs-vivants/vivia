<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CompleteInscriptionRequest;

class InscriptionController extends Controller
{
    protected function getUserByToken(string $token)
    {
        return User::where('invitation_token', $token)
            ->where('is_registered', false)
            ->firstOrFail();
    }

    public function show(string $token)
    {
        $user = User::where('invitation_token', $token)
            ->where('is_registered', false)
            ->firstOrFail();

        return view('auth.inscription', compact('user', 'token'));
    }

    public function complete(Request $request, string $token)
    {
        $user = $this->getUserByToken($token);

        $user->update([
            'password'         => Hash::make($request->password),
            'invitation_token' => null,
            'is_registered'    => true,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Votre compte a été activé avec succès !');
    }
}

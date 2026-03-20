<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function showForgot() { return view('auth.forgot-password'); }

    public function sendReset(ForgotPasswordRequest $request)
    {
        $email = $request->validated()['email'];
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        Mail::send('emails.reset-password', ['token' => $token, 'email' => $email], function ($mail) use ($email) {
            $mail->to($email)
                 ->subject('Réinitialisation de votre mot de passe – Usuel');
        });

        return back()->with('success', 'Un email de réinitialisation a été envoyé.');
    }

    public function showReset(string $token, string $email)
    {
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function reset(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $record = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$record || !Hash::check($data['token'], $record->token)) {
            return back()->withErrors(['token' => 'Lien invalide ou expiré.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['token' => 'Ce lien a expiré. Veuillez en demander un nouveau.']);
        }

        User::where('email', $data['email'])->update([
            'password' => Hash::make($data['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
    }
}

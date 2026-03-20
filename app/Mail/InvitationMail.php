<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $token;

    public function __construct(User $user, string $token)
    {
        $this->user  = $user;
        $this->token = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre invitation sur Vivia');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invitation');
    }
}

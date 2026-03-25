<?php

namespace App\Mail;

use App\Models\Adherent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecupNumeroMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Adherent $adherent,
        public string $codeTemporaire
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre numéro d\'adhérent Savoirs Vivants',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recup-numero',
        );
    }
}

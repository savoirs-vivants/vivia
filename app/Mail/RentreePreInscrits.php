<?php

namespace App\Mail;

use App\Models\Adherent;
use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentreePreInscrits extends Mailable
{
    use Queueable, SerializesModels;

    public $adherent;
    public $inscription;

    public function __construct(Adherent $adherent, Inscription $inscription)
    {
        $this->adherent = $adherent;
        $this->inscription = $inscription;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 C\'est la rentrée ! Validez votre inscription à Savoirs Vivants',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rentree_pre_inscrits',
        );
    }
}

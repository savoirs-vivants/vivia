<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnfantAbsent extends Mailable
{
    use Queueable, SerializesModels;

    public $prenomEnfant;
    public $nomActivite;
    public $dateSeance;

    /**
     * Create a new message instance.
     */
    public function __construct($prenomEnfant, $nomActivite, $dateSeance)
    {
        $this->prenomEnfant = $prenomEnfant;
        $this->nomActivite = $nomActivite;
        $this->dateSeance = $dateSeance;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Avis d\'absence : ' . $this->nomActivite,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.enfant-absent',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

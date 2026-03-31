<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoursAnnule extends Mailable
{
    use Queueable, SerializesModels;

    public $nomActivite;
    public $dateSeance;

    /**
     * Create a new message instance.
     */
    public function __construct($nomActivite, $dateSeance)
    {
        $this->nomActivite = $nomActivite;
        $this->dateSeance = $dateSeance;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Annulation de cours : ' . $this->nomActivite,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cours-annule',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\RefillRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReadyForPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    public RefillRequest $refill;

    /**
     * Create a new message instance.
     */
    public function __construct(RefillRequest $refill)
    {
        $this->refill = $refill;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medication Ready for Pickup 📦',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ready-for-pickup',
            with: [
                'userName' => $this->refill->user->name,
                'medicationName' => $this->refill->medication->name,
                'quantity' => $this->refill->quantity,
                'refillId' => $this->refill->id,
            ],
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

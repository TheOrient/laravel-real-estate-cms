<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $listing;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Listing $listing, ?string $reason = null)
    {
        $this->listing = $listing;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'İlanınız Onaylanmadı - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.listing-rejected',
            with: [
                'listing' => $this->listing,
                'reason' => $this->reason,
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

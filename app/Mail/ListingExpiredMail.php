<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingExpiredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $listing;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Listing $listing, $user = null)
    {
        $this->listing = $listing;
        $this->user = $user ?? $listing->user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'İlanınızın Süresi Doldu - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.listing-expired',
            with: [
                'listing' => $this->listing,
                'user' => $this->user,
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

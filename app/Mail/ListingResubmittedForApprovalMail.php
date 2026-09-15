<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingResubmittedForApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $listing;

    /**
     * Create a new message instance.
     */
    public function __construct(Listing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.listing_resubmitted_subject') . ' - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.listing-resubmitted',
            with: [
                'listing' => $this->listing,
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

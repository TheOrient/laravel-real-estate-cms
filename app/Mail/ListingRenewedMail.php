<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ListingRenewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $listing;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Listing $listing, User $user)
    {
        $this->listing = $listing;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('İlanınız Otomatik Yenilendi - ' . config('app.name'))
                    ->view('emails.listing-renewed')
                    ->with([
                        'listing' => $this->listing,
                        'user' => $this->user,
                        'expiresAt' => $this->listing->expires_at->format('d.m.Y H:i'),
                        'listingUrl' => route('listings.show', $this->listing->slug),
                    ]);
    }
}

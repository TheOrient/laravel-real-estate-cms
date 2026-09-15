<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageReceived;
use App\Mail\ContactAutoReplyMail;
use App\Models\Setting;

class ContactController extends Controller
{
    /**
     * Handle contact form submission
     */
    public function store(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'name.required' => 'Ad soyad zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'subject.required' => 'Konu zorunludur.',
            'message.required' => 'Mesaj zorunludur.',
            'message.max' => 'Mesaj en fazla 2000 karakter olabilir.',
        ]);

        // Add IP address for security
        $validated['ip_address'] = $request->ip();

        // Save contact message to database
        $contactMessage = ContactMessage::create($validated);

        // Send email notification to admin (queued)
        try {
            // Bildirim adresi: önce notification_email (siteye basılmaz, yalnız
            // bildirim için), yoksa görünen contact_email, o da yoksa config.
            $adminEmail = trim((string) Setting::get('notification_email'))
                ?: (trim((string) Setting::get('contact_email'))
                    ?: config('mail.admin_email', config('mail.from.address')));
            Mail::to($adminEmail)->queue(new ContactMessageReceived($contactMessage));
        } catch (\Exception $e) {
            // Log email error but don't fail the request
            \Log::error('Contact form email to admin failed: ' . $e->getMessage());
        }

        // Send auto-reply email to user (queued)
        try {
            Mail::to($contactMessage->email)->queue(new ContactAutoReplyMail($contactMessage));
        } catch (\Exception $e) {
            // Log email error but don't fail the request
            \Log::error('Contact form auto-reply failed: ' . $e->getMessage());
        }

        // Redirect back with success message
        return back()->with('success', 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.');
    }
}

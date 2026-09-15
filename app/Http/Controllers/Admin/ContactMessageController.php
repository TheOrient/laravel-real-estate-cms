<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of contact messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query()->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('subject', 'like', "%{$searchTerm}%")
                  ->orWhere('message', 'like', "%{$searchTerm}%");
            });
        }

        $messages = $query->paginate(20);

        // Get counts for each status
        $statusCounts = [
            'all' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
            'archived' => ContactMessage::where('status', 'archived')->count(),
        ];

        return view('admin.contact-messages.index', compact('messages', 'statusCounts'));
    }

    /**
     * Display the specified contact message
     */
    public function show(ContactMessage $contactMessage)
    {
        // Mark as read if it's new
        if ($contactMessage->isNew()) {
            $contactMessage->markAsRead();
        }

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    /**
     * Update the contact message status
     */
    public function update(Request $request, ContactMessage $contactMessage)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,read,replied,archived',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $contactMessage->update($validated);

        return redirect()->back()->with('success', 'Mesaj durumu güncellendi.');
    }

    /**
     * Remove the contact message
     */
    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Mesaj silindi.');
    }

    /**
     * Bulk actions for multiple messages
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:mark_read,mark_replied,archive,delete',
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:contact_messages,id',
        ]);

        $messages = ContactMessage::whereIn('id', $validated['message_ids']);

        switch ($validated['action']) {
            case 'mark_read':
                $messages->update(['status' => 'read', 'read_at' => now()]);
                $successMessage = 'Seçilen mesajlar okundu olarak işaretlendi.';
                break;

            case 'mark_replied':
                $messages->update(['status' => 'replied']);
                $successMessage = 'Seçilen mesajlar yanıtlandı olarak işaretlendi.';
                break;

            case 'archive':
                $messages->update(['status' => 'archived']);
                $successMessage = 'Seçilen mesajlar arşivlendi.';
                break;

            case 'delete':
                $messages->delete();
                $successMessage = 'Seçilen mesajlar silindi.';
                break;
        }

        return redirect()->back()->with('success', $successMessage);
    }
}

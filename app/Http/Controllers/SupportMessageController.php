<?php

namespace App\Http\Controllers;

use App\Services\AdminNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportMessageController extends Controller
{
    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request, AdminNotifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        $user = $request->user();
        $artist = current_artist();

        $notifier->activity('support_message_received', 'New support message: '.$validated['subject'], $validated['message'], route('admin.notifications.index'), [
            'From' => $user->name,
            'Email' => $user->email,
            'Role' => ucfirst($user->role),
            'Artist' => $artist?->artist_name ?? 'Not linked',
            'Subject' => $validated['subject'],
        ]);

        return back()->with('success', 'Your message was sent to TeleMusic support.');
    }
}

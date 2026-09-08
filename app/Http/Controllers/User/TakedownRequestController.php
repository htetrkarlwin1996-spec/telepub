<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TakedownRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class TakedownRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $requests = $request->user()
            ->takedownRequests()
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'song_name' => $item->song_name,
                    'album_name' => $item->album_name,
                    'links' => $item->links,
                    'reason' => $item->reason,
                    'status' => $item->status,
                    'admin_note' => $item->admin_note,
                    'created_at' => optional($item->created_at)->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('User/Takedowns/Index', [
            'requests' => $requests,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'song_name' => ['required', 'string', 'max:255'],
            'album_name' => ['nullable', 'string', 'max:255'],
            'links' => ['required', 'string', 'max:3000'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $takedown = TakedownRequest::create([
            'user_id' => $request->user()->id,
            'song_name' => $validated['song_name'],
            'album_name' => $validated['album_name'] ?? null,
            'links' => $validated['links'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        $adminEmail = config('mail.admin_email');

        if ($adminEmail) {
            Mail::raw(
                "New takedown request received.\n\n"
                . "User: {$request->user()->name}\n"
                . "Email: {$request->user()->email}\n"
                . "Song Name: {$takedown->song_name}\n"
                . "Album Name: {$takedown->album_name}\n"
                . "Links:\n{$takedown->links}\n\n"
                . "Reason:\n{$takedown->reason}\n",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('New Take Down Request');
                }
            );
        }

        return back()->with('status', 'Take down request submitted successfully.');
    }
}
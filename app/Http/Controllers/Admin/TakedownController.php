<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TakedownRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class TakedownController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status');

        $requests = TakedownRequest::query()
            ->with('user')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'song_name' => $item->song_name,
                    'album_name' => $item->album_name,
                    'links' => $item->links,
                    'reason' => $item->reason,
                    'status' => $item->status,
                    'admin_note' => $item->admin_note,
                    'created_at' => optional($item->created_at)->format('Y-m-d H:i'),
                    'updated_at' => optional($item->updated_at)->format('Y-m-d H:i'),
                    'user' => [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                    ],
                ];
            });

        return Inertia::render('Admin/Takedowns/Index', [
            'requests' => $requests,
            'filters' => [
                'status' => $status,
            ],
            'pageStatus' => session('status'),
        ]);
    }

    public function update(Request $request, TakedownRequest $takedown): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,rejected'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $takedown->status;

        $takedown->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        if (
            $oldStatus !== $validated['status']
            && in_array($validated['status'], ['completed', 'rejected'], true)
        ) {
            $this->sendStatusEmail($takedown->fresh('user'));
        }

        return redirect()
            ->route('admin.takedowns.index')
            ->with('status', 'Take down request updated successfully.')
            ->setStatusCode(303);
    }

    private function sendStatusEmail(TakedownRequest $takedown): void
    {
        if (!$takedown->user || !$takedown->user->email) {
            return;
        }

        $subject = $takedown->status === 'completed'
            ? 'Your take down request has been completed'
            : 'Your take down request has been rejected';

        Mail::send('emails.takedown-status', [
            'subjectText' => $subject,
            'title' => $takedown->status === 'completed'
                ? 'Take Down Request Completed'
                : 'Take Down Request Rejected',
            'subtitle' => 'Your request status has been updated.',
            'userName' => $takedown->user->name,
            'songName' => $takedown->song_name,
            'albumName' => $takedown->album_name,
            'requestStatus' => $takedown->status,
            'adminNote' => $takedown->admin_note,
        ], function ($message) use ($takedown, $subject) {
            $message->to($takedown->user->email)
                ->subject($subject);
        });
    }
}
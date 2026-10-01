<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function read(Request $request, DatabaseNotification $notification)
    {
        abort_unless((string) $notification->notifiable_id === (string) $request->user()->id && $notification->notifiable_type === $request->user()::class, 403);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('admin.notifications.index'));
    }
}

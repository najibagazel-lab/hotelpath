<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        $notifications = $user->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    }

    public function unread()
    {
        return response()->json(
            auth()->user()->unreadNotifications()
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'hotel_name' => $notification->data['hotel_name'] ?? '',
                ])
        );
    }

    public function markRead(Request $request, string $notification)
    {
        $item = auth()->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return response()->noContent();
    }
}

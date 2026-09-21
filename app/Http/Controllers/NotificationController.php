<?php

namespace App\Http\Controllers;

use App\Models\ManualNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of active notifications for users
     */
    public function index()
    {
        $notifications = ManualNotification::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Display a specific notification
     */
    public function show(ManualNotification $notification)
    {
        // Only show active notifications
        if (!$notification->is_active) {
            abort(404);
        }

        return view('notifications.show', compact('notification'));
    }
}
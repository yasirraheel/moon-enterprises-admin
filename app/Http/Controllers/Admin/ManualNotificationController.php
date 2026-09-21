<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ManualNotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = ManualNotification::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by user (for user-specific notifications)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get users for filter dropdown (only those with user-specific notifications)
        $users = \App\Models\User::whereHas('manualNotifications')->get();

        return view('admin.manual_notifications.index', compact('notifications', 'users'));
    }

    /**
     * Show the form for creating a new notification
     */
    public function create()
    {
        // Load all users for the dropdown
        $users = \App\Models\User::select('id', 'username', 'phone')
            ->orderBy('username', 'asc')
            ->get();

        return view('admin.manual_notifications.create', compact('users'));
    }

    /**
     * Store a newly created notification
     */
    public function store(Request $request)
    {
        $request->validate([
            'notification_type' => 'required|in:public,user_specific',
            'user_id' => 'required_if:notification_type,user_specific|array',
            'user_id.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['title', 'message', 'is_active']);
        $data['type'] = $request->notification_type;
        $data['action_type'] = $request->notification_type === 'user_specific' ? 'admin_notification' : null;

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $this->handleImageUpload($request->file('image'));
        }

        // Handle user-specific notifications
        if ($request->notification_type === 'user_specific' && $request->user_id) {
            $userIds = $request->user_id;

            // Send FCM Notifications with Personalized User Name
            try {
                // Fetch users with their FCM tokens
                $usersWithTokens = \App\Models\User::whereIn('id', $userIds)
                    ->with('fcmTokens')
                    ->get();

                foreach ($usersWithTokens as $user) {
                    $tokens = $user->fcmTokens->pluck('fcm_token')->toArray();

                    if (!empty($tokens)) {
                        // Personalize title and message
                        $personalizedTitle = str_replace('{name}', $user->full_name ?? $user->username, $request->title);
                        $personalizedMsg = "Hello " . ($user->full_name ?? $user->username) . ",\n" . $request->message;

                        $fcmData = [
                            'type' => 'manual',
                            'title' => $personalizedTitle,
                            'message' => $personalizedMsg
                        ];

                        if (isset($data['image']) && $data['image']) {
                             $fcmData['image'] = url('public/img/' . $data['image']);
                        }

                        // Check 'general' setting (Group 1: General / Admin)
                        \App\Services\FcmService::sendToUser($user, $personalizedTitle, $personalizedMsg, $fcmData, 'general');
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Manual Notification FCM Error: ' . $e->getMessage());
            }

            // Create a notification for each selected user
            foreach ($userIds as $userId) {
                // Create ManualNotification record
                $notificationData = $data;
                $notificationData['user_id'] = $userId;
                $manualNotification = ManualNotification::create($notificationData);

                // Send actual notification to user's app via Notifications model
                \App\Models\Notifications::create([
                    'destination' => $userId,
                    'author' => auth()->id() ?? 1, // Admin user ID
                    'type' => 'manual',
                    'target' => $manualNotification->id
                ]);
            }

            $userCount = count($userIds);
            $successMessage = $userCount === 1
                ? __('admin.notification_created_successfully')
                : "Notification sent to {$userCount} users successfully";
        } else {
            // Create a single public notification
            $data['user_id'] = null;
            ManualNotification::create($data);

            // SEND FCM PUBLIC NOTIFICATION
            try {
                $title = $request->title;
                $msg = $request->message;
                $fcmData = [
                    'type' => 'public',
                    'title' => $title,
                    'message' => $msg
                ];
                if (isset($data['image']) && $data['image']) {
                    $fcmData['image'] = url('public/img/' . $data['image']);
                }

                \Log::info('Sending Public FCM Notification', ['title' => $title]);
                // Check 'general' setting for public notification as well
                \App\Services\FcmService::send('/topics/all_users', $title, $msg, $fcmData, 'general');

            } catch (\Exception $e) {
                \Log::error('Manual Public Notification FCM Error: ' . $e->getMessage());
            }

            $successMessage = __('admin.notification_created_successfully');
        }

        return redirect()->route('admin.manual_notifications.index')
            ->with('success_message', $successMessage);
    }

    /**
     * Display the specified notification
     */
    public function show(ManualNotification $manualNotification)
    {
        return view('admin.manual_notifications.show', compact('manualNotification'));
    }

    /**
     * Show the form for editing the notification
     */
    public function edit(ManualNotification $manualNotification)
    {
        return view('admin.manual_notifications.edit', compact('manualNotification'));
    }

    /**
     * Update the specified notification
     */
    public function update(Request $request, ManualNotification $manualNotification)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['title', 'message', 'is_active']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($manualNotification->image && \File::exists('public/img/' . $manualNotification->image)) {
                \File::delete('public/img/' . $manualNotification->image);
            }
            $data['image'] = $this->handleImageUpload($request->file('image'));
        }

        $manualNotification->update($data);

        return redirect()->route('admin.manual_notifications.index')
            ->with('success_message', __('admin.notification_updated_successfully'));
    }

    /**
     * Remove the specified notification
     */
    public function destroy(ManualNotification $manualNotification)
    {
        // Delete image if exists
        if ($manualNotification->image && \File::exists('public/img/' . $manualNotification->image)) {
            \File::delete('public/img/' . $manualNotification->image);
        }

        $manualNotification->delete();

        return redirect()->route('admin.manual_notifications.index')
            ->with('success_message', __('admin.notification_deleted_successfully'));
    }

    /**
     * Toggle notification status
     */
    public function toggleStatus(ManualNotification $manualNotification)
    {
        $manualNotification->update(['is_active' => !$manualNotification->is_active]);

        $status = $manualNotification->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success_message', __('admin.notification_' . $status . '_successfully'));
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(Request $request)
    {
        try {
            $request->validate([
                'confirm' => 'required|in:DELETE ALL NOTIFICATIONS'
            ], [
                'confirm.in' => 'You must type "DELETE ALL NOTIFICATIONS" exactly to confirm.'
            ]);

            $notificationCount = ManualNotification::count();

            if ($notificationCount === 0) {
                return redirect()->route('admin.manual_notifications.index')
                    ->with('info_message', 'No notifications found to delete.');
            }

            // Delete all images first
            $notifications = ManualNotification::whereNotNull('image')->get();
            foreach ($notifications as $notification) {
                if ($notification->image && \File::exists('public/img/' . $notification->image)) {
                    \File::delete('public/img/' . $notification->image);
                }
            }

            // Delete all notifications
            ManualNotification::truncate();

            \Log::info('All notifications deleted by admin', [
                'admin_id' => auth()->id(),
                'deleted_count' => $notificationCount,
                'timestamp' => now()
            ]);

            return redirect()->route('admin.manual_notifications.index')
                ->with('success_message', "Successfully deleted all {$notificationCount} notifications.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.manual_notifications.index')
                ->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Delete All Notifications Error: ' . $e->getMessage(), [
                'admin_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.manual_notifications.index')
                ->with('error_message', 'Failed to delete notifications. Please try again.');
        }
    }

    /**
     * Helper method for image upload
     */
    private function handleImageUpload($file)
    {
        try {
            $temp = 'public/temp/';
            $path = 'public/img/';

            // Ensure directories exist
            if (!File::exists($temp)) {
                File::makeDirectory($temp, 0755, true);
            }
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $extension = $file->getClientOriginalExtension();
            $fileName = 'notification-' . time() . '-' . uniqid() . '.' . $extension;

            // Move file to temp directory first
            if ($file->move($temp, $fileName)) {
                // Copy to final location
                if (File::copy($temp . $fileName, $path . $fileName)) {
                    // Delete temp file
                    File::delete($temp . $fileName);
                    return $fileName;
                }
            }
            return null;
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage());
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmNotificationSetting;
use Illuminate\Http\Request;

class FcmSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->id() != 25) {
            abort(403, 'Unauthorized action.');
        }

        $fcmSettings = FcmNotificationSetting::orderBy('label')->get();
        $settings = \App\Models\AdminSettings::first();
        return view('admin.fcm_settings.index', compact('fcmSettings', 'settings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        if (auth()->id() != 25) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'settings' => 'array', // removed required, as empty array means all disabled
            'settings.*' => 'boolean',
        ]);

        // Get all setting IDs or Types
        $allSettings = FcmNotificationSetting::all();

        foreach ($allSettings as $setting) {
            $isEnabled = $request->has('settings.' . $setting->notification_type);
            $setting->update(['is_enabled' => $isEnabled]);
        }

        return redirect()->back()->with('success_message', __('admin.settings_saved_successfully'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (auth()->id() != 25) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'label' => 'required|string|max:255',
            'notification_type' => 'required|string|max:255|unique:fcm_notification_settings,notification_type',
            'description' => 'nullable|string',
        ]);

        FcmNotificationSetting::create([
            'label' => $request->label,
            'notification_type' => \Illuminate\Support\Str::slug($request->notification_type, '_'),
            'description' => $request->description,
            'is_enabled' => true
        ]);

        return redirect()->back()->with('success_message', 'New notification type added successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (auth()->id() != 25) {
            abort(403, 'Unauthorized action.');
        }

        $setting = FcmNotificationSetting::findOrFail($id);
        $setting->delete();

        return redirect()->back()->with('success_message', 'Notification type deleted successfully');
    }
}

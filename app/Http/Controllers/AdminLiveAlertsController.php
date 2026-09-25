<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\LiveTransactionAlert;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminLiveAlertsController extends Controller
{
    /**
     * Show Live Alerts & Online Users management page
     */
    public function index()
    {
        $settings = AdminSettings::first();
        $alerts = LiveTransactionAlert::orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();

        return view('admin.live-alerts', compact('settings', 'alerts'));
    }

    /**
     * Update Online Users & Transaction Alerts global settings
     */
    public function updateSettings(Request $request)
    {
        $settings = AdminSettings::first();
        if (!$settings) {
            $settings = new AdminSettings();
        }

        $validator = Validator::make($request->all(), [
            'online_users_base' => 'required|integer|min:1',
            'online_users_min' => 'required|integer|min:1',
            'online_users_max' => 'required|integer|gte:online_users_min',
            'online_users_interval' => 'required|integer|min:1|max:60',
            'transaction_alerts_interval' => 'required|integer|min:2|max:60',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $settings->online_users_enabled = $request->has('online_users_enabled');
        $settings->online_users_base = (int) $request->online_users_base;
        $settings->online_users_min = (int) $request->online_users_min;
        $settings->online_users_max = (int) $request->online_users_max;
        $settings->online_users_interval = (int) $request->online_users_interval;

        $settings->transaction_alerts_enabled = $request->has('transaction_alerts_enabled');
        $settings->transaction_alerts_interval = (int) $request->transaction_alerts_interval;

        $settings->save();

        return redirect()->back()->withSuccessMessage('Live activity and online users settings updated successfully.');
    }

    /**
     * Store or update a transaction alert
     */
    public function storeAlert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alert_id' => 'nullable|integer|exists:live_transaction_alerts,id',
            'name' => 'required|string|max:100',
            'type' => 'required|in:withdrawal,deposit',
            'amount' => 'required|numeric|min:1',
            'time_ago' => 'nullable|string|max:50',
            'custom_message' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->filled('alert_id')) {
            $alert = LiveTransactionAlert::findOrFail($request->alert_id);
            $message = 'Transaction alert updated successfully.';
        } else {
            $alert = new LiveTransactionAlert();
            $alert->sort_order = (LiveTransactionAlert::max('sort_order') ?? 0) + 1;
            $message = 'New transaction alert added successfully.';
        }

        $alert->name = trim($request->name);
        $alert->type = strtolower($request->type);
        $alert->amount = (float) $request->amount;
        $alert->time_ago = trim($request->time_ago) ?: 'just now';
        $alert->custom_message = $request->filled('custom_message') ? trim($request->custom_message) : null;
        $alert->status = $request->status;
        $alert->save();

        return redirect()->back()->withSuccessMessage($message);
    }

    /**
     * Toggle active/inactive status of an alert
     */
    public function toggleAlert($id)
    {
        $alert = LiveTransactionAlert::findOrFail($id);
        $alert->status = ($alert->status === 'active') ? 'inactive' : 'active';
        $alert->save();

        return redirect()->back()->withSuccessMessage("Alert status changed to {$alert->status}.");
    }

    /**
     * Delete a transaction alert
     */
    public function deleteAlert($id)
    {
        $alert = LiveTransactionAlert::findOrFail($id);
        $alert->delete();

        return redirect()->back()->withSuccessMessage('Transaction alert deleted successfully.');
    }

    /**
     * Restore default realistic Pakistani transaction alerts
     */
    public function resetDefaults()
    {
        $initialAlerts = [
            ['name' => 'Ahmed Ali', 'type' => 'withdrawal', 'amount' => 5000, 'time_ago' => 'just now'],
            ['name' => 'Muhammad Hassan', 'type' => 'deposit', 'amount' => 10000, 'time_ago' => '1 min ago'],
            ['name' => 'Fatima Bibi', 'type' => 'withdrawal', 'amount' => 7500, 'time_ago' => '2 mins ago'],
            ['name' => 'Ali Raza', 'type' => 'deposit', 'amount' => 3500, 'time_ago' => '3 mins ago'],
            ['name' => 'Zainab Noor', 'type' => 'withdrawal', 'amount' => 12000, 'time_ago' => '4 mins ago'],
            ['name' => 'Umar Farooq', 'type' => 'deposit', 'amount' => 15000, 'time_ago' => '5 mins ago'],
            ['name' => 'Ayesha Siddique', 'type' => 'withdrawal', 'amount' => 20000, 'time_ago' => 'just now'],
            ['name' => 'Bilal Ahmed', 'type' => 'deposit', 'amount' => 2000, 'time_ago' => '2 mins ago'],
            ['name' => 'Maria Khan', 'type' => 'withdrawal', 'amount' => 25000, 'time_ago' => '1 min ago'],
            ['name' => 'Tariq Mahmood', 'type' => 'deposit', 'amount' => 5000, 'time_ago' => '4 mins ago'],
            ['name' => 'Sana Malik', 'type' => 'withdrawal', 'amount' => 8000, 'time_ago' => 'just now'],
            ['name' => 'Hamza Sheikh', 'type' => 'deposit', 'amount' => 12000, 'time_ago' => '3 mins ago'],
        ];

        LiveTransactionAlert::truncate();

        $now = now();
        foreach ($initialAlerts as $index => $alert) {
            LiveTransactionAlert::create([
                'name' => $alert['name'],
                'type' => $alert['type'],
                'amount' => $alert['amount'],
                'time_ago' => $alert['time_ago'],
                'status' => 'active',
                'sort_order' => $index + 1,
            ]);
        }

        return redirect()->back()->withSuccessMessage('Default transaction alerts restored successfully.');
    }
}

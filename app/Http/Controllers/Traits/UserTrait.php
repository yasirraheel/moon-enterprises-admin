<?php

namespace App\Http\Controllers\Traits;

use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests;
use App\Models\AdminSettings;
use App\Models\User;
use App\Models\UsersReported;
use App\Models\Notifications;
use App\Models\Followers;
use App\Models\Subscriptions;
use App\Models\PaymentGateways;
use App\Models\Like;
use App\Models\Replies;
use App\Models\Comments;
// use App\Models\CollectionsImages; // Removed - model no longer exists
use App\Models\Pages;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

trait UserTrait {

  public function deleteUser($id)
  {
    $settings = AdminSettings::first();
    $user = User::findOrFail($id);

    // 1. Orders and Prompt records
    try {
        DB::table('orders')
            ->where('user_id', $id)
            ->orWhere('username', $user->username)
            ->delete();
    } catch (\Exception $e) {
        \Log::error('Error deleting user orders: ' . $e->getMessage());
    }

    try {
        DB::table('orders_soft_deleted')
            ->where('user_id', $id)
            ->orWhere('username', $user->username)
            ->delete();
    } catch (\Exception $e) {
        \Log::error('Error deleting user soft deleted orders: ' . $e->getMessage());
    }

    // 2. Financial transactions, deposits & withdrawals
    try {
        DB::table('deposits')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('withdrawals')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('transactions')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('invoices')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('purchases')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('paid_service_sales')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    // 3. Notifications & FCM settings/tokens
    try {
        DB::table('notifications')->where('author', $id)->orWhere('destination', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('manual_notifications')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('user_read_public_notifications')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('fcm_tokens')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('fcm_notification_settings')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    // 4. Auth & Device sessions
    try {
        DB::table('user_devices')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('two_factor_codes')->where('user_id', $id)->delete();
    } catch (\Exception $e) {}

    try {
        DB::table('personal_access_tokens')->where('tokenable_id', $id)->where('tokenable_type', User::class)->delete();
    } catch (\Exception $e) {}

    // 5. Dealership requests
    if (Schema::hasTable('dealership_requests')) {
        try {
            DB::table('dealership_requests')->where('user_id', $id)->delete();
        } catch (\Exception $e) {}
    }

    // 6. Social interactions
    try {
        Comments::where('user_id', $id)->delete();
        Replies::where('user_id', $id)->delete();
        Like::where('user_id', $id)->delete();
        Followers::where('follower', $id)->orWhere('following', $id)->delete();
        UsersReported::where('user_id', $id)->orWhere('id_reported', $id)->delete();
    } catch (\Exception $e) {}

    // 7. Subscriptions
    try {
        $subscriptions = Subscriptions::whereUserId($id)->get();
        $payment       = PaymentGateways::whereId(2)->whereName('Stripe')->whereEnabled(1)->first();

        if ($subscriptions->count() > 0) {
            foreach ($subscriptions as $subscription) {
                if ($subscription->stripe_id == '') {
                    $subscription->delete();
                } else {
                    try {
                        if ($payment && $payment->key_secret) {
                            $stripe = new \Stripe\StripeClient($payment->key_secret);
                            $stripe->subscriptions->cancel($subscription->stripe_id);
                        }
                    } catch (\Exception $e) {}

                    if ($subscription->stripe_id != '') {
                        DB::table('subscription_items')->where('subscription_id', $subscription->id)->delete();
                        $subscription->delete();
                    }
                }
            }
        }
    } catch (\Exception $e) {}

    // 8. Files & Avatar
    if ($user->avatar && $user->avatar != $settings->avatar) {
        Storage::delete(config('path.avatar') . $user->avatar);
        if (\File::exists('public/avatar/' . $user->avatar)) {
            \File::delete('public/avatar/' . $user->avatar);
        }
    }

    if ($user->cover && $user->cover != $settings->cover) {
        Storage::delete(config('path.cover') . $user->cover);
        if (\File::exists('public/cover/' . $user->cover)) {
            \File::delete('public/cover/' . $user->cover);
        }
    }

    // Finally delete the user
    $user->delete();
  }
}

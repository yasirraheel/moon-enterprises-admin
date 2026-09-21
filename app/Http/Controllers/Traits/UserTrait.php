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
use Illuminate\Http\Request;

trait UserTrait {

  public function deleteUser($id)
  {
    $settings  = AdminSettings::first();
    $user = User::findOrFail($id);

		// Collections - Removed as Collections model no longer exists
		// Collections functionality was removed during conversion to universal starter kit

	// Comments Delete
	$comments = Comments::where('user_id', '=', $id)->get();

	if (isset($comments)) {
		foreach($comments as $comment){
			$comment->delete();
		}
	}

	// Replies
	$replies = Replies::where('user_id', '=', $id)->get();

	if (isset($replies)) {
		foreach($replies as $replie){
			$replies->delete();
		}
	}

	// Likes
	$likes = Like::where('user_id', '=', $id)->get();
	if (isset($likes)) {
		foreach($likes as $like){
			$like->delete();
		}
	}

	// Downloads - Removed as Downloads model no longer exists
	// Downloads functionality was removed during conversion to universal starter kit

	// Followers
	$followers = Followers::where( 'follower', $id )->orwhere('following',$id)->get();
	if (isset($followers)){
		foreach($followers as $follower){
			$follower->delete();
		}
	}

	// Delete Notification
	$notifications = Notifications::where('author',$id)
	->orWhere('destination', $id)
	->get();

	if (isset( $notifications)){
		foreach($notifications as $notification){
			$notification->delete();
		}
	}

	// Images Reported - Removed as ImagesReported model no longer exists
	// Image reporting functionality was removed during conversion to universal starter kit

	// Images - Removed as Images and Stock models no longer exist
	// Image functionality was removed during conversion to universal starter kit
	// This section is commented out to prevent errors

	// User Reported
	$users_reporteds = UsersReported::where('user_id', '=', $id)->orWhere('id_reported', '=', $id)->get();

	if (isset($users_reporteds)) {
		foreach ($users_reporteds as $users_reported) {
				$users_reported->delete();
			}// End
	}

  // Subscriptions User
  $subscriptions = Subscriptions::whereUserId($id)->get();
  $payment       = PaymentGateways::whereId(2)->whereName('Stripe')->whereEnabled(1)->first();


  if (isset($subscriptions)) {

    foreach ($subscriptions as $subscription) {
       if ($subscription->stripe_id == '') {
          $subscription->delete();
       } else {
         try {
           $stripe  = new \Stripe\StripeClient($payment->key_secret);
           $stripe->subscriptions->cancel($subscription->stripe_id);
         } catch (\Exception $e) {
         }

         if ($subscription->stripe_id != '') {
           DB::table('subscription_items')->where('subscription_id', '=', $subscription->id)->delete();
           $subscription->delete();
         }
       }
    }
  } // Isset Stripe

	//<<<-- Delete Avatar -->>>/
		if ($user->avatar != $settings->avatar ) {
      Storage::delete(config('path.avatar').$user->avatar);
		}//<--- IF FILE EXISTS

	//<<<-- Delete Cover -->>>/
		if ($user->cover != $settings->cover ) {
      Storage::delete(config('path.cover').$user->cover);
		}//<--- IF FILE EXISTS

	  // User Delete
      $user->delete();

    }//<-- End Method
}

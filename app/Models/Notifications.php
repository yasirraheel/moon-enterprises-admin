<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\PushNotificationTrait;

class Notifications extends Model
{
	protected $guarded = [];
	const UPDATED_AT = null;

	public function user()
	{
		return $this->belongsTo(User::class)->first();
	}

	public static function send($userDestination, $userAuthor, $type, $target)
	{
		$user   = User::find($userDestination);
		$author = User::find($userAuthor);
		$getPushNotificationDevices = $user->oneSignalDevices->pluck('player_id')->all();

		self::create([
			'destination' => $userDestination,
			'author' => $userAuthor,
			'type' => $type,
			'target' => $target
		]);

		// Send push notification
		if (config('settings.push_notification_status')) {
			$authorName = $author->full_name ?: $author->username;
			$image = Images::find($target);
			$urlPhoto = $image ? url('photo', $image->id) : null;

			switch ($type) {
				case 1:
					$msg          = $authorName . ' ' . __('users.followed_you');
					$linkDestination = url($author->username);
					break;
				case 2:
					$msg          = $authorName . ' ' . __('users.like_you_photo') . ' ' . $image->title;
					$linkDestination = $urlPhoto;
					break;
				case 3:
					$msg          = $authorName . ' ' . __('users.comment_you_photo') . ' ' . $image->title;
					$linkDestination = $urlPhoto;
					break;

				case 4:
					$msg          = $authorName . ' ' . __('users.liked_your_comment') . ' ' . $image->title;
					$linkDestination = $urlPhoto;
					break;

				case 5:
					$msg          = $authorName . ' ' . __('misc.has_bought');
					$linkDestination = $urlPhoto;
					break;

				case 6:
					$msg          = __('misc.referrals_made') . ' ' . __('misc.transaction');
					$linkDestination = url('my/referrals');
					break;
			}

			try {
				// Send OneSignal push notification
				if ($getPushNotificationDevices) {
					PushNotificationTrait::sendPushNotification($msg, $linkDestination, $getPushNotificationDevices);
				}

				// Send FCM push notification (Legacy and New)
				$title = config('settings.title');
				\App\Services\FcmService::sendToUser($user, $title, $msg, ['url' => $linkDestination]);

			} catch (\Exception $e) {
				info('Push Notification Error - ' . $e->getMessage());
			}
		}
	}
}

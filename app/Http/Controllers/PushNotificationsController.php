<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\UserDevices;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PushNotificationsController extends Controller
{
  use Traits\PushNotificationTrait;

  public function __construct(Request $request, AdminSettings $settings)
  {
    $this->request  = $request;
    $this->settings = $settings::select('onesignal_appid', 'onesignal_restapi')->first();
  }

  /**
   * Register device
   *
   * @return UserDevices()
   */
  public function registerDevice()
  {
    \Log::info('registerDevice called', ['request' => $this->request->all()]);

    if (! $this->request->player_id) {
        return false;
    }

    try {

      $device = $this->getDevice($this->request->player_id);

      $getDeviceExists = UserDevices::wherePlayerId($this->request->player_id)
        ->where('user_id', '<>', auth()->id())
        ->first();

        if ($getDeviceExists) {
          $getDeviceExists->delete();
        }

        UserDevices::updateOrCreate([
            'user_id' => $this->request->user_id,
            'player_id' => $this->request->player_id,
            'device_type' => $device,
        ], [
            'user_id' => $this->request->user_id,
            'player_id' => $this->request->player_id,
            'device_type' => $device
        ]);
    } catch (Exception $e) {
        throw new UnprocessableEntityHttpException($e->getMessage());
    }
  }

  /**
   * Get device
   *
   * @return $device_type
   */
  public function getDevice($playerId)
  {
    $appId      = $this->settings->onesignal_appid;
    $restApiKey = $this->settings->onesignal_restapi;

    $client = new HttpClient();
    $response = $client->request('GET', "https://onesignal.com/api/v1/players/$playerId?app_id=$appId", [
      'headers' => [
        'Authorization' => 'Basic ' . $restApiKey,
        'Content-Type' => 'application/json; charset=utf-8',
        'accept' => 'text/plain',
      ],
    ]);

    $data = json_decode($response->getBody());

    return $data->device_type;
  }

  /**
   * Delete device
   *
   * @return void
   */
  public function deleteDevice()
  {
    try {
        UserDevices::wherePlayerId($this->request->player_id)->delete();
    } catch (Exception $e) {
        throw new UnprocessableEntityHttpException($e->getMessage());
    }
  }

  /**
   * Register FCM Token
   */
  public function registerFcmToken(Request $request)
  {
      $request->validate([
          'fcm_token' => 'required|string',
          'device_type' => 'nullable|string' // android, ios
      ]);

      try {
          $userId = auth()->guard('sanctum')->id();

          if ($userId) {
              // Remove token from other users to ensure uniqueness
              \App\Models\FcmToken::where('fcm_token', $request->fcm_token)
                  ->where('user_id', '!=', $userId)
                  ->delete();

              // Update or Create token for current user
              \App\Models\FcmToken::updateOrCreate(
                  ['user_id' => $userId, 'fcm_token' => $request->fcm_token],
                  ['device_type' => $request->device_type ?? 'android']
              );
          }

          return response()->json([
              'success' => true,
              'message' => 'FCM Token registered successfully'
          ]);

      } catch (Exception $e) {
          return response()->json([
              'success' => false,
              'message' => $e->getMessage()
          ], 500);
      }
  }

  /**
   * Unregister FCM Token
   */
  public function unregisterFcmToken(Request $request)
  {
      $request->validate([
          'fcm_token' => 'required|string'
      ]);

      try {
          \App\Models\FcmToken::where('fcm_token', $request->fcm_token)->delete();

          return response()->json([
              'success' => true,
              'message' => 'FCM Token unregistered successfully'
          ]);
      } catch (Exception $e) {
          return response()->json([
              'success' => false,
              'message' => $e->getMessage()
          ], 500);
      }
  }

}

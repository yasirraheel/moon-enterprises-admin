<?php

namespace App\Http\Controllers\Traits;

use GuzzleHttp\Client as HttpClient;

trait PushNotificationTrait
{
    public static function sendPushNotification($msg, $url, array $devices)
    {
        $client = new HttpClient();

        $appId = config('settings.onesignal_appid');
        $restApiKey = config('settings.onesignal_restapi');

        $headings = [
            "en" => config('settings.title')
        ];

        $content = [
            "en" => $msg
        ];

        $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', [
            'headers' => [
                'Authorization' => 'Basic ' . $restApiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            'body' => json_encode([
                'app_id' => $appId,
                'contents' => $content,
                'headings' => $headings,
                'include_player_ids' => $devices,
                'data' => array("foo" => "bar"),
                'url' => $url
            ])
        ]);

        return $response;
    }
}

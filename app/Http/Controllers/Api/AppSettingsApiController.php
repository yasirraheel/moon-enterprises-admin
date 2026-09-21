<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\NewsTicker;
use App\Models\IpTvLink;

class AppSettingsApiController extends Controller
{
    /**
     * Get General App Settings
     */
    public function getAppSettings()
    {
        try {
            $settings = AdminSettings::select([
                'results_app_name as app_name', 
                'results_app_logo as app_logo', 
                'results_about_us as about_us', 
                'results_contact_no as contact_no',
                'lottie_animation_url as lottie_url',
                'countdown_video_url as countdown_video'
            ])->first();

            if ($settings) {
                if ($settings->app_logo) {
                    $settings->app_logo = asset('public/img/' . $settings->app_logo);
                }
                if ($settings->lottie_url) {
                    $settings->lottie_url = asset('public/lottie/' . $settings->lottie_url);
                }
                if ($settings->countdown_video) {
                    $settings->countdown_video = asset('public/video/' . $settings->countdown_video);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch app settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Active News Tickers
     */
    public function getNewsTicker()
    {
        try {
            $news = NewsTicker::where('status', 'active')
                ->orderBy('sort_order', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Failed to fetch news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Active IP TV Links
     */
    public function getIpTvLinks()
    {
        try {
            $links = IpTvLink::where('status', 'active')
                ->orderBy('sort_order', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $links
            ]);

        } catch (\Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IP TV links',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

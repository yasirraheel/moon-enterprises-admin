<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\NewsTicker;
use App\Models\IpTvLink;
use Illuminate\Support\Facades\Validator;

class AdminAppSettingsController extends Controller
{
    public function index()
    {
        $settings = AdminSettings::first();
        $newsTickers = NewsTicker::orderBy('sort_order', 'asc')->get();
        $ipTvLinks = IpTvLink::orderBy('sort_order', 'asc')->get();

        return view('admin.app-settings', compact('settings', 'newsTickers', 'ipTvLinks'));
    }

    public function updateSettings(Request $request)
    {
        $settings = AdminSettings::first();

// Validate
        $rules = [
            'results_app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'results_app_name' => 'nullable|string|max:100',
            'results_contact_no' => 'nullable|string|max:50',
            'results_about_us' => 'nullable|string',
            'lottie_animation_url' => 'nullable|file|mimes:json,txt|max:5120',
            'countdown_video_url' => 'nullable|file|mimes:mp4,webm|max:20480', // 20MB max
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput();
        }

        // Upload Logo
        $path = 'public/img/';
        if ($request->hasFile('results_app_logo')) {
            $extension = $request->file('results_app_logo')->getClientOriginalExtension();
            $file = 'results_app_logo-' . time() . '.' . $extension;
            $request->file('results_app_logo')->move($path, $file);

// Delete old logo if exists
            if (!empty($settings->results_app_logo) && file_exists($path . $settings->results_app_logo)) {
                unlink($path . $settings->results_app_logo);
            }

$settings->results_app_logo = $file;
        }

        $settings->results_app_name = $request->results_app_name;
        $settings->results_contact_no = $request->results_contact_no;
        $settings->results_about_us = $request->results_about_us;

        if ($request->hasFile('lottie_animation_url')) {
             $path = 'public/lottie/';
             if (!file_exists($path)) {
                 mkdir($path, 0777, true);
             }
             $extension = $request->file('lottie_animation_url')->getClientOriginalExtension();
             $file = 'animation-' . time() . '.' . $extension;
             $request->file('lottie_animation_url')->move($path, $file);

             if (!empty($settings->lottie_animation_url) && file_exists($path . $settings->lottie_animation_url)) {
                 unlink($path . $settings->lottie_animation_url);
             }
             $settings->lottie_animation_url = $file;
        }

        if ($request->hasFile('countdown_video_url')) {
             $path = 'public/video/';
             if (!file_exists($path)) {
                 mkdir($path, 0777, true);
             }
             $extension = $request->file('countdown_video_url')->getClientOriginalExtension();
             $file = 'countdown-' . time() . '.' . $extension;
             $request->file('countdown_video_url')->move($path, $file);
             
             if (!empty($settings->countdown_video_url) && file_exists($path . $settings->countdown_video_url)) {
                 unlink($path . $settings->countdown_video_url);
             }
             $settings->countdown_video_url = $file;
        }

        $settings->save();

        return redirect()->back()->withSuccessMessage('App settings updated successfully.');
    }

    // News Ticker Methods
    public function storeNews(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'sort_order' => 'integer',
        ]);

        NewsTicker::create($request->all());
        return redirect()->back()->withSuccessMessage('News added successfully.')->with('active_tab', 'news');
    }

public function updateNews(Request $request)
    {
         $request->validate([
            'id' => 'required|exists:news_tickers,id',
            'message' => 'required|string',
            'sort_order' => 'integer',
            'status' => 'required|in:active,inactive',
        ]);

$news = NewsTicker::findOrFail($request->id);
        $news->update($request->all());
        return redirect()->back()->withSuccessMessage('News updated successfully.')->with('active_tab', 'news');
    }

    public function deleteNews($id)
    {
        NewsTicker::findOrFail($id)->delete();
        return redirect()->back()->withSuccessMessage('News deleted successfully.')->with('active_tab', 'news');
    }

    // IP TV Methods
    public function storeIpTv(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'url' => 'required|url',
            'sort_order' => 'integer',
        ]);

        IpTvLink::create($request->all());
        return redirect()->back()->withSuccessMessage('IP TV Link added successfully.')->with('active_tab', 'iptv');
    }

    public function updateIpTv(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ip_tv_links,id',
            'title' => 'required|string|max:191',
            'url' => 'required|url',
            'sort_order' => 'integer',
             'status' => 'required|in:active,inactive',
        ]);

        $iptv = IpTvLink::findOrFail($request->id);
        $iptv->update($request->all());
        return redirect()->back()->withSuccessMessage('IP TV Link updated successfully.')->with('active_tab', 'iptv');
    }

    public function deleteIpTv($id)
    {
        IpTvLink::findOrFail($id)->delete();
        return redirect()->back()->withSuccessMessage('IP TV Link deleted successfully.')->with('active_tab', 'iptv');
    }

    public function parseM3u(Request $request)
    {
        $request->validate([
            'm3u_url' => 'nullable|url',
            'm3u_file' => 'nullable|file|mimes:txt,m3u,m3u8',
        ]);

        if (!$request->has('m3u_url') && !$request->hasFile('m3u_file')) {
            return redirect()->back()->withErrors(['error' => 'Please provide a URL or upload a file.'])->with('active_tab', 'iptv');
        }

        $content = '';
        if ($request->hasFile('m3u_file')) {
            $content = file_get_contents($request->file('m3u_file')->getRealPath());
        } elseif ($request->m3u_url) {
            $content = file_get_contents($request->m3u_url);
        }

        $lines = explode("\n", $content);
        $parsedChannels = [];
        $currentChannel = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, '#EXTINF:') === 0) {
                // Parse metadata
                // Example: #EXTINF:-1 tvg-logo="http://example.com/logo.png" group-title="Sports",Channel Name
                $currentChannel = [];
                
                // Extract Logo
                if (preg_match('/tvg-logo="([^"]*)"/', $line, $matches)) {
                    $currentChannel['icon'] = $matches[1];
                } else {
                    $currentChannel['icon'] = '';
                }

                // Extract Title (everything after the last comma)
                $parts = explode(',', $line);
                $currentChannel['title'] = trim(end($parts));
                
            } elseif (strpos($line, '#') !== 0) {
                // Assume it's a URL if it doesn't start with #
                if (!empty($currentChannel)) {
                    $currentChannel['url'] = $line;
                    $parsedChannels[] = $currentChannel;
                    $currentChannel = []; // Reset
                }
            }
        }

        // Return view with parsed channels
        // We need to reload the standard data too
        $settings = AdminSettings::first();
        $newsTickers = NewsTicker::orderBy('sort_order', 'asc')->get();
        $ipTvLinks = IpTvLink::orderBy('sort_order', 'asc')->get();

        // Pass parsed_channels to the view
        return view('admin.app-settings', compact('settings', 'newsTickers', 'ipTvLinks', 'parsedChannels'))->with('active_tab', 'iptv');
    }

    public function storeBulkIpTv(Request $request)
    {
        $request->validate([
            'channels' => 'required|array',
            'channels.*.title' => 'required|string',
            'channels.*.url' => 'required|url',
        ]);

        $count = 0;
        foreach ($request->channels as $channel) {
            // Skip if not selected for saving (assuming a 'save' key is sent for selected items)
            if (!isset($channel['save'])) {
                continue;
            }

            IpTvLink::create([
                'title' => $channel['title'],
                'url' => $channel['url'],
                'icon' => $channel['icon'] ?? null,
                'status' => 'active',
                'sort_order' => 0
            ]);
            $count++;
        }

        return redirect()->route('admin.app_settings')->withSuccessMessage("$count channels imported successfully.")->with('active_tab', 'iptv');
    }
}

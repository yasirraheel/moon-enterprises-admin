<?php

namespace App\Http\Controllers;

use App\Models\ApkVersion;
use App\Models\AdminSettings;
use App\Models\HelpVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ==================== APK VERSION MANAGEMENT ====================

    /**
     * Show APK versions management page
     */
    public function apkVersions()
    {
        $apkVersions = ApkVersion::orderBy('version_code', 'desc')->get();
        return view('admin.apk-versions', compact('apkVersions'));
    }

    /**
     * Update APK download link
     */
    public function updateApkLink(Request $request)
    {
        $request->validate([
            'version_name' => 'required|string|max:20',
            'version_code' => 'required|integer',
            'download_link' => 'required|url',
            'release_notes' => 'nullable|string|max:1000',
            'is_force_update' => 'boolean',
            'version_id' => 'nullable|integer'
        ]);

        try {
            $wasActive = false;
            $updatedVersion = null;
            
            // If this is set as active, deactivate all other versions
            if ($request->has('is_active')) {
                ApkVersion::where('is_active', true)->update(['is_active' => false]);
            }

            // Check if we're editing an existing version (via version_id)
            if ($request->filled('version_id')) {
                // Edit existing version by ID
                $existingVersion = ApkVersion::findOrFail($request->version_id);
                $wasActive = $existingVersion->is_active;
                
                $existingVersion->update([
                    'version_name' => $request->version_name,
                    'version_code' => $request->version_code,
                    'download_link' => $request->download_link,
                    'release_notes' => $request->release_notes,
                    'is_active' => $request->has('is_active'),
                    'is_force_update' => $request->has('is_force_update')
                ]);
                $updatedVersion = $existingVersion;
                $message = 'APK version updated successfully!';
            } else {
                // Check if version code already exists (for new versions)
                $existingVersion = ApkVersion::where('version_code', $request->version_code)->first();
                
                if ($existingVersion) {
                    // Update existing version found by version_code
                    $wasActive = $existingVersion->is_active;
                    
                    $existingVersion->update([
                        'version_name' => $request->version_name,
                        'download_link' => $request->download_link,
                        'release_notes' => $request->release_notes,
                        'is_active' => $request->has('is_active'),
                        'is_force_update' => $request->has('is_force_update')
                    ]);
                    $updatedVersion = $existingVersion;
                    $message = 'APK version updated successfully!';
                } else {
                    // Create new version
                    $updatedVersion = ApkVersion::create([
                        'version_name' => $request->version_name,
                        'version_code' => $request->version_code,
                        'download_link' => $request->download_link,
                        'release_notes' => $request->release_notes,
                        'is_active' => $request->has('is_active'),
                        'is_force_update' => $request->has('is_force_update'),
                        'download_count' => 0
                    ]);
                    $message = 'APK version added successfully!';
                }
            }

            // Note: Version is displayed directly from APK versions table in settings view
            // No need to store in admin_settings table

            return redirect()->route('apk.versions')->withSuccessMessage($message);

        } catch (\Exception $e) {
            Log::error('APK Link Update Error: ' . $e->getMessage());
            return back()->withErrorMessage('Failed to update APK link. Please try again.');
        }
    }

    /**
     * Toggle APK active status
     */
    public function toggleApkActive($id)
    {
        $apkVersion = ApkVersion::findOrFail($id);
        
        if ($apkVersion->is_active) {
            $apkVersion->update(['is_active' => false]);
            $message = 'APK version deactivated successfully!';
        } else {
            // Deactivate all other versions first
            ApkVersion::where('is_active', true)->update(['is_active' => false]);
            $apkVersion->update(['is_active' => true]);
            
            // Note: Version is displayed directly from APK versions table in settings view
            
            $message = 'APK version activated successfully!';
        }
        
        return back()->withSuccessMessage($message);
    }

    /**
     * Delete APK version
     */
    public function deleteApkVersion($id)
    {
        $apkVersion = ApkVersion::findOrFail($id);
        $apkVersion->delete();
        
        return back()->withSuccessMessage('APK version deleted successfully!');
    }

    /**
     * Show public download page
     */
    public function showDownloadPage()
    {
        $activeVersion = ApkVersion::getActiveVersion();
        $latestVersion = ApkVersion::getLatestVersion();
        
        return view('download-apk', compact('activeVersion', 'latestVersion'));
    }

    /**
     * Get APK version info for API
     */
    public function getApkVersionInfo()
    {
        $activeVersion = ApkVersion::getActiveVersion();
        $latestVersion = ApkVersion::getLatestVersion();
        
        return response()->json([
            'active_version' => $activeVersion ? [
                'version_name' => $activeVersion->version_name,
                'version_code' => $activeVersion->version_code,
                'download_url' => $activeVersion->download_url,
                'is_force_update' => $activeVersion->is_force_update,
                'release_notes' => $activeVersion->release_notes
            ] : null,
            'latest_version' => $latestVersion ? [
                'version_name' => $latestVersion->version_name,
                'version_code' => $latestVersion->version_code,
                'download_url' => $latestVersion->download_url,
                'is_force_update' => $latestVersion->is_force_update,
                'release_notes' => $latestVersion->release_notes
            ] : null
        ]);
    }

    // ==================== HELP VIDEOS MANAGEMENT ====================

    /**
     * Show help videos management page
     */
    public function helpVideos()
    {
        $videos = HelpVideo::orderBy('created_at', 'desc')->get();
        return view('admin.help-videos', compact('videos'));
    }

    /**
     * Store or update help video
     */
    public function storeHelpVideo(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_type' => 'required|in:youtube,local',
            'youtube_url' => 'required_if:video_type,youtube|nullable|url',
            'local_video' => 'required_if:video_type,local|nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:102400',
            'video_id' => 'nullable|integer'
        ]);

        try {
            $videoPath = null;

            // Handle local video upload
            if ($request->video_type === 'local' && $request->hasFile('local_video')) {
                $file = $request->file('local_video');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('help-videos');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                // Move file
                $file->move($uploadPath, $fileName);
                $videoPath = $fileName;
            }

            // Check if we're editing an existing video
            if ($request->filled('video_id')) {
                $video = HelpVideo::findOrFail($request->video_id);
                
                // Delete old video file if switching to YouTube or uploading new file
                if ($video->video_type === 'local' && $video->local_video_path) {
                    $oldPath = public_path('help-videos/' . $video->local_video_path);
                    if (file_exists($oldPath) && ($request->video_type === 'youtube' || $videoPath)) {
                        unlink($oldPath);
                    }
                }
                
                $video->update([
                    'title' => $request->title,
                    'video_type' => $request->video_type,
                    'youtube_url' => $request->video_type === 'youtube' ? $request->youtube_url : null,
                    'local_video_path' => $request->video_type === 'local' ? ($videoPath ?: $video->local_video_path) : null,
                    'is_active' => $request->has('is_active')
                ]);
                
                $message = 'Help video updated successfully!';
            } else {
                // Create new video
                HelpVideo::create([
                    'title' => $request->title,
                    'video_type' => $request->video_type,
                    'youtube_url' => $request->video_type === 'youtube' ? $request->youtube_url : null,
                    'local_video_path' => $videoPath,
                    'is_active' => $request->has('is_active')
                ]);
                
                $message = 'Help video added successfully!';
            }

            return redirect()->route('admin.help.videos')->withSuccessMessage($message);

        } catch (\Exception $e) {
            Log::error('Help Video Error: ' . $e->getMessage());
            return back()->withErrorMessage('Failed to save help video. Please try again.');
        }
    }

    /**
     * Toggle help video active status
     */
    public function toggleHelpVideoActive($id)
    {
        $video = HelpVideo::findOrFail($id);
        $video->update(['is_active' => !$video->is_active]);
        
        $message = $video->is_active ? 'Help video activated successfully!' : 'Help video deactivated successfully!';
        return back()->withSuccessMessage($message);
    }

    /**
     * Delete help video
     */
    public function deleteHelpVideo($id)
    {
        $video = HelpVideo::findOrFail($id);
        
        // Delete video file if it's local
        if ($video->video_type === 'local' && $video->local_video_path) {
            $filePath = public_path('help-videos/' . $video->local_video_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $video->delete();
        
        return back()->withSuccessMessage('Help video deleted successfully!');
    }

    /**
     * Get help videos for API
     */
    public function getHelpVideosApi()
    {
        $videos = HelpVideo::getActiveVideos();
        
        $response = $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video_type' => $video->video_type,
                'video_url' => $video->video_type === 'youtube' 
                    ? $video->youtube_url 
                    : url('public/help-videos/' . $video->local_video_path),
                'embed_url' => $video->video_type === 'youtube' 
                    ? $video->getYoutubeEmbedUrl() 
                    : null,
                'view_count' => $video->view_count,
                'created_at' => $video->created_at->toDateTimeString()
            ];
        });

        return response()->json([
            'success' => true,
            'videos' => $response
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpVideo extends Model
{
    protected $fillable = [
        'title',
        'video_type',
        'youtube_url',
        'local_video_path',
        'is_active',
        'view_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'view_count' => 'integer'
    ];

    /**
     * Get active help videos
     */
    public static function getActiveVideos()
    {
        return self::where('is_active', true)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get video URL based on type
     */
    public function getVideoUrl()
    {
        if ($this->video_type === 'youtube') {
            return $this->youtube_url;
        }
        
        return $this->local_video_path ? url('public/help-videos/' . $this->local_video_path) : null;
    }

    /**
     * Get YouTube embed URL
     */
    public function getYoutubeEmbedUrl()
    {
        if ($this->video_type !== 'youtube' || !$this->youtube_url) {
            return null;
        }

        // Extract video ID from various YouTube URL formats
        $videoId = null;
        if (preg_match('/[?&]v=([^&]+)/', $this->youtube_url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^?]+)/', $this->youtube_url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/embed\/([^?]+)/', $this->youtube_url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
}

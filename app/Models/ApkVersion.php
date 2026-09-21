<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApkVersion extends Model
{
    protected $fillable = [
        'version_name',
        'version_code',
        'download_link',
        'apk_file_path',
        'release_notes',
        'is_active',
        'is_force_update',
        'download_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_force_update' => 'boolean',
        'download_count' => 'integer',
        'version_code' => 'integer'
    ];

    /**
     * Get the active APK version
     */
    public static function getActiveVersion()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get the latest APK version
     */
    public static function getLatestVersion()
    {
        return self::orderBy('version_code', 'desc')->first();
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Get public download link.
     */
    public function getDownloadUrlAttribute()
    {
        return $this->getPublicDownloadLink();
    }

    /**
     * Get computed public download link.
     */
    public function getPublicDownloadLinkAttribute()
    {
        return $this->getPublicDownloadLink();
    }

    private function getPublicDownloadLink(): ?string
    {
        if (!empty($this->apk_file_path)) {
            return route('public.apk.file', ['id' => $this->id]);
        }

        return $this->download_link;
    }
}

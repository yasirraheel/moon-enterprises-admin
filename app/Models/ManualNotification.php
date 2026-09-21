<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ManualNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'action_type',
        'title',
        'message',
        'image',
        'metadata',
        'is_active',
        'read_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('public/img', $this->image);
        }
        return null;
    }
}
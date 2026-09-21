<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidService extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price',
        'description',
        'golden_text',
        'image',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by title.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('title');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidServiceSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'amount',
        'status',
        'purchased_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'purchased_at' => 'datetime'
    ];

    /**
     * Get the user that owns the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service that was purchased.
     */
    public function service()
    {
        return $this->belongsTo(PaidService::class, 'service_id');
    }

    /**
     * Scope a query to only include active purchases.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include purchases for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

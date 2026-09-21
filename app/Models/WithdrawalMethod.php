<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalMethod extends Model
{
    protected $fillable = [
        'name',
        'image',
        'min_amount',
        'max_amount',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2'
    ];

    // Scope for active withdrawal methods
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordering by name
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }
}

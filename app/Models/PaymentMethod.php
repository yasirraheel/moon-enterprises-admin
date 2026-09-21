<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'bank_or_account_name',
        'account_title',
        'account_no',
        'bank_image',
        'is_active',
        'sort_order',
        'minimum_limit',
        'maximum_limit'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'minimum_limit' => 'decimal:2',
        'maximum_limit' => 'decimal:2'
    ];

    // Scope for active payment methods
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordering by sort_order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}

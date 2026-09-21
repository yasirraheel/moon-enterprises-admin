<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawals extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'account_number',
        'account_title',
        'bank_name',
        'status',
        'transaction_id',
        'payment_proof',
        'admin_notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime'
    ];

    const CREATED_AT = 'date';
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}

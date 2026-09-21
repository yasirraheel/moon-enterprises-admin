<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_balance',
        'transaction_amount',
        'type',
        'remaining_balance',
        'transaction_type',
        'description',
        'reference_id',
        'reference_type',
        'metadata'
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'transaction_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function getFormattedAmountAttribute()
    {
        return number_format($this->transaction_amount, 2);
    }

    public function getFormattedCurrentBalanceAttribute()
    {
        return number_format($this->current_balance, 2);
    }

    public function getFormattedRemainingBalanceAttribute()
    {
        return number_format($this->remaining_balance, 2);
    }

    public function getTransactionTypeLabelAttribute()
    {
        $labels = [
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'paid_service' => 'Paid Service',
            'order_placed' => 'Order Placed',
            'admin_credit' => 'Admin Credit',
            'admin_debit' => 'Admin Debit',
            'signup_bonus' => 'Signup Bonus',
            'refund' => 'Refund'
        ];

        return $labels[$this->transaction_type] ?? ucfirst(str_replace('_', ' ', $this->transaction_type));
    }
}

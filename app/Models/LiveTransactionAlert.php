<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveTransactionAlert extends Model
{
    protected $table = 'live_transaction_alerts';

    protected $guarded = [];

    /**
     * Get formatted amount with commas
     */
    public function getFormattedAmountAttribute()
    {
        return number_format((float) $this->amount);
    }

    /**
     * Get computed display message
     */
    public function getDisplayMessageAttribute()
    {
        if (!empty($this->custom_message)) {
            return $this->custom_message;
        }

        $action = strtolower($this->type) === 'deposit' ? 'deposited' : 'withdrew';
        return "{$this->name} {$action} Rs. " . $this->formatted_amount;
    }
}

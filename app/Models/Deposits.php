<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposits extends Model
{
	protected $guarded = [];
	const CREATED_AT = 'date';
	const UPDATED_AT = null;

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class);
	}

	public function invoice()
	{
		return $this->hasOne(Invoices::class)->whereStatus('paid')->first();
	}

	public function invoicePending()
	{
		return $this->hasOne(Invoices::class)->whereStatus('pending');
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

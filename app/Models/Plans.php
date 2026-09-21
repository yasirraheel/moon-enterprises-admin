<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use HasFactory;

    protected $fillable = [
      'plan_id',
      'name',
      'price',
      'price_year',
      'credits',
      'duration',
      'unused_credits_rollover',
      'status',
      'created_at'
    ];

    public function user()
    {
  		return $this->belongsTo(User::class)->first();
  	}
}

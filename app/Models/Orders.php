<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'username',
        'user_phone',
        'game_name',
        'bond_name',
        'rttp',
        'first',
        'second',
        'status',
        'commission_amount',
        'n_p',
        'is_exported',
        'cut_first',
        'cut_second',
        'exported_at',
        'after_export_first',
        'after_export_second'
    ];

    protected $casts = [
        'is_exported' => 'boolean',
        'exported_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

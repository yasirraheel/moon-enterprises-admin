<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpTvLink extends Model
{
    protected $fillable = [
        'title',
        'url',
        'icon',
        'status',
        'sort_order'
    ];
}

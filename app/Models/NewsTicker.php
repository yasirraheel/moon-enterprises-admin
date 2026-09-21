<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsTicker extends Model
{
    protected $fillable = [
        'message',
        'status',
        'sort_order'
    ];
}

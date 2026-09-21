<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePrompts extends Model
{
    protected $fillable = [
        'prompt',
        'status',
        'sort_order',
        'number_start',
        'number_end',
        'first_limit',
        'second_limit',
    ];

}

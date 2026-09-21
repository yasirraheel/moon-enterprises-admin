<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Results extends Model
{
    use HasFactory;

    protected $fillable = [
        'fd',
        'result_date',
        'result_time',
        'first_prize',
        'second_prize',
        'third_prize',
        'fourth_prize',
    ];
}

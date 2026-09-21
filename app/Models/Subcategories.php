<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategories extends Model
{
	protected $guarded = [];
	public $timestamps = false;

	protected $fillable = [
		'name',
		'category_id',
		'slug',
		'mode',
		'description',
		'keywords',
		'start_date',
		'start_time',
		'close_date',
		'close_time'
	];

	public function category()
	{
		return $this->belongsTo(Categories::class);
	}
}

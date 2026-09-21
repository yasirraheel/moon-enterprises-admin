<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
	protected $guarded = [];
	public $timestamps = false;

	protected $fillable = [
		'name',
		'mode',
		'date',
		'time'
	];

	public function subcategories()
	{
		return $this->hasMany(Subcategories::class, 'category_id')->where('mode', 'on');
	}

	/**
	 * Get all content associated with this category
	 * This can be extended to support different content types
	 */
	public function content()
	{
		// This can be morphed to different content types as needed
		// return $this->morphedByMany(ContentModel::class, 'categorizable');
		return collect(); // Empty for now until specific content models are added
	}

}

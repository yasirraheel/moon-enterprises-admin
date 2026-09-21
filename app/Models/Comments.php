<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
	protected $guarded = [];
	const CREATED_AT = 'date';
	const UPDATED_AT = null;

	protected $fillable = [
		'user_id',
		'content_id',
		'content_type',
		'comment',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class)->first();
	}

	public function author()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * Get the parent commentable model (morphed)
	 */
	public function commentable()
	{
		return $this->morphTo('content');
	}

	public function total_likes()
	{
		return $this->hasMany(CommentsLikes::class,'comment_id')->where('status','1');
	}

}

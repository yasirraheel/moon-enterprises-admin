<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
	public $timestamps = false;

	public static function users()
	{
		$sort      =  request()->get('sort');
		$location  =  request()->get('location');

		// Default to popular sorting since images are removed
		if ($sort == 'latest') {
			$sortQuery = 'users.id';
		} else {
			$sortQuery = 'COUNT(followers.id)';
		}

		$data = User::where('users.status', 'active');

		// LOCATION
		if (isset($location) && $location != '') {
			$data->where('users.countries_id', $location);
		}

		// POPULAR (followers count)
		$data->leftjoin('followers', function($join) {
			$join->on('users.id', '=', 'followers.following')
				 ->where('followers.status', '=', '1');
		});

		$query = 	$data->where('users.status', '=', 'active')
			->groupBy('users.id')
			->orderBy(\DB::raw($sortQuery), 'DESC')
			->orderBy('users.id', 'ASC')
			->select(
				'users.id',
				'users.username',
				'users.name',
				'users.status'
			)
			->withCount(['followers'])
			->paginate(config('settings.result_request'))->onEachSide(1);

		return $query;
	}

	// Search functionality - now returns empty results since images are removed
	public static function searchImages()
	{
		$q = request()->get('q');
		$page = request()->get('page');

		// Return empty collection for universal starter kit
		$images = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);

		$title = __('misc.result_of') . ' ' . $q . ' - ';
		$total = 0;

		return ['images' => $images, 'page' => $page, 'title' => $title, 'total' => $total, 'q' => $q];
	}

	// All image-related methods now return empty collections
	public static function latestImagesHome()
	{
		return collect([]);
	}

	public static function latestImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function featuredImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function popularImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function commentedImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function viewedImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function downloadsImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function categoryImages($slug)
	{
		$category = Categories::with(['subcategories:id,category_id,name,slug'])->where('slug', '=', $slug)->firstOrFail();

		$images = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);

		return ['images' => $images, 'category' => $category];
	}

	public static function subCategoryImages($slug, $subcategory)
	{
		$subcategory = Subcategories::with(['category:id,name,slug'])->where('slug', '=', $subcategory)->firstOrFail();

		$images = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);

		return ['images' => $images, 'subcategory' => $subcategory];
	}

	public static function tagsImages($tags)
	{
		$images = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);

		$title = __('misc.tags') . ' - ' . $tags;
		$total = 0;

		return ['images' => $images, 'title' => $title, 'total' => $total, 'tags' => $tags];
	}

	public static function camerasImages($camera)
	{
		$images = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);

		$title = __('misc.photos_taken_with') . ' ' . ucfirst($camera);
		$total = 0;

		return ['images' => $images, 'title' => $title, 'total' => $total, 'camera' => $camera];
	}

	public static function colorsImages($colors)
	{
		$images = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);

		$title = __('misc.colors') . ' #' . $colors;
		$total = 0;

		return ['images' => $images, 'title' => $title, 'total' => $total, 'colors' => $colors];
	}

	public static function userImages($id)
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function premiumImages()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}

	public static function vectors()
	{
		return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
			'path' => request()->url(),
		]);
	}
}

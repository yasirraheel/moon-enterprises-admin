<?php

namespace App\Http\Controllers;

use Mail;
use App\Helper;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Deposits;
use App\Models\Categories;
use App\Models\Orders;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Notifications;
use App\Models\Subcategories;
use App\Models\Subscriptions;
use App\Models\UsersReported;
use App\Models\PaymentGateways;
use App\Models\PaymentMethod;
use App\Models\Withdrawals;
use App\Models\WithdrawalMethod;
use App\Models\PaidService;
use App\Models\PaidServiceSale;
use App\Models\OrdersSoftDeleted;
use App\Models\Transaction;
use App\Models\ApkVersion;
use App\Models\HelpVideo;
use App\Services\TransactionService;
use App\Services\NotificationService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Notifications\DepositVerification;
use Intervention\Image\Laravel\Facades\Image;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AdminController extends Controller
{

	public function __construct(AdminSettings $settings)
	{
		$this->settings = $settings::first();
	}
	// START
	public function dashboard()
	{
		if (!auth()->user()->hasPermission('dashboard')) {
			return view('admin.unauthorized');
		}

		// === BUSINESS EARNINGS CALCULATION ===

		// 1. EARNINGS: Paid Services + Orders - Refunds
		$paidServiceTotal = Transaction::where('transaction_type', 'paid_service')->sum('transaction_amount');
		$orderTotal = Transaction::where('transaction_type', 'order_placed')->sum('transaction_amount');
		$refundTotal = Transaction::where('transaction_type', 'refund')->sum('transaction_amount');
		$totalEarnings = $paidServiceTotal + $orderTotal - $refundTotal;

		// 2. DEPOSITS & WITHDRAWALS: Separate tracking
		$depositTotal = Transaction::where('transaction_type', 'deposit')->sum('transaction_amount');
		// Correct way to get withdrawals: Sum of withdrawal transactions OR from Withdrawals table if separate
		$withdrawalTotal = \App\Models\Withdrawals::where('status', 'approved')->sum('amount');

		// 3. ADMIN CREDITS: Money added by admin
		$adminCreditTotal = Transaction::where('transaction_type', 'admin_credit')->sum('transaction_amount');
		$adminDebitTotal = Transaction::where('transaction_type', 'admin_debit')->sum('transaction_amount');
		$netAdminCredits = $adminCreditTotal - $adminDebitTotal;

		// Initialize arrays for charts
		$monthsData = [];
		$revenueSum = [];
		$transactionCounts = [];
		$creditData = [];
		$debitData = [];

		// Calculate Chart Data for last 30 days
		for ($i = 0; $i <= 30; ++$i) {
			$date = date('Y-m-d', strtotime('-' . $i . ' day'));

			// Earnings for this date (Paid Services + Orders - Refunds)
			$paidServices = Transaction::where('transaction_type', 'paid_service')
				->whereDate('created_at', '=', $date)
				->sum('transaction_amount');

			$orders = Transaction::where('transaction_type', 'order_placed')
				->whereDate('created_at', '=', $date)
				->sum('transaction_amount');

			$refunds = Transaction::where('transaction_type', 'refund')
				->whereDate('created_at', '=', $date)
				->sum('transaction_amount');

			$dailyEarnings = $paidServices + $orders - $refunds;

			// Deposits for this date
			$deposits = Transaction::where('transaction_type', 'deposit')
				->whereDate('created_at', '=', $date)
				->sum('transaction_amount');

			// Withdrawals for this date
			$withdrawals = \App\Models\Withdrawals::where('status', 'approved')
				->whereDate('updated_at', '=', $date) // Use updated_at for approval date
				->sum('amount');

			// Transaction count for this date
			$transactionCount = Transaction::whereDate('created_at', '=', $date)->count();

			// Format Date on Chart
			$formatDate = Helper::formatDateChart($date);
			$monthsData[] = "'$formatDate'";

			// Chart data
			$revenueSum[] = $dailyEarnings;
			$creditData[] = $deposits;
			$debitData[] = $withdrawals;
			$transactionCounts[] = $transactionCount;
		}

		// === DAILY EARNINGS STATISTICS ===

		// Today's Earnings (Paid Services + Orders - Refunds)
		$stat_revenue_today = Transaction::where('transaction_type', 'paid_service')
			->whereDate('created_at', Carbon::today())
			->sum('transaction_amount') +
			Transaction::where('transaction_type', 'order_placed')
			->whereDate('created_at', Carbon::today())
			->sum('transaction_amount') -
			Transaction::where('transaction_type', 'refund')
			->whereDate('created_at', Carbon::today())
			->sum('transaction_amount');

		// Yesterday's Earnings
		$stat_revenue_yesterday = Transaction::where('transaction_type', 'paid_service')
			->whereDate('created_at', Carbon::yesterday())
			->sum('transaction_amount') +
			Transaction::where('transaction_type', 'order_placed')
			->whereDate('created_at', Carbon::yesterday())
			->sum('transaction_amount') -
			Transaction::where('transaction_type', 'refund')
			->whereDate('created_at', Carbon::yesterday())
			->sum('transaction_amount');

		// This Week's Earnings
		$stat_revenue_week = Transaction::where('transaction_type', 'paid_service')
			->whereBetween('created_at', [
				Carbon::parse('now')->startOfWeek(),
				Carbon::parse('now')->endOfWeek(),
			])->sum('transaction_amount') +
			Transaction::where('transaction_type', 'order_placed')
			->whereBetween('created_at', [
				Carbon::parse('now')->startOfWeek(),
				Carbon::parse('now')->endOfWeek(),
			])->sum('transaction_amount') -
			Transaction::where('transaction_type', 'refund')
			->whereBetween('created_at', [
				Carbon::parse('now')->startOfWeek(),
				Carbon::parse('now')->endOfWeek(),
			])->sum('transaction_amount');

		// Last Week's Earnings
		$stat_revenue_last_week = Transaction::where('transaction_type', 'paid_service')
			->whereBetween('created_at', [
				Carbon::now()->startOfWeek()->subWeek(),
				Carbon::now()->subWeek()->endOfWeek(),
			])->sum('transaction_amount') +
			Transaction::where('transaction_type', 'order_placed')
			->whereBetween('created_at', [
				Carbon::now()->startOfWeek()->subWeek(),
				Carbon::now()->subWeek()->endOfWeek(),
			])->sum('transaction_amount') -
			Transaction::where('transaction_type', 'refund')
			->whereBetween('created_at', [
				Carbon::now()->startOfWeek()->subWeek(),
				Carbon::now()->subWeek()->endOfWeek(),
			])->sum('transaction_amount');

		// This Month's Earnings
		$stat_revenue_month = Transaction::where('transaction_type', 'paid_service')
			->whereBetween('created_at', [
				Carbon::parse('now')->startOfMonth(),
				Carbon::parse('now')->endOfMonth(),
			])->sum('transaction_amount') +
			Transaction::where('transaction_type', 'order_placed')
			->whereBetween('created_at', [
				Carbon::parse('now')->startOfMonth(),
				Carbon::parse('now')->endOfMonth(),
			])->sum('transaction_amount') -
			Transaction::where('transaction_type', 'refund')
			->whereBetween('created_at', [
				Carbon::parse('now')->startOfMonth(),
				Carbon::parse('now')->endOfMonth(),
			])->sum('transaction_amount');

		// Last Month's Earnings
		$stat_revenue_last_month = Transaction::where('transaction_type', 'paid_service')
			->whereBetween('created_at', [
				Carbon::now()->startOfMonth()->subMonth(),
				Carbon::now()->subMonth()->endOfMonth(),
			])->sum('transaction_amount') +
			Transaction::where('transaction_type', 'order_placed')
			->whereBetween('created_at', [
				Carbon::now()->startOfMonth()->subMonth(),
				Carbon::now()->subMonth()->endOfMonth(),
			])->sum('transaction_amount') -
			Transaction::where('transaction_type', 'refund')
			->whereBetween('created_at', [
				Carbon::now()->startOfMonth()->subMonth(),
				Carbon::now()->subMonth()->endOfMonth(),
			])->sum('transaction_amount');

		// === TRANSACTION TYPE BREAKDOWN ===
		// (Already calculated above, keeping for reference)

		// === CHART DATA ===
		$label = implode(',', array_reverse($monthsData));
		$data = implode(',', array_reverse($revenueSum));
		$dataLastTransactions = implode(',', array_reverse($transactionCounts));
		$dataCredits = implode(',', array_reverse($creditData));
		$dataDebits = implode(',', array_reverse($debitData));

		// === COUNTS ===
		$totalUsers = User::count() - 1; // Subtract 1 to exclude admin user from customer count
		$totalTransactions = Transaction::count();
		$totalDeposits = Transaction::where('transaction_type', 'deposit')->count();
		$totalWithdrawals = Withdrawals::where('status', 'approved')->count();
		$totalPaidServices = Transaction::where('transaction_type', 'paid_service')->count();
		$totalOrders = Transaction::where('transaction_type', 'order_placed')->count();

		return view('admin.dashboard', [
			// 1. BUSINESS EARNINGS
			'earningNetAdmin' => $totalEarnings,
			'totalEarnings' => $totalEarnings,
			'paidServiceTotal' => $paidServiceTotal,
			'orderTotal' => $orderTotal,
			'refundTotal' => $refundTotal,

			// 2. DEPOSITS & WITHDRAWALS
			'depositTotal' => $depositTotal,
			'withdrawalTotal' => $withdrawalTotal,
			'netDeposits' => $depositTotal - $withdrawalTotal,

			// 3. ADMIN CREDITS
			'adminCreditTotal' => $adminCreditTotal,
			'adminDebitTotal' => $adminDebitTotal,
			'netAdminCredits' => $netAdminCredits,

			// Chart Data
			'label' => $label,
			'data' => $data,
			'datalastSales' => $dataLastTransactions,
			'dataCredits' => $dataCredits,
			'dataDebits' => $dataDebits,

			// User Stats
			'totalUsers' => $totalUsers,
			'totalSales' => $totalTransactions,
			'totalTransactions' => $totalTransactions,

			// Time-based Earnings
			'stat_revenue_today' => $stat_revenue_today,
			'stat_revenue_yesterday' => $stat_revenue_yesterday,
			'stat_revenue_week' => $stat_revenue_week,
			'stat_revenue_last_week' => $stat_revenue_last_week,
			'stat_revenue_month' => $stat_revenue_month,
			'stat_revenue_last_month' => $stat_revenue_last_month,

			// Transaction Type Counts
			'totalDeposits' => $totalDeposits,
			'totalWithdrawals' => $totalWithdrawals,
			'totalPaidServices' => $totalPaidServices,
			'totalOrders' => $totalOrders,
		]);
	}

	// START
	public function categories()
	{
		$data = Categories::orderBy('name')->get();

		return view('admin.categories', compact('data'));
	}

	public function addCategories()
	{
		return view('admin.add-categories');
	}

	public function storeCategories(Request $request)
	{
		$temp            = 'public/temp/'; // Temp
		$path            = 'public/img-category/'; // Path General

		Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
			return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		$rules = [
			'name'        => 'required',
			'thumbnail'   => 'image|dimensions:min_width=457,min_height=359',
			'date'        => 'nullable|date',
			'time'        => 'nullable',
		];

		$this->validate($request, $rules);

		if ($request->hasFile('thumbnail')) {

			$extension        = $request->file('thumbnail')->extension();
			$thumbnail        = str_slug($request->name) . '-' . str_random(32) . '.' . $extension;

			if ($request->file('thumbnail')->move($temp, $thumbnail)) {

				$image = Image::read($temp . $thumbnail);

				if ($image->width() == 457 && $image->height() == 359) {

					$image->encodeByExtension($extension)->save($path . $thumbnail);
				} else {
					$image->cover(width: 457, height: 359)
						->encodeByExtension($extension)
						->save($path . $thumbnail);
				}
				\File::delete($temp . $thumbnail);
			}
		} // HasFile

		else {
			$thumbnail = '';
		}

		$sql              = new Categories();
		$sql->name        = trim($request->name);
		$sql->thumbnail   = $thumbnail;
		$sql->mode        = $request->mode ?? 'off';
		$sql->date        = $request->date;
		$sql->time        = $request->time;
		$sql->save();

		return redirect('panel/admin/categories')->withSuccessMessage(__('admin.success_add_category'));
	}

	public function editCategories($id)
	{
		$category = Categories::find($id);
		$categories = Categories::orderBy('name')->get(); // For sidebar menu

		return view('admin.edit-categories', compact('category', 'categories'));
	}

	public function updateCategories(Request $request)
	{


		$categories = Categories::findOrFail($request->id);
		$temp       = 'public/temp/'; // Temp
		$path       = 'public/img-category/'; // Path General

		Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
			return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		$rules = [
			'name'      => 'required',
			'thumbnail' => 'image|dimensions:min_width=457,min_height=359',
			'date'      => 'nullable|date',
			'time'      => 'nullable',
		];

		$this->validate($request, $rules);

		if ($request->hasFile('thumbnail')) {

			$extension        = $request->file('thumbnail')->getClientOriginalExtension();
			$type_mime_shot   = $request->file('thumbnail')->getMimeType();
			$sizeFile         = $request->file('thumbnail')->getSize();
			$thumbnail        = str_slug($request->name) . '-' . str_random(32) . '.' . $extension;

			if ($request->file('thumbnail')->move($temp, $thumbnail)) {

				$image = Image::read($temp . $thumbnail);

				if ($image->width() == 457 && $image->height() == 359) {
					$image->encodeByExtension($extension)->save($path . $thumbnail);

				} else {
					$image->cover(width: 457, height: 359)
						->encodeByExtension($extension)
						->save($path . $thumbnail);
				}

				\File::delete($temp . $thumbnail);

				// Delete Old Image
				\File::delete($path . $categories->thumbnail);
			} // End File
		} // HasFile
		else {
			$thumbnail = $categories->thumbnail;
		}

		// UPDATE CATEGORY
		$categories->name       = $request->name;
		$categories->thumbnail  = $thumbnail;
		$categories->mode       = $request->mode ?? 'off';
		$categories->date       = $request->date;
		$categories->time       = $request->time;
		$categories->save();

		return redirect('panel/admin/categories')->withSuccessMessage(__('misc.success_update'));
	}

	public function deleteCategories($id)
	{
		$categories = Categories::find($id);
		$thumbnail  = 'public/img-category/' . $categories->thumbnail; // Path General

		if (!isset($categories) || $categories->id == 1) {
			return redirect('panel/admin/categories');
		} else {
			// Delete Thumbnail
			if (\File::exists($thumbnail)) {
				\File::delete($thumbnail);
			} //<--- IF FILE EXISTS

		// Images functionality removed - this is now a universal starter kit
		// No need to update images table as it doesn't exist

			// Delete Category
			$categories->delete();

			return redirect('panel/admin/categories')->withSuccessMessage(__('admin.success_delete_category'));
		}
	}

	public function settings()
	{
		// Get active APK version for display
		$activeApkVersion = ApkVersion::where('is_active', true)->first();
		$appVersion = $activeApkVersion ? $activeApkVersion->version_name : '1.0';

		return view('admin.settings', compact('appVersion'));
	}

	public function saveSettings(Request $request)
	{

		if ($request->captcha && !config('captcha.sitekey') && !config('captcha.secret')) {
			return back()->withErrors(['error' => __('misc.error_active_captcha')]);
		}


		$rules = array(
			'title'        => 'required',
			'link_terms'   => 'required|url',
			'link_privacy' => 'required|url',
			'link_license' => 'url',
			'link_blog'    => 'url'
		);

		$this->validate($request, $rules);

		$sql                      = AdminSettings::first();
		$sql->title               = $request->title;
		$sql->tagline             = $request->tagline;
		$sql->link_terms          = $request->link_terms;
		$sql->link_privacy        = $request->link_privacy;
		$sql->link_license        = $request->link_license;
		$sql->link_blog           = $request->link_blog;
		$sql->whatsapp_number     = $request->whatsapp_number;
		$sql->whatsapp_group_link = $request->whatsapp_group_link;
		$sql->captcha             = $request->captcha ?? 'off';
		$sql->registration_active = $request->registration_active ?? '0';
		$sql->email_verification  = $request->email_verification ?? '0';
		$sql->theme                = $request->theme;
		$sql->banner_cookies       = $request->banner_cookies ?? false;

		// Note: Version is managed through APK Versions, not stored in admin_settings

		// SEO Settings - Commented out
		// $sql->seo_title            = $request->seo_title;
		// $sql->seo_description      = $request->seo_description;
		// $sql->seo_keywords         = $request->seo_keywords;
		// $sql->og_title             = $request->og_title;
		// $sql->og_description       = $request->og_description;
		// $sql->canonical_url        = $request->canonical_url;

		// Handle OG Image upload - Commented out (SEO section)
		// if ($request->hasFile('og_image')) {
		// 	$temp = 'public/temp/';
		// 	$path = 'public/img/';

		// 	$extension = $request->file('og_image')->getClientOriginalExtension();
		// 	$file = 'og-image-' . time() . '.' . $extension;

		// 	if ($request->file('og_image')->move($temp, $file)) {
		// 		\File::copy($temp . $file, $path . $file);
		// 		\File::delete($temp . $file);

		// 		// Delete old OG image if exists
		// 		if ($sql->og_image && \File::exists($path . $sql->og_image)) {
		// 			\File::delete($path . $sql->og_image);
		// 		}

		// 		$sql->og_image = $file;
		// 	}
		// }

		$sql->save();

		// Default locale
		Helper::envUpdate('DEFAULT_LOCALE', $request->default_language);

		// Timezone
		if ($request->has('timezone')) {
			Helper::envUpdate('TIMEZONE', $request->timezone);
		}

		// App Name
		Helper::envUpdate('APP_NAME', ' "' . $request->title . '" ', true);

		if ($this->settings->who_can_upload == 'all' && $request->who_can_upload == 'admin') {
			User::where('role', '<>', 1)->update([
				'authorized_to_upload' => 'no'
			]);
		} elseif ($this->settings->who_can_upload == 'admin' && $request->who_can_upload == 'all') {
			User::where('role', '<>', 1)->update([
				'authorized_to_upload' => 'yes'
			]);
		}

		return redirect('panel/admin/settings')->withSuccessMessage(__('admin.success_update'));
	}

	public function settingsLimits()
	{
		return view('admin.limits');
	}

	public function saveSettingsLimits(Request $request)
	{


		$sql                      = AdminSettings::first();
		$sql->result_request      = $request->result_request;
		$sql->limit_upload_user   = $request->limit_upload_user;
		$sql->daily_limit_downloads = $request->daily_limit_downloads;
		$sql->title_length        = $request->title_length;
		$sql->message_length      = $request->message_length;
		$sql->comment_length      = $request->comment_length;
		$sql->file_size_allowed   = $request->file_size_allowed;
		$sql->auto_approve_images = $request->auto_approve_images;
		$sql->downloads           = $request->downloads;
		$sql->tags_limit          = $request->tags_limit;
		$sql->description_length  = $request->description_length;
		$sql->min_width_height_image = $request->min_width_height_image;
		$sql->file_size_allowed_vector = $request->file_size_allowed_vector;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/settings/limits');
	}

	public function members_reported()
	{

		$data = UsersReported::orderBy('id', 'DESC')->get();

		return view('admin.members_reported', compact('data'));
	}

	public function delete_members_reported(Request $request)
	{

		$report = UsersReported::find($request->id);

		if (isset($report)) {
			$report->delete();
		}

		return redirect('panel/admin/members-reported');
	}

	/* COMMENTED OUT - Stock photo related functionality
	public function images_reported()
	{

		$data = ImagesReported::orderBy('id', 'DESC')->get();

		//dd($data);

		return view('admin.images_reported', compact('data'));
	}

	public function delete_images_reported(Request $request)
	{

		$report = ImagesReported::find($request->id);

		if (isset($report)) {
			$report->delete();
		}

		return redirect('panel/admin/images-reported');
	}
	END COMMENTED OUT */

	/* COMMENTED OUT - Stock photo related functionality
	public function images()
	{
		$query = request()->get('q');
		$sort = request()->get('sort');
		$pagination = 15;

		$data = Images::orderBy('id', 'desc')->paginate($pagination);

		// Search
		if (isset($query)) {
			$data = Images::where('title', 'LIKE', '%' . $query . '%')
				->orWhere('tags', 'LIKE', '%' . $query . '%')
				->orderBy('id', 'desc')->paginate($pagination);
		}

		// Sort
		if (isset($sort) && $sort == 'title') {
			$data = Images::orderBy('title', 'asc')->paginate($pagination);
		}

		if (isset($sort) && $sort == 'pending') {
			$data = Images::where('status', 'pending')->paginate($pagination);
		}

		if (isset($sort) && $sort == 'downloads') {
			$data = Images::join('downloads', 'images.id', '=', 'downloads.images_id')
				->groupBy('downloads.images_id')
				->orderBy(\DB::raw('COUNT(downloads.images_id)'), 'desc')
				->select('images.*')
				->paginate($pagination);
		}

		if (isset($sort) && $sort == 'likes') {
			$data = Images::join('likes', function ($join) {
				$join->on('likes.images_id', '=', 'images.id')->where('likes.status', '=', '1');
			})
				->groupBy('likes.images_id')
				->orderBy(\DB::raw('COUNT(likes.images_id)'), 'desc')
				->select('images.*')
				->paginate($pagination);
		}

		// return view('admin.images', ['data' => $data, 'query' => $query, 'sort' => $sort]);
	}
	END STOCK PHOTO METHODS */

	// Image management methods removed for universal starter kit

	public function profiles_social()
	{
		return view('admin.profiles-social');
	}

	public function update_profiles_social(Request $request)
	{
		$sql = AdminSettings::find(1);

		$rules = array(
			'twitter'    => 'url',
			'facebook'   => 'url',
			'linkedin'   => 'url',
			'instagram'  => 'url',
			'youtube'  => 'url',
			'pinterest'  => 'url',
		);

		$this->validate($request, $rules);

		$sql->twitter       = $request->twitter;
		$sql->facebook      = $request->facebook;
		$sql->linkedin      = $request->linkedin;
		$sql->instagram     = $request->instagram;
		$sql->youtube     = $request->youtube;
		$sql->pinterest     = $request->pinterest;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/profiles-social');
	}

	public function google()
	{
		return view('admin.google');
	}

	public function update_google(Request $request)
	{
		$sql = AdminSettings::first();

		$sql->google_adsense_index = $request->google_adsense_index;
		$sql->google_adsense   = $request->google_adsense;
		$sql->google_analytics = $request->google_analytics;
		$sql->save();

		foreach ($request->except(['_token']) as $key => $value) {
			Helper::envUpdate($key, $value);
		}

		return redirect('panel/admin/google')->withSuccessMessage(__('admin.success_update'));
	}

	public function theme()
	{
		return view('admin.theme');
	}

	public function themeStore(Request $request)
	{
		$temp  = 'public/temp/'; // Temp
		$path  = 'public/img/'; // Path
		$pathAvatar = config('path.avatar');
		$pathCover = config('path.cover');
		$pathCategory = 'public/img-category/'; // Path Category

		$rules = [
			'logo'   => 'image',
			'logo_light' => 'image',
			'favicon'   => 'image',
			'app_logo'   => 'image',
			'image_header'   => 'image',
			'img_section'   => 'image',
		];

		$this->validate($request, $rules);

		//========== LOGO
		if ($request->hasFile('logo')) {

			$extension = $request->file('logo')->getClientOriginalExtension();
			$file      = 'logo-' . time() . '.' . $extension;

			if ($request->file('logo')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->logo);
			} // End File

			$this->settings->logo = $file;
			$this->settings->save();
		} // HasFile

		//========== LOGO
		if ($request->hasFile('logo_light')) {

			$extension = $request->file('logo_light')->getClientOriginalExtension();
			$file      = 'logo_light-' . time() . '.' . $extension;

			if ($request->file('logo_light')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->logo_light);
			} // End File

			$this->settings->logo_light = $file;
			$this->settings->save();
		} // HasFile

		//======== FAVICON
		if ($request->hasFile('favicon')) {

			$extension  = $request->file('favicon')->getClientOriginalExtension();
			$file       = 'favicon-' . time() . '.' . $extension;

			if ($request->file('favicon')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->favicon);
			} // End File

			$this->settings->favicon = $file;
			$this->settings->save();
		} // HasFile

		//======== APP LOGO
		if ($request->hasFile('app_logo')) {

			$extension  = $request->file('app_logo')->getClientOriginalExtension();
			$file       = 'app_logo-' . time() . '.' . $extension;

			if ($request->file('app_logo')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->app_logo);
			} // End File

			$this->settings->app_logo = $file;
			$this->settings->save();
		} // HasFile

		//======== image_header
		if ($request->hasFile('image_header')) {

			$extension  = $request->file('image_header')->getClientOriginalExtension();
			$file       = 'header_index-' . time() . '.' . $extension;

			if ($request->file('image_header')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->image_header);
			} // End File

			$this->settings->image_header = $file;
			$this->settings->save();
		} // HasFile

		//======== img_section
		if ($request->hasFile('img_section')) {

			$extension  = $request->file('img_section')->getClientOriginalExtension();
			$file       = 'img_section-' . time() . '.' . $extension;

			if ($request->file('img_section')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->img_section);
			} // End File

			$this->settings->img_section = $file;
			$this->settings->save();
		} // HasFile

		//======== Watermark


		// Update Color Default, and Button style
		$this->settings->whereId(1)
			->update([
				'color_default' => $request->color_default
			]);

		//======= CLEAN CACHE
		\Artisan::call('cache:clear');

		return redirect('panel/admin/theme')
			->withSuccessMessage(__('misc.success_update'));
	}

	public function payments()
	{
		return view('admin.payments-settings');
	}

	public function savePayments(Request $request)
	{
		$sql = AdminSettings::first();

		$rules = [
			'currency_code' => 'required|alpha|max:3',
			'currency_symbol' => 'required|max:10',
			'currency_position' => 'required|in:left,right',
			'decimal_format' => 'required|in:dot,comma',
			'min_bond_amount' => 'required|numeric|min:0',
			'max_bond_amount' => 'required|numeric|min:0|gte:min_bond_amount'
		];

		$this->validate($request, $rules);

		$sql->currency_code = strtoupper($request->currency_code);
		$sql->currency_symbol = $request->currency_symbol;
		$sql->currency_position = $request->currency_position;
		$sql->decimal_format = $request->decimal_format;
		$sql->min_bond_amount = $request->min_bond_amount;
		$sql->max_bond_amount = $request->max_bond_amount;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/payments');
	}

	/* COMMENTED OUT - Stock photo related functionality
	public function purchases()
	{
		$data = Purchases::with(['images', 'invoice'])->whereApproved('1')->orderBy('id', 'desc')->paginate(30);
		return view('admin.purchases', compact('data'));
	}
	END COMMENTED OUT */

	public function depositsView($id)
	{
		$data = Deposits::with(['user'])->findOrFail($id);
		return view('admin.deposits-view', compact('data'));
	}

	public function approveDeposits(Request $request)
	{
		$query = Deposits::with(['invoicePending'])->findOrFail($request->id);

		$data = [
			'type' => 'approve',
			'amount' => Helper::amountFormat($query->amount),
			'name' => $query->user()->full_name
		];

		// Send Mail to User
		try {
			$query->user()->notify(new DepositVerification($data));
		} catch (\Exception $e) {
			return back()->withErrors([
				'errors' => $e->getMessage(),
			]);
		}

		$query->status = 'active';
		$query->save();

		// Add Funds to User
		$query->user()->increment('funds', $query->amount);

		// Update Invoice
		$query->invoicePending->update([
			'status' => 'paid'
		]);

		return redirect('panel/admin/deposits');
	}

	public function deleteDeposits(Request $request)
	{
		$path = config('path.admin');
		$query = Deposits::with(['invoicePending'])->findOrFail($request->id);

		if (isset($query->user()->full_name)) {
			$data = [
				'type' => 'not_approve',
				'amount' => Helper::amountFormat($query->amount),
				'name' => $query->user()->full_name
			];

			// Send Mail to User
			try {
				$query->user()->notify(new DepositVerification($data));
			} catch (\Exception $e) {
				return back()->withErrors([
					'errors' => $e->getMessage(),
				]);
			}
		}

		// Delete Image
		Storage::delete($path . $query->screenshot_transfer);

		// Delete Invoice
		$query->invoicePending->delete();

		$query->delete();

		return redirect('panel/admin/deposits');
	}


	public function paymentsGateways($id)
	{
		$data = PaymentGateways::findOrFail($id);
		$name = ucfirst($data->name);

		return view('admin.' . str_slug($name) . '-settings')->withData($data);
	}

	public function savePaymentsGateways($id, Request $request)
	{

		$data = PaymentGateways::findOrFail($id);

		$input = $_POST;

		// Sandbox off
		if (!$request->sandbox) {
			$input['sandbox'] = 'false';
		}

		// Enabled off
		if (!$request->enabled) {
			$input['enabled'] = '0';
		}

		$this->validate($request, [
			'email'    => 'email',
		]);

		$data->fill($input)->save();

		// Set Stripe Keys
		if ($data->name == 'Stripe') {
			Helper::envUpdate('STRIPE_KEY', $input['key']);
			Helper::envUpdate('STRIPE_SECRET', $input['key_secret']);
			Helper::envUpdate('STRIPE_WEBHOOK_SECRET', $input['webhook_secret']);
		}

		// Set PayPal Keys on .env file
		if ($data->name == 'PayPal') {
			if (!$request->sandbox) {
				Helper::envUpdate('PAYPAL_MODE', 'live');
				Helper::envUpdate('PAYPAL_LIVE_CLIENT_ID', $input['key']);
				Helper::envUpdate('PAYPAL_LIVE_CLIENT_SECRET', $input['key_secret']);
			} else {
				Helper::envUpdate('PAYPAL_MODE', 'sandbox');
				Helper::envUpdate('PAYPAL_SANDBOX_CLIENT_ID', $input['key']);
				Helper::envUpdate('PAYPAL_SANDBOX_CLIENT_SECRET', $input['key_secret']);
			}

			Helper::envUpdate('PAYPAL_WEBHOOK_ID', $input['webhook_secret']);
		} // PayPal

		// Set Paystack Keys
		if ($data->name == 'Paystack') {
			Helper::envUpdate('PAYSTACK_PUBLIC_KEY', $input['key']);
			Helper::envUpdate('PAYSTACK_SECRET_KEY', $input['key_secret']);
			Helper::envUpdate('MERCHANT_EMAIL', $input['email']);
		}

		// Set Flutterwave Keys
		if ($data->name == 'Flutterwave') {
			Helper::envUpdate('FLW_PUBLIC_KEY', $input['key']);
			Helper::envUpdate('FLW_SECRET_KEY', $input['key_secret']);
		}

		return back()->withSuccessMessage(__('admin.success_update'));
	}

	public function maintenance(Request $request)
	{
		$strRandom = str_random(50);

		if ($request->maintenance_mode) {
			\Artisan::call('down', [
				'--secret' => $strRandom
			]);
		} elseif (!$request->maintenance_mode) {
			\Artisan::call('up');
		}

		$this->settings->maintenance_mode = $request->maintenance_mode;
		$this->settings->save();

		if ($request->maintenance_mode) {
			return redirect($strRandom)
				->withSuccessMessage(trans('misc.maintenance_mode_on'));
		} else {
			return redirect('panel/admin/maintenance')
				->withSuccessMessage(trans('misc.maintenance_mode_off'));
		}
	}

	// Show billing page with payment methods
	public function billing()
	{
		$paymentMethods = PaymentMethod::ordered()->get();
		return view('admin.billing', compact('paymentMethods'));
	}

	// Store new payment method
	public function storePaymentMethod(Request $request)
	{
		$request->validate([
			'bank_or_account_name' => 'required|string|max:255',
			'account_title' => 'required|string|max:255',
			'account_no' => 'required|string|max:100',
			'bank_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
			'is_active' => 'required|in:0,1',
			'sort_order' => 'integer|min:0',
			'minimum_limit' => 'nullable|numeric|min:0',
			'maximum_limit' => 'nullable|numeric|min:0|gte:minimum_limit'
		]);

		$paymentMethod = new PaymentMethod();
		$paymentMethod->bank_or_account_name = $request->bank_or_account_name;
		$paymentMethod->account_title = $request->account_title;
		$paymentMethod->account_no = $request->account_no;
		$paymentMethod->is_active = (bool) $request->is_active;
		$paymentMethod->sort_order = $request->sort_order ?? 0;
		$paymentMethod->minimum_limit = $request->minimum_limit;
		$paymentMethod->maximum_limit = $request->maximum_limit;

		// Handle image upload
		if ($request->hasFile('bank_image')) {
			$paymentMethod->bank_image = $this->handleImageUpload($request->file('bank_image'));
		}

		$paymentMethod->save();

		return back()->withSuccessMessage('Payment method added successfully.');
	}

	// Get payment method for editing
	public function getPaymentMethod($id)
	{
		$paymentMethod = PaymentMethod::findOrFail($id);
		return response()->json($paymentMethod);
	}

	// Update payment method
	public function updatePaymentMethod(Request $request)
	{
		$request->validate([
			'method_id' => 'required|exists:payment_methods,id',
			'bank_or_account_name' => 'required|string|max:255',
			'account_title' => 'required|string|max:255',
			'account_no' => 'required|string|max:100',
			'bank_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
			'is_active' => 'required|in:0,1',
			'sort_order' => 'integer|min:0',
			'minimum_limit' => 'nullable|numeric|min:0',
			'maximum_limit' => 'nullable|numeric|min:0|gte:minimum_limit'
		]);

		$paymentMethod = PaymentMethod::findOrFail($request->method_id);
		$paymentMethod->bank_or_account_name = $request->bank_or_account_name;
		$paymentMethod->account_title = $request->account_title;
		$paymentMethod->account_no = $request->account_no;
		$paymentMethod->is_active = (bool) $request->is_active;
		$paymentMethod->sort_order = $request->sort_order ?? 0;
		$paymentMethod->minimum_limit = $request->minimum_limit;
		$paymentMethod->maximum_limit = $request->maximum_limit;

		// Handle image upload
		if ($request->hasFile('bank_image')) {
			// Delete old image if exists
			if ($paymentMethod->bank_image && \File::exists('public/img/' . $paymentMethod->bank_image)) {
				\File::delete('public/img/' . $paymentMethod->bank_image);
			}
			$paymentMethod->bank_image = $this->handleImageUpload($request->file('bank_image'));
		}

		$paymentMethod->save();

		return back()->withSuccessMessage('Payment method updated successfully.');
	}

	// Delete payment method
	public function deletePaymentMethod($id)
	{
		$paymentMethod = PaymentMethod::findOrFail($id);

		// Delete associated image
		if ($paymentMethod->bank_image && \File::exists('public/img/' . $paymentMethod->bank_image)) {
			\File::delete('public/img/' . $paymentMethod->bank_image);
		}

		$paymentMethod->delete();

		return back()->withSuccessMessage('Payment method deleted successfully.');
	}

	// Helper method for image upload
	private function handleImageUpload($file)
	{
		try {
			$temp = 'public/temp/';
			$path = 'public/img/';

			// Ensure directories exist
			if (!\File::exists($temp)) {
				\File::makeDirectory($temp, 0755, true);
			}
			if (!\File::exists($path)) {
				\File::makeDirectory($path, 0755, true);
			}

			$extension = $file->getClientOriginalExtension();
			$fileName = 'payment-method-' . time() . '-' . uniqid() . '.' . $extension;

			// Move file to temp directory first
			if ($file->move($temp, $fileName)) {
				// Copy to final location
				if (\File::copy($temp . $fileName, $path . $fileName)) {
					// Delete temp file
					\File::delete($temp . $fileName);
					return $fileName;
				} else {
					// Clean up temp file if copy failed
					\File::delete($temp . $fileName);
					throw new \Exception('Failed to save image');
				}
			} else {
				throw new \Exception('Failed to upload image');
			}
		} catch (\Exception $e) {
			\Log::error('Payment method image upload error: ' . $e->getMessage());
			throw $e;
		}
	}

	// Method to delete bank image
	public function deleteBankImage()
	{
		try {
			$path = 'public/img/';

			// Delete the image file if it exists
			if ($this->settings->bank_image && \File::exists($path . $this->settings->bank_image)) {
				\File::delete($path . $this->settings->bank_image);
			}

			// Remove from database
			$this->settings->bank_image = null;
		$this->settings->save();

			return back()->withSuccessMessage('Bank image deleted successfully.');
		} catch (\Exception $e) {
			\Log::error('Bank image deletion error: ' . $e->getMessage());
			return back()->withErrors(['error' => 'Failed to delete bank image. Please try again.']);
		}
	}

	public function emailSettings(Request $request)
	{
		$request->validate([
			'MAIL_FROM_ADDRESS' => 'required'
		]);

		$request->MAIL_ENCRYPTION = strtolower($request->MAIL_ENCRYPTION);

		$this->settings->email_admin = $request->email_admin;
		$this->settings->email_no_reply = $request->MAIL_FROM_ADDRESS;
		$this->settings->save();

		foreach ($request->except(['_token']) as $key => $value) {
			Helper::envUpdate($key, $value);
		}

		return back()->withSuccessMessage(trans('admin.success_update'));
	} // End Method

	public function storage(Request $request)
	{
		$messages = [
			'APP_URL.required' => trans('validation.required', ['attribute' => 'App URL']),
			'APP_URL.url' => trans('validation.url', ['attribute' => 'App URL'])
		];

		$request->validate([
			'APP_URL' => 'required|url',
			'AWS_ACCESS_KEY_ID' => 'required_if:FILESYSTEM_DRIVER,==,s3',
			'AWS_SECRET_ACCESS_KEY' => 'required_if:FILESYSTEM_DRIVER,==,s3',
			'AWS_DEFAULT_REGION' => 'required_if:FILESYSTEM_DRIVER,==,s3',
			'AWS_BUCKET' => 'required_if:FILESYSTEM_DRIVER,==,s3',

			'DOS_ACCESS_KEY_ID' => 'required_if:FILESYSTEM_DRIVER,==,dospace',
			'DOS_SECRET_ACCESS_KEY' => 'required_if:FILESYSTEM_DRIVER,==,dospace',
			'DOS_DEFAULT_REGION' => 'required_if:FILESYSTEM_DRIVER,==,dospace',
			'DOS_BUCKET' => 'required_if:FILESYSTEM_DRIVER,==,dospace',

			'WAS_ACCESS_KEY_ID' => 'required_if:FILESYSTEM_DRIVER,==,wasabi',
			'WAS_SECRET_ACCESS_KEY' => 'required_if:FILESYSTEM_DRIVER,==,wasabi',
			'WAS_DEFAULT_REGION' => 'required_if:FILESYSTEM_DRIVER,==,wasabi',
			'WAS_BUCKET' => 'required_if:FILESYSTEM_DRIVER,==,wasabi',

			'VULTR_ACCESS_KEY' => 'required_if:FILESYSTEM_DRIVER,==,vultr',
			'VULTR_SECRET_KEY' => 'required_if:FILESYSTEM_DRIVER,==,vultr',
			'VULTR_REGION' => 'required_if:FILESYSTEM_DRIVER,==,vultr',
			'VULTR_BUCKET' => 'required_if:FILESYSTEM_DRIVER,==,vultr',
		], $messages);

		foreach ($request->except(['_token']) as $key => $value) {

			if ($value == $request->APP_URL) {
				$value = trim($value, '/');
			}

			Helper::envUpdate($key, $value);
		}

		return back()->withSuccessMessage(trans('admin.success_update'));
	} // End Method

	public function updateSocialLogin(Request $request)
	{
		$this->settings->facebook_login = $request->facebook_login ?? 'off';
		$this->settings->google_login   = $request->google_login ?? 'off';
		$this->settings->twitter_login  = $request->twitter_login ?? 'off';
		$this->settings->save();

		foreach ($request->except(['_token']) as $key => $value) {
			Helper::envUpdate($key, $value);
		}

		\Session::flash('success_message', trans('admin.success_update'));
		return back();
	}

	public function pwa(Request $request)
	{
		$allImgs = $request->file('files');

		if ($allImgs) {
			foreach ($allImgs as $key => $file) {

				$filename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
				$file->move(public_path('images/icons'), $filename);

				\File::delete(env($key));

				$envIcon = 'public/images/icons/' . $filename;
				Helper::envUpdate($key, $envIcon);
			}
		}

		// Updaye Short Name
		Helper::envUpdate('PWA_SHORT_NAME', ' "' . $request->PWA_SHORT_NAME . '" ', true);

		$sql = $this->settings;
		$sql->status_pwa = $request->status_pwa;
		$sql->save();

		\Artisan::call('cache:clear');
		\Artisan::call('view:clear');

		return back()->withSuccessMessage(trans('admin.success_update'));
	}

	public function subscriptions()
	{
		$subscriptions = Subscriptions::orderBy('id', 'DESC')->paginate(50);
		return view('admin.subscriptions', ['subscriptions' => $subscriptions]);
	}

	/* COMMENTED OUT - Stock photo related functionality
	public function collections()
	{
		$data = Collections::with('collectionImages')
			->with('creator')
			->orderBy('id', 'DESC')->paginate(30);

		return view('admin.collections', compact('data'));
	}

	public function deleteCollection(Request $request)
	{
		$collection = Collections::findOrFail($request->id);

		// Delete images on collection
		CollectionsImages::whereCollectionsId($collection->id)->delete();

		$collection->delete();

		return redirect('panel/admin/collections');
	}
	END COMMENTED OUT */

	public function clearCache()
	{
		// Clear Cache, Config and Views
		\Artisan::call('cache:clear');
		\Artisan::call('config:clear');
		\Artisan::call('view:clear');

		$pathLogFile = storage_path("logs" . DIRECTORY_SEPARATOR . "laravel.log");

		try {
			collect(Storage::disk('default')->listContents('.cache', true))
				->each(function ($file) {
					Storage::disk('default')->deleteDirectory($file['path']);
					Storage::disk('default')->delete($file['path']);
				});

			// Delete Log file
			if (auth()->user()->isSuperAdmin()) {
				if (file_exists($pathLogFile)) {
					unlink($pathLogFile);
				}
			}
		} catch (\Exception $e) {
		}

		return redirect('panel/admin/maintenance')
			->withSuccessMessage(trans('admin.successfully_cleaned'));
	} // End method

	public function customCssJs(Request $request)
	{
		$sql = $this->settings;
		$sql->custom_css = $request->custom_css;
		$sql->custom_js = $request->custom_js;
		$sql->save();

		return back()->withSuccessMessage(trans('admin.success_update'));
	} // End method

	public function storeAnnouncements(Request $request)
	{
		$this->settings->announcement = $request->announcement_content;
		$this->settings->type_announcement = $request->type_announcement;
		$this->settings->announcement_show = $request->announcement_show;
		$this->settings->announcement_cookie = str_random(25);
		$this->settings->save();

		return back()->withSuccessMessage(trans('admin.success_update'));
	} // End method

	public function subcategories()
	{
		$subcategories = Subcategories::with(['category'])->orderBy('name')->paginate(20);
		$totalSubcategoriesCategories = $subcategories->count();

		return view('admin.subcategories')->with([
			'subcategories' => $subcategories,
			'totalSubcategoriesCategories' => $totalSubcategoriesCategories,
		]);
	}

	public function addSubcategories()
	{
		return view('admin.add-subcategories');
	}

	public function storeSubcategories(Request $request)
	{
		Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
			return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		$rules = [
			'name' => 'nullable',
			'category' => 'required',
			'start_date' => 'nullable|date',
			'start_time' => 'nullable',
			'close_date' => 'nullable|date',
			'close_time' => 'nullable',
		];

		$this->validate($request, $rules);

		$sql              = new Subcategories();
		$sql->name        = $request->name ? trim($request->name) : null;
		$sql->category_id = $request->category;
		$sql->mode        = $request->mode ?? 'off';
		$sql->start_date  = $request->start_date;
		$sql->start_time  = $request->start_time;
		$sql->close_date  = $request->close_date;
		$sql->close_time  = $request->close_time;
		$sql->save();

		return redirect('panel/admin/subcategories')
			->withSuccessMessage(__('misc.successfully_added'));
	}

	public function editSubcategories($id)
	{
		$subcategory = Subcategories::find($id);

		return view('admin.edit-subcategories')->with('subcategory', $subcategory);
	}

	public function updateSubcategories(Request $request)
	{
		$subcategory = Subcategories::findOrFail($request->id);

		Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
			return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		$rules = [
			'name'      => 'nullable',
			'category' => 'required',
			'start_date' => 'nullable|date',
			'start_time' => 'nullable',
			'close_date' => 'nullable|date',
			'close_time' => 'nullable',
		];

		$this->validate($request, $rules);

		// UPDATE SUBCATEGORY
		$subcategory->name       = $request->name ? trim($request->name) : null;
		$subcategory->category_id  = $request->category;
		$subcategory->mode       = $request->mode ?? 'off';
		$subcategory->start_date = $request->start_date;
		$subcategory->start_time = $request->start_time;
		$subcategory->close_date = $request->close_date;
		$subcategory->close_time = $request->close_time;
		$subcategory->save();

		return redirect('panel/admin/subcategories')
			->withSuccessMessage(__('misc.success_update'));
	}

	public function deleteSubcategories($id)
	{
		Subcategories::find($id)->delete();

		return redirect('panel/admin/subcategories')
			->withSuccessMessage(__('misc.successfully_removed'));
	}

	public function savePushNotifications(Request $request)
	{
		$this->settings->push_notification_status  = $request->push_notification_status;
		$this->settings->onesignal_appid           = $request->onesignal_appid;
		$this->settings->onesignal_restapi         = $request->onesignal_restapi;
		$this->settings->save();

		return back()->withSuccessMessage(__('admin.success_update'));
	}

	// Deposit Management
	public function deposits(Request $request) // Updated to accept Request
	{
		$query = Deposits::with(['user', 'paymentMethod']);

		// Normal view: Exclude approved dealers
		$query->whereHas('user', function ($q) {
			$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
		});

		// Use the base query for all lists to respect the filter
		$allDeposits = (clone $query)->latest()->paginate(20);
		$pendingDeposits = (clone $query)->pending()->latest()->paginate(20);
		$approvedDeposits = (clone $query)->approved()->latest()->paginate(20);
		$rejectedDeposits = (clone $query)->rejected()->latest()->paginate(20);

		return view('admin.deposits', compact('allDeposits', 'pendingDeposits', 'approvedDeposits', 'rejectedDeposits'));
	}

	public function approveDeposit(Request $request)
	{
		$request->validate([
			'deposit_id' => 'required|exists:deposits,id',
			'admin_notes' => 'nullable|string|max:1000'
		]);

		$deposit = Deposits::findOrFail($request->deposit_id);

		if ($deposit->status !== 'pending') {
			return back()->withErrorMessage('This deposit has already been processed.');
		}

		$deposit->status = 'approved';
		$deposit->admin_notes = $request->admin_notes;
		$deposit->save();

		// Get admin role name
		$adminRole = auth()->user()->role() ? auth()->user()->role()->name : 'Admin';

		// Log transaction and update user balance
		TransactionService::logDeposit(
			$deposit->user_id,
			$deposit->amount,
			$deposit->id,
			'Deposit approved - ' . ($request->admin_notes ?: 'No notes'),
			$adminRole
		);

		// Send notification to user
		$deposit->user->notify(new \App\Notifications\DepositVerification($deposit, 'approved'));

		return back()->withSuccessMessage('Deposit approved successfully. Amount has been added to user balance.');
	}

	public function rejectDeposit(Request $request)
	{
		$request->validate([
			'deposit_id' => 'required|exists:deposits,id',
			'admin_notes' => 'required|string|max:1000'
		]);

		$deposit = Deposits::findOrFail($request->deposit_id);

		if ($deposit->status !== 'pending') {
			return back()->withErrorMessage('This deposit has already been processed.');
		}

		$deposit->status = 'rejected';
		$deposit->admin_notes = $request->admin_notes;
		$deposit->save();

		// Get admin role name
		$adminRole = auth()->user()->role() ? auth()->user()->role()->name : 'Admin';

		// Create notification for user
		TransactionService::logDepositRejection(
			$deposit->user_id,
			$deposit->amount,
			$deposit->id,
			$request->admin_notes,
			$adminRole
		);

		// Send notification to user
		$deposit->user->notify(new \App\Notifications\DepositVerification($deposit, 'rejected'));

		return back()->withSuccessMessage('Deposit rejected successfully.');
	}

	// Orders Management
	public function orders(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return view('admin.unauthorized');
		}

		$query = Orders::query();

		// Normal view: Exclude approved dealers
		$query->whereHas('user', function ($q) {
			$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
		});

		// Filter by category if provided
		if ($request->has('category') && !empty($request->category)) {
			$categoryName = $request->category;
			$query->where('game_name', 'like', '%' . $categoryName . '%');
		}

		// RTTP filter - search only in RTTP column, no pagination
		if ($request->has('rttp_filter') && !empty($request->rttp_filter)) {
			$rttpTerm = $request->rttp_filter;
			$query->where('rttp', $rttpTerm);
			// Get all results without pagination for RTTP filter
			$data = $query->orderBy('id', 'DESC')->get();
		} else {
			// Regular search functionality
			if ($request->has('q') && !empty($request->q)) {
				$searchTerm = $request->q;
				$query->where(function($q) use ($searchTerm) {
					$q->where('username', 'like', '%' . $searchTerm . '%')
					  ->orWhere('user_phone', 'like', '%' . $searchTerm . '%')
					  ->orWhere('game_name', 'like', '%' . $searchTerm . '%')
					  ->orWhere('bond_name', 'like', '%' . $searchTerm . '%')
					  ->orWhere('rttp', 'like', '%' . $searchTerm . '%')
					  ->orWhere('first', 'like', '%' . $searchTerm . '%')
					  ->orWhere('second', 'like', '%' . $searchTerm . '%');
				});
			}

			$data = $query->orderBy('id', 'DESC')->paginate(20);
		}

		// Get all categories for the sidebar
		$categories = Categories::orderBy('name')->get();

		return view('admin.orders', compact('data', 'categories'));
	}

	// Export Orders to PDF
	public function exportOrdersPdf(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return view('admin.unauthorized');
		}

		$query = Orders::query();

		// Scope to dealers or non-dealers based on source page
		if ($request->boolean('filter_dealers')) {
			$query->whereHas('user', function ($q) {
				$q->dealers();
			});
		} else {
			// Match the main orders list behaviour: exclude approved dealers
			$query->whereHas('user', function ($q) {
				$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
			});
		}

		// Filter by category if provided
		if ($request->has('category') && !empty($request->category)) {
			$categoryName = $request->category;
			$query->where('game_name', 'like', '%' . $categoryName . '%');
		} else {
			$categoryName = 'All Categories';
		}

		// Filter by dealer if provided
		if ($request->has('dealer_id') && !empty($request->dealer_id)) {
			$query->where('user_id', $request->dealer_id);
		}

		// RTTP filter
		if ($request->has('rttp_filter') && !empty($request->rttp_filter)) {
			$query->where('rttp', $request->rttp_filter);
		}

		// Text search filter
		if ($request->has('q') && !empty($request->q)) {
			$searchTerm = $request->q;
			$query->where(function ($q) use ($searchTerm) {
				$q->where('username', 'like', '%' . $searchTerm . '%')
					->orWhere('user_phone', 'like', '%' . $searchTerm . '%')
					->orWhere('game_name', 'like', '%' . $searchTerm . '%')
					->orWhere('bond_name', 'like', '%' . $searchTerm . '%')
					->orWhere('rttp', 'like', '%' . $searchTerm . '%')
					->orWhere('first', 'like', '%' . $searchTerm . '%')
					->orWhere('second', 'like', '%' . $searchTerm . '%');
			});
		}

		// Get all orders (no pagination for PDF)
		$orders = $query->orderBy('id', 'DESC')->get();
		$totalOrders = $orders->count();

		// Get settings
		$settings = $this->settings;

		// Generate PDF
		$pdf = PDF::loadView('admin.orders-pdf', compact('orders', 'categoryName', 'totalOrders', 'settings'));
		$pdf->setPaper('A4', 'landscape');

		$filename = 'orders_' . ($categoryName !== 'All Categories' ? strtolower(str_replace(' ', '_', $categoryName)) : 'all') . '_' . date('Y-m-d_H-i-s') . '.pdf';

		return $pdf->download($filename);
	}

	// Export Winner Orders to PDF
	public function exportWinnerPdf(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return view('admin.unauthorized');
		}

		$query = Orders::query();

		// Filter by category if provided
		if ($request->has('category') && !empty($request->category)) {
			$categoryName = $request->category;
			$query->where('game_name', 'like', '%' . $categoryName . '%');
		}

		// RTTP filter - must be provided for winner export
		if ($request->has('rttp_filter') && !empty($request->rttp_filter)) {
			$rttpTerm = $request->rttp_filter;
			$query->where('rttp', $rttpTerm);
            // Exclude rejected orders
            $query->where('status', '!=', 'rejected');
		} else {
			return back()->withErrorMessage('RTTP filter is required for winner export.');
		}

		// Get all winner orders
		$orders = $query->orderBy('id', 'DESC')->get();
		$categoryName = $request->get('category', 'All Categories');
		$totalWinners = $orders->count();
		$winningNumber = $request->get('rttp_filter');

		// Get settings
		$settings = $this->settings;

		// Generate PDF
		$pdf = PDF::loadView('admin.winner-orders-pdf', compact('orders', 'categoryName', 'totalWinners', 'winningNumber', 'settings'));
		$pdf->setPaper('A4', 'landscape');

		$filename = 'winners_' . ($categoryName !== 'All Categories' ? strtolower(str_replace(' ', '_', $categoryName)) : 'all') . '_' . $winningNumber . '_' . date('Y-m-d_H-i-s') . '.pdf';

		return $pdf->download($filename);
	}

	// Transactions Management
	public function transactions(Request $request)
	{
		if (!auth()->user()->hasPermission('transactions')) {
			return view('admin.unauthorized');
		}

		$query = Transaction::with(['user']);

		// Normal view: Exclude approved dealers
		$query->whereHas('user', function ($q) {
			$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
		});

		// Filter by user if provided
		if ($request->has('user_id') && !empty($request->user_id)) {
			$query->where('user_id', $request->user_id);
		}

		// Filter by transaction type if provided
		if ($request->has('transaction_type') && !empty($request->transaction_type)) {
			$query->where('transaction_type', $request->transaction_type);
		}

		// Filter by type (credit/debit) if provided
		if ($request->has('type') && !empty($request->type)) {
			$query->where('type', $request->type);
		}

		// Search functionality
		if ($request->has('q') && !empty($request->q)) {
			$searchTerm = $request->q;
			$query->where(function($q) use ($searchTerm) {
				$q->where('description', 'like', '%' . $searchTerm . '%')
				  ->orWhere('reference_id', 'like', '%' . $searchTerm . '%')
				  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
					  $userQuery->where('username', 'like', '%' . $searchTerm . '%')
								->orWhere('phone', 'like', '%' . $searchTerm . '%');
				  });
			});
		}

		$data = $query->orderBy('created_at', 'DESC')->paginate(20);

		// Get all users for filter dropdown
		$users = User::orderBy('username')->get();

		return view('admin.transactions', compact('data', 'users'));
	}


	public function store_orders(Request $request)
	{
		$rules = [
			'username' => 'required|string|max:255',
			'user_phone' => 'required|string|max:20',
			'game_name' => 'required|string|max:255',
			'bond_name' => 'required|string|max:255',
			'rttp' => 'required|string|max:255',
			'first' => 'required|string|max:255',
			'second' => 'required|string|max:255',
			'status' => 'required|in:OK,first_win,second_win,rejected',
			'n_p' => 'nullable|in:N,P'
		];

		$this->validate($request, $rules);

		// Strict Server-Side Time Limit Check
		$category = Categories::where('name', $request->game_name)->first();
		if ($category) {
			$subcategory = Subcategories::where('name', $request->bond_name)
				->where('category_id', $category->id)
				->first();

			if ($subcategory) {
				// Check if close date and time are set
				if (!empty($subcategory->close_date) && !empty($subcategory->close_time)) {
					try {
						$closeDateTime = \Carbon\Carbon::parse($subcategory->close_date . ' ' . $subcategory->close_time);

						if (now()->greaterThan($closeDateTime)) {
							return back()->withErrors(['error' => 'Order placement is closed for this game. Closed at: ' . $closeDateTime->format('Y-m-d H:i:s')])->withInput();
						}
					} catch (\Exception $e) {
						\Log::error('Date parsing error in store_orders: ' . $e->getMessage());
					}
				}
			}
		}

		Orders::create($request->all());

		return back()->withSuccessMessage('Order created successfully.');
	}

	public function update_orders(Request $request)
	{
		$order = Orders::findOrFail($request->id);

		$rules = [
			'username' => 'required|string|max:255',
			'user_phone' => 'required|string|max:20',
			'game_name' => 'required|string|max:255',
			'bond_name' => 'required|string|max:255',
			'rttp' => 'required|string|max:255',
			'first' => 'required|string|max:255',
			'second' => 'required|string|max:255',
			'status' => 'required|in:OK,first_win,second_win,rejected',
			'n_p' => 'nullable|in:N,P'
		];

		$this->validate($request, $rules);

		// Check if status is being changed to 'rejected' and process refund
		$oldStatus = $order->status;
		$newStatus = $request->status;

		\Log::info('Order Update - Status Change', [
			'order_id' => $order->id,
			'old_status' => $oldStatus,
			'new_status' => $newStatus
		]);

		// Process refund if order is being rejected
		if ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
			// Check if refund already processed for this order
			$existingRefund = Transaction::where('reference_id', $order->id)
				->where('reference_type', 'Orders')
				->where('transaction_type', 'refund')
				->first();

			if ($existingRefund) {
				\Log::warning('Refund Already Processed', [
					'order_id' => $order->id,
					'transaction_id' => $existingRefund->id
				]);
			} else {
				// Calculate total amount from first + second (the actual amount user paid)
				$refundAmount = floatval($order->first) + floatval($order->second);
				$user = User::find($order->user_id);

				\Log::info('Processing Refund', [
					'order_id' => $order->id,
					'user_id' => $order->user_id,
					'first' => $order->first,
					'second' => $order->second,
					'total_refund_amount' => $refundAmount,
					'user_balance_before' => $user ? $user->balance : 'User not found'
				]);

				if ($user && $refundAmount > 0) {
					// Get user's current balance before refund
					$currentBalance = floatval($user->balance);

					// Add only the exact order amount back to balance
					$newBalance = $currentBalance + $refundAmount;
					$user->balance = $newBalance;
					$user->save();

					\Log::info('User Balance Updated', [
						'user_id' => $user->id,
						'previous_balance' => $currentBalance,
						'refund_amount' => $refundAmount,
						'new_balance' => $newBalance
					]);

					// Create transaction record
					$transaction = new Transaction();
					$transaction->user_id = $user->id;
					$transaction->current_balance = $currentBalance;
					$transaction->transaction_amount = $refundAmount;
					$transaction->type = 'credit';
					$transaction->remaining_balance = $newBalance;
					$transaction->transaction_type = 'refund';
					$transaction->description = 'Refund for rejected order #' . $order->id . ' (' . $order->game_name . ' - ' . $order->bond_name . ')';
					$transaction->reference_id = $order->id;
					$transaction->reference_type = 'Orders';
					$transaction->save();

					\Log::info('Refund Transaction Created', [
						'transaction_id' => $transaction->id,
						'user_id' => $user->id,
						'refund_amount' => $refundAmount,
						'first' => $order->first,
						'second' => $order->second
					]);
				} else {
					\Log::error('Refund Failed', [
						'order_id' => $order->id,
						'user_found' => $user ? true : false,
						'refund_amount' => $refundAmount
					]);
				}
			}
		}

		$order->update($request->all());

		// Send notification to user about order status update
		NotificationService::notifyOrderStatusUpdated(
			$order->user_id,
			$newStatus,
			$order->game_name,
			[
				'id' => $order->id,
				'bond_name' => $order->bond_name,
				'rttp' => $order->rttp,
				'first' => $order->first,
				'second' => $order->second
			]
		);

		return back()->withSuccessMessage('Order updated successfully.');
	}

	public function updateOrderStatus(Request $request)
	{
		try {
			$request->validate([
				'order_id' => 'required|exists:orders,id',
				'status' => 'required|in:OK,first_win,second_win,rejected'
			]);

			$order = Orders::findOrFail($request->order_id);
			$oldStatus = $order->status;
			$refundAmount = 0;
			$refundProcessed = false;

			Log::info('Order status update attempt', [
				'order_id' => $order->id,
				'old_status' => $oldStatus,
				'new_status' => $request->status,
				'order_amount' => $order->rttp,
				'user_id' => $order->user_id
			]);

			// If order is rejected, refund the amount to user BEFORE updating status
			if ($request->status === 'rejected' && $oldStatus !== 'rejected') {
				$user = User::findOrFail($order->user_id);
				$refundAmount = floatval($order->rttp ?? 0);

				Log::info('Refund check', [
					'refund_amount' => $refundAmount,
					'user_id' => $user->id,
					'current_balance' => $user->balance
				]);

				if ($refundAmount > 0) {
					// Get current balance
					$currentBalance = floatval($user->balance ?? 0);
					$newBalance = $currentBalance + $refundAmount;

					// Update user balance
					$user->balance = $newBalance;
					$user->save();

					// Log refund transaction
					$transaction = Transaction::create([
						'user_id' => $user->id,
						'current_balance' => $currentBalance,
						'transaction_amount' => $refundAmount,
						'type' => 'credit',
						'remaining_balance' => $newBalance,
						'transaction_type' => 'refund',
						'description' => 'Order #' . $order->id . ' refunded - ' . $order->game_name,
						'reference_id' => $order->id,
						'reference_type' => 'order',
						'metadata' => json_encode([
							'order_id' => $order->id,
							'game_name' => $order->game_name,
							'bond_name' => $order->bond_name,
							'refund_reason' => 'Order rejected by admin'
						])
					]);

					$refundProcessed = true;

					Log::info('Order refunded successfully', [
						'order_id' => $order->id,
						'user_id' => $user->id,
						'amount' => $refundAmount,
						'old_balance' => $currentBalance,
						'new_balance' => $newBalance,
						'transaction_id' => $transaction->id
					]);
				} else {
					Log::warning('No refund amount for rejected order', [
						'order_id' => $order->id,
						'rttp' => $order->rttp
					]);
				}
			}

			// Now update the order status
			$order->status = $request->status;
			$order->save();

			// Send notification to user about order status update
			NotificationService::notifyOrderStatusUpdated(
				$order->user_id,
				$request->status,
				$order->game_name,
				[
					'id' => $order->id,
					'bond_name' => $order->bond_name,
					'rttp' => $order->rttp,
					'first' => $order->first,
					'second' => $order->second
				]
			);

			$message = 'Order status updated successfully.';
			if ($refundProcessed) {
				$message .= ' Amount of ' . number_format($refundAmount, 2) . ' refunded to user.';
			}

			return response()->json([
				'success' => true,
				'message' => $message,
				'refund_processed' => $refundProcessed,
				'refund_amount' => $refundAmount
			]);

		} catch (\Exception $e) {
			Log::error('Update Order Status Error: ' . $e->getMessage(), [
				'order_id' => $request->order_id ?? 'unknown',
				'trace' => $e->getTraceAsString()
			]);
			return response()->json([
				'success' => false,
				'message' => 'Failed to update order status: ' . $e->getMessage()
			], 500);
		}
	}

	public function updateOrderNp(Request $request)
	{
		try {
			$request->validate([
				'order_id' => 'required|exists:orders,id',
				'n_p' => 'required|in:N,P'
			]);

			$order = Orders::findOrFail($request->order_id);
			$order->n_p = $request->n_p;
			$order->save();

			return response()->json([
				'success' => true,
				'message' => 'N/P updated successfully.'
			]);

		} catch (\Exception $e) {
			\Log::error('Update Order N/P Error: ' . $e->getMessage());
			return response()->json([
				'success' => false,
				'message' => 'Failed to update N/P.'
			], 500);
		}
	}

	public function delete_orders(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return back()->withErrorMessage('You do not have permission to delete orders.');
		}

		$order = Orders::findOrFail($request->id);
		$order->delete();

		return back()->withSuccessMessage('Order deleted successfully.');
	}

	// Delete All Orders (Soft Delete)
	public function deleteAllOrders(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return back()->withErrorMessage('You do not have permission to delete orders.');
		}

		$request->validate([
			'confirm' => 'required|in:DELETE_ALL_ORDERS'
		], [
			'confirm.required' => 'Please confirm the deletion by typing DELETE_ALL_ORDERS',
			'confirm.in' => 'Confirmation text must be exactly: DELETE_ALL_ORDERS'
		]);

		// Build query for orders
		$query = Orders::query();

		// Filter by dealers if requested
		if ($request->has('filter_dealers') && $request->filter_dealers) {
			$query->whereHas('user', function ($q) {
				$q->where('dealer_status', 'approved');
			});
		} else {
            // If not explicitly filtering FOR dealers, exclude approved dealers
            // This ensures "Delete All" in normal view only deletes normal orders
            $query->whereHas('user', function ($q) {
                $q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
            });
        }

		// Filter by category if provided
		if ($request->has('category') && !empty($request->category)) {
			$categoryName = $request->category;
			$query->where('game_name', 'like', '%' . $categoryName . '%');
		}

		// Get filtered orders
		$orders = $query->get();
		$deletedCount = 0;

		foreach ($orders as $order) {
			// Copy to soft deleted table
			OrdersSoftDeleted::create([
				'original_id' => $order->id,
				'user_id' => $order->user_id,
				'username' => $order->username,
				'user_phone' => $order->user_phone,
				'game_name' => $order->game_name,
				'bond_name' => $order->bond_name,
				'rttp' => $order->rttp,
				'first' => $order->first,
				'second' => $order->second,
				'status' => $order->status,
				'deleted_at' => now(),
				'original_created_at' => $order->created_at,
				'original_updated_at' => $order->updated_at,
			]);

			// Delete from main table
			$order->delete();
			$deletedCount++;
		}

		$categoryText = $request->has('category') ? " {$request->category}" : '';
		$dealerText = ($request->has('filter_dealers') && $request->filter_dealers) ? " Dealer" : "";
		return back()->withSuccessMessage("Successfully soft deleted {$deletedCount}{$dealerText}{$categoryText} orders. They can be restored from the soft deleted orders page.");
	}

	// View Soft Deleted Orders
	public function softDeletedOrders(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return view('admin.unauthorized');
		}

		$query = OrdersSoftDeleted::with(['user']);

		// Search functionality
		if ($request->has('q') && !empty($request->q)) {
			$searchTerm = $request->q;
			$query->where(function($q) use ($searchTerm) {
				$q->where('username', 'like', '%' . $searchTerm . '%')
				  ->orWhere('user_phone', 'like', '%' . $searchTerm . '%')
				  ->orWhere('game_name', 'like', '%' . $searchTerm . '%')
				  ->orWhere('bond_name', 'like', '%' . $searchTerm . '%')
				  ->orWhere('rttp', 'like', '%' . $searchTerm . '%');
			});
		}

		$data = $query->orderBy('deleted_at', 'DESC')->paginate(500);

		return view('admin.orders-soft-deleted', compact('data'));
	}

	// Restore Bulk Soft Deleted Orders
	public function restoreBulkOrders(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return back()->withErrorMessage('You do not have permission to restore orders.');
		}

		$request->validate([
			'ids' => 'required|array',
			'ids.*' => 'exists:orders_soft_deleted,id'
		]);

		$ids = $request->ids;
		$softDeletedOrders = OrdersSoftDeleted::whereIn('id', $ids)->get();
		$restoredCount = 0;

		foreach ($softDeletedOrders as $softDeletedOrder) {
			// Create a new entry in the original orders table
			Orders::create([
				'user_id' => $softDeletedOrder->user_id,
				'username' => $softDeletedOrder->username,
				'user_phone' => $softDeletedOrder->user_phone,
				'game_name' => $softDeletedOrder->game_name,
				'bond_name' => $softDeletedOrder->bond_name,
				'rttp' => $softDeletedOrder->rttp,
				'first' => $softDeletedOrder->first,
				'second' => $softDeletedOrder->second,
				'status' => $softDeletedOrder->status,
				'created_at' => $softDeletedOrder->original_created_at,
				'updated_at' => $softDeletedOrder->original_updated_at,
			]);

			// Delete the record from the soft deleted table
			$softDeletedOrder->delete();
			$restoredCount++;
		}

		return back()->withSuccessMessage("Successfully restored {$restoredCount} orders.");
	}

	// Restore Soft Deleted Order
	public function restoreOrder(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return back()->withErrorMessage('You do not have permission to restore orders.');
		}

		$softDeletedOrder = OrdersSoftDeleted::findOrFail($request->id);
		$restoredOrder = $softDeletedOrder->restore();

		return back()->withSuccessMessage("Order #{$restoredOrder->id} has been restored successfully.");
	}

	// Permanently Delete Soft Deleted Order
	public function permanentlyDeleteOrder(Request $request)
	{
		if (!auth()->user()->hasPermission('orders')) {
			return back()->withErrorMessage('You do not have permission to permanently delete orders.');
		}

		$softDeletedOrder = OrdersSoftDeleted::findOrFail($request->id);
		$originalId = $softDeletedOrder->original_id;
		$softDeletedOrder->delete();

		return back()->withSuccessMessage("Order #{$originalId} has been permanently deleted.");
	}

	// Withdrawals Management
	public function withdrawals(Request $request) // Updated to accept Request
	{
		$query = Withdrawals::with(['user']);

		// Normal view: Exclude approved dealers
		$query->whereHas('user', function ($q) {
			$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
		});

		// Use base query
		$allWithdrawals = (clone $query)->latest()->paginate(20);
		$pendingWithdrawals = (clone $query)->pending()->latest()->paginate(20);
		$approvedWithdrawals = (clone $query)->approved()->latest()->paginate(20);
		$rejectedWithdrawals = (clone $query)->rejected()->latest()->paginate(20);

		return view('admin.withdrawals', compact('allWithdrawals', 'pendingWithdrawals', 'approvedWithdrawals', 'rejectedWithdrawals'));
	}

	public function approveWithdrawal(Request $request)
	{
		$request->validate([
			'withdrawal_id' => 'required|exists:withdrawals,id',
			'transaction_id' => 'required|string|max:255',
			'admin_notes' => 'nullable|string|max:1000'
		]);

		$withdrawal = Withdrawals::findOrFail($request->withdrawal_id);

		if ($withdrawal->status !== 'pending') {
			return back()->withSuccessMessage('This withdrawal has already been processed.');
		}

		$withdrawal->status = 'approved';
		$withdrawal->transaction_id = $request->transaction_id;
		$withdrawal->admin_notes = $request->admin_notes;
		$withdrawal->save();

        // Get admin role name
        $adminRole = auth()->user()->role() ? auth()->user()->role()->name : 'Admin';

        // Log approval (balance already deducted during request creation)
        TransactionService::logWithdrawalApproval(
            $withdrawal->user_id,
            $withdrawal->amount,
            $withdrawal->id,
            'Withdrawal approved - Transaction ID: ' . $request->transaction_id . ($request->admin_notes ? ' - ' . $request->admin_notes : ''),
            $adminRole
        );		// Send notification to user
		$withdrawal->user->notify(new \App\Notifications\WithdrawalVerification([
			'type' => 'approved',
			'amount' => $withdrawal->amount,
			'withdrawal' => $withdrawal
		]));

		return back()->withSuccessMessage('Withdrawal approved successfully. Amount has been deducted from user balance.');
	}

	public function rejectWithdrawal(Request $request)
	{
		$request->validate([
			'withdrawal_id' => 'required|exists:withdrawals,id',
			'admin_notes' => 'required|string|max:1000'
		]);

		$withdrawal = Withdrawals::findOrFail($request->withdrawal_id);

		if ($withdrawal->status !== 'pending') {
			return back()->withErrorMessage('This withdrawal has already been processed.');
		}

		$withdrawal->status = 'rejected';
		$withdrawal->admin_notes = $request->admin_notes;
		$withdrawal->save();

        // Get admin role name
        $adminRole = auth()->user()->role() ? auth()->user()->role()->name : 'Admin';

        // Refund amount back to user and log rejection
        TransactionService::logWithdrawalRejection(
            $withdrawal->user_id,
            $withdrawal->amount,
            $withdrawal->id,
            $request->admin_notes,
            $adminRole
        );		// Send notification to user
		$withdrawal->user->notify(new \App\Notifications\WithdrawalVerification([
			'type' => 'rejected',
			'amount' => $withdrawal->amount,
			'withdrawal' => $withdrawal
		]));

		return back()->withSuccessMessage('Withdrawal rejected successfully.');
	}

        public function restoreAllOrders(Request $request)
        {
                if (!auth()->user()->hasPermission('orders')) {
                        return back()->withErrorMessage('You do not have permission to restore orders.');
                }

                $request->validate([
                        'confirm' => 'required|in:RESTORE_ALL_ORDERS'
                ], [
                        'confirm.required' => 'Please confirm the restoration by typing RESTORE_ALL_ORDERS',
                        'confirm.in' => 'Confirmation text must be exactly: RESTORE_ALL_ORDERS'
                ]);

                // Get all soft deleted orders
                $softDeletedOrders = OrdersSoftDeleted::all();
                $restoredCount = 0;

                foreach ($softDeletedOrders as $softDeletedOrder) {
                        // Create a new entry in the original orders table
                        Orders::create([
                                'user_id' => $softDeletedOrder->user_id,
                                'username' => $softDeletedOrder->username,
                                'user_phone' => $softDeletedOrder->user_phone,
                                'game_name' => $softDeletedOrder->game_name,
                                'bond_name' => $softDeletedOrder->bond_name,
                                'rttp' => $softDeletedOrder->rttp,
                                'first' => $softDeletedOrder->first,
                                'second' => $softDeletedOrder->second,
                                'status' => $softDeletedOrder->status,
                                'created_at' => $softDeletedOrder->original_created_at,
                                'updated_at' => $softDeletedOrder->original_updated_at,
                        ]);

                        // Delete the record from the soft deleted table
                        $softDeletedOrder->delete();
                        $restoredCount++;
                }

                return back()->withSuccessMessage("Successfully restored {$restoredCount} orders back to the main orders table.");
        }

        // Permanently Delete All Soft Deleted Orders
        public function deleteAllSoftDeletedOrders(Request $request)
        {
                if (!auth()->user()->hasPermission('orders')) {
                        return back()->withErrorMessage('You do not have permission to delete orders.');
                }

                $request->validate([
                        'confirm' => 'required|in:DELETE_ALL_ORDERS'
                ], [
                        'confirm.required' => 'Please confirm the deletion by typing DELETE_ALL_ORDERS',
                        'confirm.in' => 'Confirmation text must be exactly: DELETE_ALL_ORDERS'
                ]);

                // Get count before deletion
                $deletedCount = OrdersSoftDeleted::count();

                // Permanently delete all soft deleted orders
                OrdersSoftDeleted::truncate();

                return back()->withSuccessMessage("Successfully permanently deleted {$deletedCount} soft deleted orders.");
        }

        private function handleWithdrawalProofUpload($file)
        {
                try {
                        $temp = 'public/temp/';
                        $path = 'public/withdrawals/';

                        if (!\File::exists($temp)) {
                                \File::makeDirectory($temp, 0755, true);
                        }
                        if (!\File::exists($path)) {
                                \File::makeDirectory($path, 0755, true);
                        }

                        $extension = $file->getClientOriginalExtension();
                        $fileName = 'withdrawal-proof-' . time() . '-' . uniqid() . '.' . $extension;

                        if ($file->move($temp, $fileName)) {
                                if (\File::copy($temp . $fileName, $path . $fileName)) {
                                        \File::delete($temp . $fileName);
                                        return $fileName;
                                } else {
                                        \File::delete($temp . $fileName);
                                        throw new \Exception('Failed to save payment proof');
                                }
                        } else {
                                throw new \Exception('Failed to upload payment proof');
                        }
                } catch (\Exception $e) {
                        \Log::error('Withdrawal proof upload error: ' . $e->getMessage());
                        throw $e;
                }
        }

        // ===== WITHDRAWAL METHODS MANAGEMENT =====

        // Show withdrawal methods page
        public function withdrawalMethods()
        {
                $withdrawalMethods = WithdrawalMethod::ordered()->get();
                return view('admin.withdrawal-methods', compact('withdrawalMethods'));
        }

        // Store new withdrawal method
        public function storeWithdrawalMethod(Request $request)
        {
                $request->validate([
                        'name' => 'required|string|max:255',
                        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                        'min_amount' => 'nullable|numeric|min:0',
                        'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
                        'is_active' => 'required|in:0,1'
                ]);

                $withdrawalMethod = new WithdrawalMethod();
                $withdrawalMethod->name = $request->name;
                $withdrawalMethod->min_amount = $request->min_amount;
                $withdrawalMethod->max_amount = $request->max_amount;
                $withdrawalMethod->is_active = (bool) $request->is_active;

                // Handle image upload
                if ($request->hasFile('image')) {
                        $withdrawalMethod->image = $this->handleImageUpload($request->file('image'));
                }

                $withdrawalMethod->save();

                return back()->withSuccessMessage('Withdrawal method added successfully.');
        }

        // Get withdrawal method for editing
        public function getWithdrawalMethod($id)
        {
                $withdrawalMethod = WithdrawalMethod::findOrFail($id);
                return response()->json($withdrawalMethod);
        }

        // Update withdrawal method
        public function updateWithdrawalMethod(Request $request)
        {
                $request->validate([
                        'method_id' => 'required|exists:withdrawal_methods,id',
                        'name' => 'required|string|max:255',
                        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                        'min_amount' => 'nullable|numeric|min:0',
                        'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
                        'is_active' => 'required|in:0,1'
                ]);

                $withdrawalMethod = WithdrawalMethod::findOrFail($request->method_id);
                $withdrawalMethod->name = $request->name;
                $withdrawalMethod->min_amount = $request->min_amount;
                $withdrawalMethod->max_amount = $request->max_amount;
                $withdrawalMethod->is_active = (bool) $request->is_active;

                // Handle image upload
                if ($request->hasFile('image')) {
                        // Delete old image if exists
                        if ($withdrawalMethod->image && \File::exists(public_path('img/' . $withdrawalMethod->image))) {
                                \File::delete(public_path('img/' . $withdrawalMethod->image));
                        }
                        $withdrawalMethod->image = $this->handleImageUpload($request->file('image'));
                }

                $withdrawalMethod->save();

                return back()->withSuccessMessage('Withdrawal method updated successfully.');
        }

        // Delete withdrawal method
        public function deleteWithdrawalMethod($id)
        {
                $withdrawalMethod = WithdrawalMethod::findOrFail($id);

                // Delete image file if exists
                if ($withdrawalMethod->image && \File::exists(public_path('img/' . $withdrawalMethod->image))) {
                        \File::delete(public_path('img/' . $withdrawalMethod->image));
                }

                $withdrawalMethod->delete();

                return back()->withSuccessMessage('Withdrawal method deleted successfully.');
        }

        // ===== PAID SERVICES MANAGEMENT =====

        // Show paid services page
        public function paidServices()
        {
                $paidServices = PaidService::ordered()->get();
                return view('admin.paid-services', compact('paidServices'));
        }

        // Show paid service sales page
        public function paidServiceSales()
        {
                $sales = PaidServiceSale::with(['user', 'service'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

                return view('admin.paid-service-sales', compact('sales'));
        }

        // Change sale status
        public function changeSaleStatus(Request $request)
        {
                $request->validate([
                        'sale_id' => 'required|exists:paid_service_sales,id',
                        'status' => 'required|in:active,inactive,refunded'
                ]);

                $sale = PaidServiceSale::findOrFail($request->sale_id);
                $sale->status = $request->status;
                $sale->save();

                return response()->json([
                        'success' => true,
                        'message' => 'Sale status updated successfully.'
                ]);
        }

        // Refund sale
        public function refundSale(Request $request)
        {
                $request->validate([
                        'sale_id' => 'required|exists:paid_service_sales,id'
                ]);

                $sale = PaidServiceSale::findOrFail($request->sale_id);

                // Check if already refunded
                if ($sale->status === 'refunded') {
                        return response()->json([
                                'success' => false,
                                'message' => 'This sale has already been refunded.'
                        ], 400);
                }

                // Log refund transaction and update user balance
                TransactionService::logRefund(
                    $sale->user_id,
                    $sale->amount,
                    $sale->id,
                    'PaidServiceSale',
                    'Refund for paid service: ' . ($sale->service->title ?? 'Unknown Service')
                );

                // Update sale status
                $sale->status = 'refunded';
                $sale->save();

                return response()->json([
                        'success' => true,
                        'message' => 'Sale refunded successfully. Amount added to user wallet.'
                ]);
        }

        // Delete sale
        public function deleteSale(Request $request)
        {
                $request->validate([
                        'sale_id' => 'required|exists:paid_service_sales,id'
                ]);

                $sale = PaidServiceSale::findOrFail($request->sale_id);
                $sale->delete();

                return response()->json([
                        'success' => true,
                        'message' => 'Sale deleted successfully.'
                ]);
        }

        // Delete all sales
        public function deleteAllSales(Request $request)
        {
                try {
                        $count = PaidServiceSale::count();

                        if ($count === 0) {
                                return response()->json([
                                        'success' => false,
                                        'message' => 'No sales found to delete.'
                                ], 400);
                        }

                        PaidServiceSale::truncate();

                        return response()->json([
                                'success' => true,
                                'message' => "Successfully deleted {$count} sale(s)."
                        ]);
                } catch (\Exception $e) {
                        return response()->json([
                                'success' => false,
                                'message' => 'An error occurred while deleting sales.'
                        ], 500);
                }
        }

        // Store new paid service
        public function storePaidService(Request $request)
        {
                $request->validate([
                        'title' => 'required|string|max:255',
                        'price' => 'required|numeric|min:0',
                        'description' => 'nullable|string',
                        'golden_text' => 'nullable|string',
                        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                        'is_active' => 'required|in:0,1'
                ]);

                $paidService = new PaidService();
                $paidService->title = $request->title;
                $paidService->price = $request->price;
                $paidService->description = $request->description;
                $paidService->golden_text = $request->golden_text;
                $paidService->is_active = (bool) $request->is_active;

                // Handle image upload
                if ($request->hasFile('image')) {
                        $paidService->image = $this->handleImageUpload($request->file('image'));
                }

                $paidService->save();

                return back()->withSuccessMessage('Paid service added successfully.');
        }

        // Get paid service for editing
        public function getPaidService($id)
        {
                $paidService = PaidService::findOrFail($id);
                return response()->json($paidService);
        }

        // Update paid service
        public function updatePaidService(Request $request)
        {
                $request->validate([
                        'service_id' => 'required|exists:paid_services,id',
                        'title' => 'required|string|max:255',
                        'price' => 'required|numeric|min:0',
                        'description' => 'nullable|string',
                        'golden_text' => 'nullable|string',
                        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                        'is_active' => 'required|in:0,1'
                ]);

                $paidService = PaidService::findOrFail($request->service_id);
                $paidService->title = $request->title;
                $paidService->price = $request->price;
                $paidService->description = $request->description;
                $paidService->golden_text = $request->golden_text;
                $paidService->is_active = (bool) $request->is_active;

                // Handle image upload
                if ($request->hasFile('image')) {
                        // Delete old image if exists
                        if ($paidService->image && \File::exists('public/img/' . $paidService->image)) {
                                \File::delete('public/img/' . $paidService->image);
                        }
                        $paidService->image = $this->handleImageUpload($request->file('image'));
                }

                $paidService->save();

                return back()->withSuccessMessage('Paid service updated successfully.');
        }

        // Delete paid service
        public function deletePaidService($id)
        {
                $paidService = PaidService::findOrFail($id);

                // Delete image file
                if ($paidService->image && \File::exists('public/img/' . $paidService->image)) {
                        \File::delete('public/img/' . $paidService->image);
                }

                $paidService->delete();

                return back()->withSuccessMessage('Paid service deleted successfully.');
        }

    // ==================== APK VERSION MANAGEMENT ====================

    /**
     * Show APK versions management page
     */
    public function apkVersions()
    {
        $apkVersions = ApkVersion::orderBy('version_code', 'desc')->get();
        $uploadedApkFiles = $this->getAvailableApkFiles();

        return view('admin.apk-versions', compact('apkVersions', 'uploadedApkFiles'));
    }

    /**
     * Upload APK file asynchronously for later selection
     */
    public function uploadApkFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apk_file' => 'required|file|max:102400',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->hasFile('apk_file')) {
                return;
            }

            $file = $request->file('apk_file');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension !== 'apk') {
                $validator->errors()->add('apk_file', 'The apk file must be a file of type: apk.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('apk_file');
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^A-Za-z0-9\-_]/', '-', $original);
            $safeBase = trim($safeBase, '-');
            $safeBase = $safeBase ?: 'app-release';
            $fileName = $safeBase . '-' . time() . '.apk';
            $storedPath = $file->storeAs('apks', $fileName, 'public');

            return response()->json([
                'success' => true,
                'message' => 'APK uploaded successfully.',
                'data' => [
                    'file_path' => $storedPath,
                    'file_name' => basename($storedPath),
                    'download_link' => asset('storage/' . $storedPath),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('APK async upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'APK upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload APK in chunks to handle stricter proxy/PHP body limits.
     */
    public function uploadApkChunk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload_id' => 'required|string|max:100',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1|max:1000',
            'file_name' => 'required|string|max:255',
            'apk_chunk' => 'required|file|max:5120', // 5MB per chunk
        ]);

        $validator->after(function ($validator) use ($request) {
            $fileName = strtolower($request->input('file_name', ''));
            if (substr($fileName, -4) !== '.apk') {
                $validator->errors()->add('file_name', 'The apk file must be a file of type: apk.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadId = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $request->upload_id);
            $chunkIndex = (int) $request->chunk_index;
            $totalChunks = (int) $request->total_chunks;
            $fileName = $request->file_name;

            $chunkDir = storage_path('app/apk-chunks/' . $uploadId);
            if (!is_dir($chunkDir)) {
                mkdir($chunkDir, 0755, true);
            }

            $request->file('apk_chunk')->move($chunkDir, 'chunk_' . $chunkIndex . '.part');

            $allReceived = true;
            for ($i = 0; $i < $totalChunks; $i++) {
                if (!file_exists($chunkDir . DIRECTORY_SEPARATOR . 'chunk_' . $i . '.part')) {
                    $allReceived = false;
                    break;
                }
            }

            if (!$allReceived) {
                return response()->json([
                    'success' => true,
                    'completed' => false,
                    'message' => 'Chunk uploaded'
                ]);
            }

            $original = pathinfo($fileName, PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^A-Za-z0-9\-_]/', '-', $original);
            $safeBase = trim($safeBase, '-');
            $safeBase = $safeBase ?: 'app-release';
            $finalName = $safeBase . '-' . time() . '.apk';
            $finalRelativePath = 'apks/' . $finalName;
            $finalAbsolutePath = Storage::disk('public')->path($finalRelativePath);

            $out = fopen($finalAbsolutePath, 'wb');
            if ($out === false) {
                throw new \RuntimeException('Unable to create output file.');
            }

            for ($i = 0; $i < $totalChunks; $i++) {
                $partPath = $chunkDir . DIRECTORY_SEPARATOR . 'chunk_' . $i . '.part';
                $in = fopen($partPath, 'rb');
                if ($in === false) {
                    fclose($out);
                    throw new \RuntimeException('Unable to read chunk ' . $i . '.');
                }
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
            fclose($out);

            for ($i = 0; $i < $totalChunks; $i++) {
                @unlink($chunkDir . DIRECTORY_SEPARATOR . 'chunk_' . $i . '.part');
            }
            @rmdir($chunkDir);

            return response()->json([
                'success' => true,
                'completed' => true,
                'message' => 'APK uploaded successfully.',
                'data' => [
                    'file_path' => $finalRelativePath,
                    'file_name' => $finalName,
                    'download_link' => asset('storage/' . $finalRelativePath),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('APK chunk upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Chunk upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Update APK download link
     */
    public function updateApkLink(Request $request)
    {
        \Log::info('APK Link Update Request', [
            'version_name' => $request->version_name,
            'version_code' => $request->version_code,
            'download_link' => $request->download_link,
            'download_source' => $request->download_source,
            'has_apk_file' => $request->hasFile('apk_file'),
            'is_active' => $request->has('is_active'),
            'is_force_update' => $request->has('is_force_update')
        ]);

        $validator = Validator::make($request->all(), [
            'version_name' => 'required|string|max:20',
            'version_code' => 'required|integer',
            'download_source' => 'required|in:link,file',
            'download_link' => 'nullable|url',
            'selected_apk_file' => 'nullable|string|max:255',
            'apk_file' => 'nullable|file|max:102400',
            'release_notes' => 'nullable|string|max:1000',
            'is_force_update' => 'boolean'
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->download_source === 'link' && empty($request->download_link)) {
                $validator->errors()->add('download_link', 'Download link is required when source is External Link.');
            }

            if ($request->hasFile('apk_file')) {
                $file = $request->file('apk_file');
                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension !== 'apk') {
                    $validator->errors()->add('apk_file', 'The apk file must be a file of type: apk.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        \Log::info('APK Link Update Validation Passed');

        try {
            // If this is set as active, deactivate all other versions
            if ($request->has('is_active')) {
                ApkVersion::where('is_active', true)->update(['is_active' => false]);
            }

            // Prefer explicit edit target, otherwise fall back to version code
            $existingVersion = null;
            if (!empty($request->version_id)) {
                $existingVersion = ApkVersion::find($request->version_id);
            }
            if (!$existingVersion) {
                $existingVersion = ApkVersion::where('version_code', $request->version_code)->first();
            }

            $downloadLink = $request->download_link;
            $apkFilePath = $existingVersion->apk_file_path ?? null;

            if ($request->download_source === 'file') {
                if (!empty($request->selected_apk_file)) {
                    $selectedPath = $request->selected_apk_file;
                    if (!Storage::disk('public')->exists($selectedPath)) {
                        return back()->withErrors([
                            'selected_apk_file' => 'Selected APK file was not found. Please upload again.'
                        ])->withInput();
                    }

                    if ($existingVersion && !empty($existingVersion->apk_file_path) && $existingVersion->apk_file_path !== $selectedPath) {
                        $this->deleteApkFileIfUnused($existingVersion->apk_file_path, $existingVersion->id);
                    }

                    $apkFilePath = $selectedPath;
                    $downloadLink = $existingVersion ? route('public.apk.file', ['id' => $existingVersion->id]) : null;
                } elseif ($request->hasFile('apk_file')) {
                    // Fallback if form submits file directly
                    $file = $request->file('apk_file');
                    $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeBase = preg_replace('/[^A-Za-z0-9\-_]/', '-', $original);
                    $safeBase = trim($safeBase, '-');
                    $safeBase = $safeBase ?: 'app-release';
                    $fileName = $safeBase . '-' . time() . '.apk';
                    $storedPath = $file->storeAs('apks', $fileName, 'public');

                    if ($existingVersion && !empty($existingVersion->apk_file_path) && $existingVersion->apk_file_path !== $storedPath) {
                        $this->deleteApkFileIfUnused($existingVersion->apk_file_path, $existingVersion->id);
                    }

                    $apkFilePath = $storedPath;
                    $downloadLink = $existingVersion ? route('public.apk.file', ['id' => $existingVersion->id]) : null;
                } elseif (!$existingVersion || empty($existingVersion->apk_file_path)) {
                    return back()->withErrors([
                        'selected_apk_file' => 'Please upload/select an APK file first.'
                    ])->withInput();
                } else {
                    // Keep existing uploaded file and generated link
                    $downloadLink = route('public.apk.file', ['id' => $existingVersion->id]);
                }
            } else {
                // Switching to external link: clean up old uploaded file if present
                if ($existingVersion && !empty($existingVersion->apk_file_path)) {
                    $this->deleteApkFileIfUnused($existingVersion->apk_file_path, $existingVersion->id);
                }
                $apkFilePath = null;
            }

            if ($existingVersion) {
                if ($request->download_source === 'file') {
                    $downloadLink = route('public.apk.file', ['id' => $existingVersion->id]);
                }

                // Update existing version
                $existingVersion->update([
                    'version_name' => $request->version_name,
                    'version_code' => $request->version_code,
                    'download_link' => $downloadLink,
                    'apk_file_path' => $apkFilePath,
                    'release_notes' => $request->release_notes,
                    'is_active' => $request->has('is_active'),
                    'is_force_update' => $request->has('is_force_update')
                ]);
                $message = 'APK version updated successfully!';
            } else {
                // Create new version
                $newVersion = ApkVersion::create([
                    'version_name' => $request->version_name,
                    'version_code' => $request->version_code,
                    'download_link' => $downloadLink ?? '',
                    'apk_file_path' => $apkFilePath,
                    'release_notes' => $request->release_notes,
                    'is_active' => $request->has('is_active'),
                    'is_force_update' => $request->has('is_force_update'),
                    'download_count' => 0
                ]);

                if ($request->download_source === 'file') {
                    $newVersion->update([
                        'download_link' => route('public.apk.file', ['id' => $newVersion->id])
                    ]);
                }
                $message = 'APK version added successfully!';
            }

            \Log::info('APK Link Update Success', ['message' => $message]);
            return redirect()->route('apk.versions')->withSuccessMessage($message);

        } catch (\Exception $e) {
            \Log::error('APK Link Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrorMessage('Failed to update APK link. Please try again.');
        }
    }

    /**
     * Show public download page
     */
    public function showDownloadPage()
    {
        $activeVersion = ApkVersion::getActiveVersion();
        $latestVersion = ApkVersion::getLatestVersion();

        return view('download-apk', compact('activeVersion', 'latestVersion'));
    }

    /**
     * Public APK file download endpoint (works without storage symlink)
     */
    public function downloadApkFile($id)
    {
        $apkVersion = ApkVersion::findOrFail($id);

        if (empty($apkVersion->apk_file_path)) {
            if (!empty($apkVersion->download_link)) {
                $apkVersion->incrementDownloadCount();
                return redirect()->away($apkVersion->download_link);
            }

            abort(404, 'Download link not found.');
        }

        if (!Storage::disk('public')->exists($apkVersion->apk_file_path)) {
            abort(404, 'APK file not found.');
        }

        $apkVersion->incrementDownloadCount();

        $absolutePath = Storage::disk('public')->path($apkVersion->apk_file_path);
        $downloadName = 'app-v' . $apkVersion->version_name . '.apk';

        return response()->download($absolutePath, $downloadName, [
            'Content-Type' => 'application/vnd.android.package-archive'
        ]);
    }

    /**
     * Toggle APK version active status
     */
    public function toggleApkActive($id)
    {
        $apkVersion = ApkVersion::findOrFail($id);

        if ($apkVersion->is_active) {
            $apkVersion->update(['is_active' => false]);
            $message = 'APK version deactivated successfully';
        } else {
            // Deactivate all other versions first
            ApkVersion::where('is_active', true)->update(['is_active' => false]);
            $apkVersion->update(['is_active' => true]);
            $message = 'APK version activated successfully';
        }

        return back()->withSuccessMessage($message);
    }

    /**
     * Delete APK version
     */
    public function deleteApkVersion($id)
    {
        $apkVersion = ApkVersion::findOrFail($id);

        if (!empty($apkVersion->apk_file_path)) {
            $this->deleteApkFileIfUnused($apkVersion->apk_file_path, $apkVersion->id);
        }

        $apkVersion->delete();

        return back()->withSuccessMessage('APK version deleted successfully!');
    }

    /**
     * List all uploaded APK files for admin dropdown
     */
    private function getAvailableApkFiles(): array
    {
        $files = Storage::disk('public')->files('apks');
        $apkFiles = [];

        foreach ($files as $filePath) {
            if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'apk') {
                continue;
            }

            $apkFiles[] = [
                'path' => $filePath,
                'name' => basename($filePath),
                'url' => asset('storage/' . $filePath),
                'modified_at' => Storage::disk('public')->lastModified($filePath),
            ];
        }

        usort($apkFiles, function ($a, $b) {
            return $b['modified_at'] <=> $a['modified_at'];
        });

        return $apkFiles;
    }

    /**
     * Delete APK file only if no other APK version references it
     */
    private function deleteApkFileIfUnused(string $path, ?int $excludingId = null): void
    {
        $query = ApkVersion::where('apk_file_path', $path);
        if (!is_null($excludingId)) {
            $query->where('id', '!=', $excludingId);
        }

        if (!$query->exists() && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get APK version info for API
     */
    public function getApkVersionInfo()
    {
        $activeVersion = ApkVersion::getActiveVersion();
        $latestVersion = ApkVersion::getLatestVersion();

        return response()->json([
            'success' => true,
            'data' => [
                'active_version' => $activeVersion,
                'latest_version' => $latestVersion,
                'download_url' => $activeVersion ? $activeVersion->download_url : null
            ]
        ]);
    }

    /**
     * Update APK platform URL
     */
    public function updateApkPlatformUrl(Request $request)
    {
        $request->validate([
            'apk_platform_url' => 'required|url'
        ]);

        try {
            $this->settings->update([
                'apk_platform_url' => $request->apk_platform_url
            ]);

            return response()->json([
                'success' => true,
                'message' => 'APK Platform URL updated successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('APK Platform URL Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update APK Platform URL. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete all transactions
     */
    public function deleteAllTransactions(Request $request)
    {
        try {
            // Validate confirmation text
            $request->validate([
                'confirm' => 'required|in:DELETE ALL TRANSACTIONS'
            ], [
                'confirm.in' => 'You must type "DELETE ALL TRANSACTIONS" exactly to confirm.'
            ]);

            // Get count before deletion for logging
            $transactionCount = \App\Models\Transaction::count();

            if ($transactionCount === 0) {
                return redirect()->route('admin.transactions')->withInfoMessage('No transactions found to delete.');
            }

            // Delete all transactions
            \App\Models\Transaction::truncate();

            // Log the action
            \Log::info('All transactions deleted by admin', [
                'admin_id' => auth()->id(),
                'deleted_count' => $transactionCount,
                'timestamp' => now()
            ]);

            return redirect()->route('admin.transactions')->withSuccessMessage("Successfully deleted all {$transactionCount} transactions.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.transactions')->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Delete All Transactions Error: ' . $e->getMessage(), [
                'admin_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.transactions')->withErrorMessage('Failed to delete transactions. Please try again.');
        }
    }

    /**
     * Delete all deposits
     */
    public function deleteAllDeposits(Request $request)
    {
        try {
            // Validate confirmation text
            $request->validate([
                'confirm' => 'required|in:DELETE ALL DEPOSITS'
            ], [
                'confirm.in' => 'You must type "DELETE ALL DEPOSITS" exactly to confirm.'
            ]);

            // Build query
            $query = \App\Models\Deposits::query();

            if ($request->has('filter_dealers') && $request->filter_dealers) {
                // Delete ONLY approved dealers
                $query->whereHas('user', function ($q) {
                    $q->where('dealer_status', 'approved');
                });
            } else {
                // Delete ONLY non-dealers
                $query->whereHas('user', function ($q) {
                    $q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
                });
            }

            // Get count before deletion for logging
            $depositCount = $query->count();

            if ($depositCount === 0) {
                return redirect()->back()->withInfoMessage('No deposits found to delete.');
            }

            // Delete deposits
            $query->delete();

            // Log the action
            \Log::info('All deposits deleted by admin', [
                'admin_id' => auth()->id(),
                'deleted_count' => $depositCount,
                'dealer_filter' => $request->filter_dealers ? 'yes' : 'no',
                'timestamp' => now()
            ]);

            return redirect()->back()->withSuccessMessage("Successfully deleted all {$depositCount} deposits.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Delete All Deposits Error: ' . $e->getMessage(), [
                'admin_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrorMessage('Failed to delete deposits. Please try again.');
        }
    }

    /**
     * Delete all withdrawals
     */
    public function deleteAllWithdrawals(Request $request)
    {
        try {
            // Validate confirmation text
            $request->validate([
                'confirm' => 'required|in:DELETE ALL WITHDRAWALS'
            ], [
                'confirm.in' => 'You must type "DELETE ALL WITHDRAWALS" exactly to confirm.'
            ]);

            // Build query
            $query = \App\Models\Withdrawals::query();

            if ($request->has('filter_dealers') && $request->filter_dealers) {
                // Delete ONLY approved dealers
                $query->whereHas('user', function ($q) {
                    $q->where('dealer_status', 'approved');
                });
            } else {
                // Delete ONLY non-dealers
                $query->whereHas('user', function ($q) {
                    $q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
                });
            }

            // Get count before deletion for logging
            $withdrawalCount = $query->count();

            if ($withdrawalCount === 0) {
                return redirect()->back()->withSuccessMessage('No withdrawals found to delete.');
            }

            // Delete withdrawals
            $query->delete();

            // Log the action
            \Log::info('All withdrawals deleted by admin', [
                'admin_id' => auth()->id(),
                'deleted_count' => $withdrawalCount,
                'dealer_filter' => $request->filter_dealers ? 'yes' : 'no',
                'timestamp' => now()
            ]);

            return redirect()->back()->withSuccessMessage("Successfully deleted all {$withdrawalCount} withdrawals.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Delete All Withdrawals Error: ' . $e->getMessage(), [
                'admin_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withSuccessMessage('Failed to delete withdrawals. Please try again.');
        }
    }
    // ==================== HELP VIDEOS MANAGEMENT ====================

    /**
     * Show help videos management page
     */
    public function helpVideos()
    {
        $videos = HelpVideo::orderBy('created_at', 'desc')->get();
        return view('admin.help-videos', compact('videos'));
    }

    /**
     * Store or update help video
     */
    public function storeHelpVideo(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_type' => 'required|in:youtube,local',
            'youtube_url' => 'required_if:video_type,youtube|nullable|url',
            'local_video' => 'required_if:video_type,local|nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:102400',
            'video_id' => 'nullable|integer'
        ]);

        try {
            $videoPath = null;

            // Handle local video upload
            if ($request->video_type === 'local' && $request->hasFile('local_video')) {
                $file = $request->file('local_video');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

                // Create directory if it doesn't exist
                $uploadPath = public_path('help-videos');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                // Move file
                $file->move($uploadPath, $fileName);
                $videoPath = $fileName;
            }

            // Check if we're editing an existing video
            if ($request->filled('video_id')) {
                $video = HelpVideo::findOrFail($request->video_id);

                // Delete old video file if switching to YouTube or uploading new file
                if ($video->video_type === 'local' && $video->local_video_path) {
                    $oldPath = public_path('help-videos/' . $video->local_video_path);
                    if (file_exists($oldPath) && ($request->video_type === 'youtube' || $videoPath)) {
                        unlink($oldPath);
                    }
                }

                $video->update([
                    'title' => $request->title,
                    'video_type' => $request->video_type,
                    'youtube_url' => $request->video_type === 'youtube' ? $request->youtube_url : null,
                    'local_video_path' => $request->video_type === 'local' ? ($videoPath ?: $video->local_video_path) : null,
                    'is_active' => $request->has('is_active')
                ]);

                $message = 'Help video updated successfully!';
            } else {
                // Create new video
                HelpVideo::create([
                    'title' => $request->title,
                    'video_type' => $request->video_type,
                    'youtube_url' => $request->video_type === 'youtube' ? $request->youtube_url : null,
                    'local_video_path' => $videoPath,
                    'is_active' => $request->has('is_active')
                ]);

                $message = 'Help video added successfully!';
            }

            return redirect()->route('admin.help.videos')->withSuccessMessage($message);

        } catch (\Exception $e) {
            Log::error('Help Video Error: ' . $e->getMessage());
            return back()->withErrorMessage('Failed to save help video. Please try again.');
        }
    }

    /**
     * Toggle help video active status
     */
    public function toggleHelpVideoActive($id)
    {
        $video = HelpVideo::findOrFail($id);
        $video->update(['is_active' => !$video->is_active]);

        $message = $video->is_active ? 'Help video activated successfully!' : 'Help video deactivated successfully!';
        return back()->withSuccessMessage($message);
    }

    /**
     * Delete help video
     */
    public function deleteHelpVideo($id)
    {
        $video = HelpVideo::findOrFail($id);

        // Delete video file if it's local
        if ($video->video_type === 'local' && $video->local_video_path) {
            $filePath = public_path('help-videos/' . $video->local_video_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $video->delete();

        return back()->withSuccessMessage('Help video deleted successfully!');
    }

    /**
     * Get help videos for API
     */
    public function getHelpVideosApi()
    {
        $videos = HelpVideo::getActiveVideos();

        $response = $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video_type' => $video->video_type,
                'video_url' => $video->video_type === 'youtube'
                    ? $video->youtube_url
                    : url('public/help-videos/' . $video->local_video_path),
                'embed_url' => $video->video_type === 'youtube'
                    ? $video->getYoutubeEmbedUrl()
                    : null,
                'view_count' => $video->view_count,
                'created_at' => $video->created_at->toDateTimeString()
            ];
        });

        return response()->json([
            'success' => true,
            'videos' => $response
        ]);
    }

    /**
     * Increment help video view count
     */
    public function incrementHelpVideoView($id)
    {
        try {
            $video = HelpVideo::findOrFail($id);
            $video->incrementViewCount();

            return response()->json([
                'success' => true,
                'message' => 'View count incremented successfully',
                'view_count' => $video->view_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }
    }

    /**
     * System Logs View
     */
    public function systemLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $fileLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($fileLines !== false) {
                $logs = array_slice($fileLines, -50);
                $logs = array_reverse($logs);
            }
        }

        return view('admin.system-logs', compact('logs'));
    }

    /**
     * Delete System Logs
     */
    public function deleteSystemLogs()
    {
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            return redirect()->back()->withSuccessMessage('System logs cleared successfully!');
        }

        return redirect()->back()->withErrorMessage('Log file not found.');
    }
}

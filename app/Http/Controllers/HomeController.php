<?php

namespace App\Http\Controllers;

use DB;
use Lang;
use Mail;
use App\Models\User;
use App\Models\Plans;
use App\Models\Query;
use App\Models\Categories;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{
  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    try {
      // Check Datebase access
      AdminSettings::select('id')->first();
    } catch (\Exception $e) {
      // Redirect to Installer
      return redirect('installer/script');
    }

    $settings = AdminSettings::first();
    $categories = Categories::select(['name', 'slug', 'thumbnail'])->where('mode', 'on')->orderBy('name')->simplePaginate(4);

    // Simplified for universal starter kit - just get top categories without image count
    $popularCategories = Categories::where('mode', 'on')->take(5)->get();

    if ($popularCategories->count() != 0) {
      foreach ($popularCategories as $popularCategorie) {
        $categoryName = Lang::has('categories.' . $popularCategorie->slug) ? __('categories.' . $popularCategorie->slug) : $popularCategorie->name;

        $popularCategorieArray[]  = '<a style="color:#FFF;" href="' . url('category', $popularCategorie->slug) . '">' . $categoryName . '</a>';
      }
      $categoryPopular = implode(', ', $popularCategorieArray);
    } else {
      $categoryPopular = false;
    }

    // Get basic stats for display
    $userCount = User::count();
    $categoriesCount = Categories::where('mode', 'on')->count();

    return view(
      'index.home',
      [
        'settings' => $settings,
        'categories' => $categories,
        'categoryPopular' => $categoryPopular,
        'userCount' => $userCount,
        'categoriesCount' => $categoriesCount
      ]
    );
  }

  public function sevenhome()
  {
    try {
      // Check Database access
      AdminSettings::select('id')->first();
    } catch (\Exception $e) {
      // Redirect to Installer
      return redirect('installer/script');
    }

    // Get settings for the TTS interface
    $settings = AdminSettings::first();

    // Get basic stats for display
    $userCount = User::count();
    $downloadsCount = 0; // Placeholder since downloads table was removed
    $imagesCount = 0; // Placeholder since images table was removed
    $categoriesCount = Categories::where('mode', 'on')->count();

    // Get categories for display
    $categories = Categories::select(['name', 'slug', 'thumbnail'])->where('mode', 'on')->orderBy('name')->simplePaginate(4);

    // Simplified for universal starter kit - just get top categories without image count
    $popularCategories = Categories::where('mode', 'on')->take(5)->get();

    if ($popularCategories->count() != 0) {
      foreach ($popularCategories as $popularCategorie) {
        $categoryName = Lang::has('categories.' . $popularCategorie->slug) ? __('categories.' . $popularCategorie->slug) : $popularCategorie->name;

        $popularCategorieArray[]  = '<a style="color:#FFF;" href="' . url('category', $popularCategorie->slug) . '">' . $categoryName . '</a>';
      }
      $categoryPopular = implode(', ', $popularCategorieArray);
    } else {
      $categoryPopular = false;
    }

    return view(
      'index.sevenhome',
      [
        'settings' => $settings,
        'categories' => $categories,
        'categoryPopular' => $categoryPopular,
        'userCount' => $userCount,
        'downloadsCount' => $downloadsCount,
        'imagesCount' => $imagesCount,
        'categoriesCount' => $categoriesCount,
        'images' => collect([]), // Empty collection for compatibility
        'featured' => null, // No featured images in TTS interface
      ]
    );
  }

  public function getVerifyAccount($confirmation_code)
  {
    if (
      Auth::guest()
      || Auth::check()
      && Auth::user()->activation_code == $confirmation_code
      && Auth::user()->status == 'pending'
    ) {
      $user = User::where('activation_code', $confirmation_code)->where('status', 'pending')->first();

      if ($user) {

        $update = User::where('activation_code', $confirmation_code)
          ->where('status', 'pending')
          ->update(array('status' => 'active', 'activation_code' => ''));


        Auth::loginUsingId($user->id);

        return redirect('/')
          ->with([
            'success_verify' => true,
          ]);
      } else {
        return redirect('/')
          ->with([
            'error_verify' => true,
          ]);
      }
    } else {
      return redirect('/');
    }
  }

  public function getSearch()
  {
    $q = request()->get('q');
    $images = Query::searchImages();

    //<--- * If $q is empty or is minus to 1 * ---->
    if ($q == '' || strlen($q) <= 2) {
      return redirect('/latest');
    }

    if (request()->ajax()) {
      return view('includes.images')->with($images)->render();
    }

    return view('default.search')->with($images);
  }

  public function members()
  {
    $users = Query::users();

    if (request()->ajax()) {
      return view('includes.users')->withUsers($users)->render();
    }

    return view('default.members')->withUsers($users);
  }

  public function premium()
  {
    if (config('settings.sell_option') == 'off') {
      abort(404);
    }

    $images = Query::premiumImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.premium'),
      'description' => __('misc.premium_desc'),
    ]);
  }

  public function latest()
  {
    $images = Query::latestImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.latest'),
      'description' => __('misc.latest_desc'),
    ]);
  }

  public function featured()
  {
    $images = Query::featuredImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.featured'),
      'description' => __('misc.featured_desc'),
    ]);
  }


  public function popular()
  {
    $images = Query::popularImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.popular'),
      'description' => __('misc.popular_desc'),
    ]);
  }

  public function commented()
  {
    $images = Query::commentedImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.most_commented'),
      'description' => __('misc.most_commented_desc'),
    ]);
  }

  public function viewed()
  {
    $images = Query::viewedImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.most_viewed'),
      'description' => __('misc.most_viewed_desc'),
    ]);
  }

  public function downloads()
  {
    $images = Query::downloadsImages();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.most_downloads'),
      'description' => __('misc.most_downloads_desc'),
    ]);
  }

  public function categories()
  {
    $categories = Categories::whereMode('on')->orderBy('name')->get();
    return view('default.categories')->withCategories($categories);
  }

  public function category($slug)
  {
    $images = Query::categoryImages($slug);

    if (request()->ajax()) {
      return view('includes.images')->with($images)->render();
    }

    return view('default.category')->with($images);
  }

  public function subcategory($slug, $subcategory)
  {
    $images = Query::subCategoryImages($slug, $subcategory);

    if (request()->ajax()) {
      return view('includes.images')->with($images)->render();
    }

    return view('default.subcategory')->with($images);
  }

  public function cameras($slug)
  {
    if (strlen($slug) > 3) {
      $images = Query::camerasImages($slug);

      if (request()->ajax()) {
        return view('includes.images')->with($images)->render();
      }

      return view('default.cameras')->with($images);
    } else {
      abort('404');
    }
  }

  public function colors($slug)
  {
    if (strlen($slug) == 6) {
      $images = Query::colorsImages($slug);

      if (request()->ajax()) {
        return view('includes.images')->with($images)->render();
      }

      return view('default.colors')->with($images);
    } else {
      abort('404');
    }
  }

  public function collections(Request $request)
  {
    $title = __('misc.collections') . ' - ';

    // Removed for universal starter kit - return empty paginated collection
    $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
      'path' => request()->url(),
    ]);

    if ($request->input('page') > $data->lastPage()) {
      abort('404');
    }

    if (request()->ajax()) {
      return view('includes.collections-grid', ['data' => $data])->render();
    }

    return view('default.collections', ['title' => $title, 'data' => $data]);
  } //<--- End Method

  public function contact()
  {
    return view('default.contact');
  }

  public function contactStore(Request $request)
  {
    $input = $request->all();

    $errorMessages = [
      'g-recaptcha-response.required' => 'reCAPTCHA Error',
      'g-recaptcha-response.captcha' => 'reCAPTCHA Error',
    ];

    $validator = Validator::make($input, [
      'full_name' => 'min:3|max:25',
      'email'     => 'required|email',
      'subject'     => 'required',
      'message' => 'min:10|required',
      'g-recaptcha-response' => 'required|captcha'
    ], $errorMessages);

    if ($validator->fails()) {
      return redirect('contact')
        ->withInput()->withErrors($validator);
    }

    // SEND EMAIL TO SUPPORT
    $fullname    = $input['full_name'];
    $email_user  = $input['email'];
    $title_site  = config('settings.title');
    $subject     = $input['subject'];
    $email_reply = config('settings.email_admin');

    Mail::send(
      'emails.contact-email',
      array(
        'full_name' => $input['full_name'],
        'email' => $input['email'],
        'subject' => $input['subject'],
        '_message' => $input['message'],
        'ip' => request()->ip(),
      ),
      function ($message) use (
        $fullname,
        $email_user,
        $title_site,
        $email_reply,
        $subject
      ) {
        $message->from($email_reply, $fullname);
        $message->subject(__('misc.message') . ' - ' . $subject . ' - ' . $email_user);
        $message->to($email_reply, $title_site);
        $message->replyTo($email_user);
      }
    );

    return redirect('contact')->with(['notification' => __('misc.send_contact_success')]);
  }

  public function pricing()
  {
    $plans = Plans::whereStatus('1');

    if ($plans->count() == 0 || config('settings.sell_option') == 'off') {
      abort(404);
    }

    return view('default.pricing')->with([
      'plans' => $plans,
      'getSubscription' => auth()->check() ? auth()->user()->getSubscription() : null
    ]);
  }

  public function tags()
  {
    // Removed for universal starter kit - return empty data
    $data = collect([]);
    return view('default.tags')->withData($data);
  }

  public function tagsShow($slug)
  {
    $slug = str_replace('_', ' ', $slug);

    if (strlen($slug) > 1) {
      $images = Query::tagsImages($slug);

      if (request()->ajax()) {
        return view('includes.images')->with($images)->render();
      }

      return view('default.tags-show')->with($images);
    } else {
      abort('404');
    }
  }

  public function vectors()
  {
    $images = Query::vectors();

    if (request()->ajax()) {
      return view('includes.images', ['images' => $images])->render();
    }

    return view('index.explore', [
      'images' => $images,
      'title' => __('misc.vectors'),
      'description' => __('misc.vectors_desc'),
    ]);
  }
}

@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('admin.general_settings') }}</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

      @if (session('success_message'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

              @include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

      <form method="POST" action="{{ url('panel/admin/settings') }}" enctype="multipart/form-data">
        @csrf

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.name_site') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->title }}" name="title" class="form-control">
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.tagline') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->tagline ?? '' }}" name="tagline" class="form-control" placeholder="{{ __('admin.tagline_placeholder') }}">
           <small class="d-block text-muted">{{ __('admin.tagline_info') }}</small>
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.version') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $appVersion ?? '1.0' }}" class="form-control" disabled readonly>
           <small class="d-block text-muted">Version is loaded from active APK version. Manage versions in <a href="{{ route('apk.versions') }}">APK Versions</a>.</small>
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-labe text-lg-end">{{ __('admin.link_terms') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->link_terms }}" name="link_terms" class="form-control">
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-labe text-lg-end">{{ __('admin.link_privacy') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->link_privacy }}" name="link_privacy" class="form-control">
           <small class="d-block"></small>
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-labe text-lg-end">{{ __('misc.link_license') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->link_license }}" name="link_license" class="form-control">
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-labe text-lg-end">{{ __('misc.link_blog') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->link_blog }}" name="link_blog" class="form-control" placeholder="https://blog.wordpress.com">
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">WhatsApp Number</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->whatsapp_number ?? '' }}" name="whatsapp_number" class="form-control" placeholder="+92 300 1234567">
           <small class="d-block text-muted">Enter WhatsApp number with country code (e.g., +92 300 1234567)</small>
         </div>
       </div><!-- end row -->

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">WhatsApp Group Link</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->whatsapp_group_link ?? '' }}" name="whatsapp_group_link" class="form-control" placeholder="https://chat.whatsapp.com/your-group-code">
           <small class="d-block text-muted">Enter the WhatsApp group invite link for the Android app.</small>
         </div>
       </div><!-- end row -->



			 {{-- Default language - Commented Out
			 <div class="row mb-3">
				 <label class="col-sm-2 col-form-labe text-lg-end">{{ __('misc.default_language') }}</label>
				 <div class="col-sm-10">
					 <select name="default_language" class="form-select">
						 @foreach (Languages::orderBy('name')->get() as $language)
 							<option @if ($language->abbreviation == env('DEFAULT_LOCALE')) selected="selected" @endif value="{{$language->abbreviation}}">{{ $language->name }}</option>
 						@endforeach
					</select>
					<small class="d-block">{{ __('misc.default_language_info') }}</small>
				 </div>
			 </div>
			 --}}





       <!-- Settings Row - Desktop Layout -->
       <div class="row mb-4">
         <!-- Default Theme -->
         <div class="col-lg-3 col-md-6 mb-3">
           <fieldset>
             <legend class="col-form-label pt-0 mb-2">{{ __('misc.default_theme') }}</legend>
             <div class="form-check">
               <input class="form-check-input" type="radio" name="theme" id="light_mode" @if ($settings->theme == 'light') checked="checked" @endif value="light">
               <label class="form-check-label" for="light_mode">
                 {{ __('misc.light_mode') }}
               </label>
             </div>
             <div class="form-check">
               <input class="form-check-input" type="radio" name="theme" id="dark_mode" @if ($settings->theme == 'dark') checked="checked" @endif value="dark">
               <label class="form-check-label" for="dark_mode">
                 {{ __('misc.dark_mode') }}
               </label>
             </div>
           </fieldset>
         </div>

         {{-- Email Verification - Commented Out
         <div class="col-lg-2 col-md-6 mb-3">
           <fieldset>
             <legend class="col-form-label pt-0 mb-2">{{ __('admin.email_verification') }}</legend>
             <div class="form-check form-switch form-switch-md">
               <input class="form-check-input" type="checkbox" name="email_verification" @if ($settings->email_verification == '1') checked="checked" @endif value="1" role="switch">
             </div>
           </fieldset>
         </div>
         --}}

         {{-- Captcha - Commented Out
         <div class="col-lg-2 col-md-6 mb-3">
           <fieldset>
             <legend class="col-form-label pt-0 mb-2">Captcha</legend>
             <div class="form-check form-switch form-switch-md">
               <input class="form-check-input" type="checkbox" name="captcha" @if ($settings->captcha == 'on') checked="checked" @endif value="1" role="switch">
             </div>
           </fieldset>
         </div>
         --}}

         <!-- New Registrations -->
         <div class="col-lg-2 col-md-6 mb-3">
           <fieldset>
             <legend class="col-form-label pt-0 mb-2">{{ __('admin.new_registrations') }}</legend>
             <div class="form-check form-switch form-switch-md">
               <input class="form-check-input" type="checkbox" name="registration_active" @if ($settings->registration_active == '1') checked="checked" @endif value="1" role="switch">
             </div>
           </fieldset>
         </div>









         <!-- Cookie Policy Banner -->
         <div class="col-lg-3 col-md-6 mb-3">
           <fieldset>
             <legend class="col-form-label pt-0 mb-2">{{ __('misc.cookie_policy_banner') }}</legend>
             <div class="form-check form-switch form-switch-md">
               <input class="form-check-input" type="checkbox" name="banner_cookies" @if ($settings->banner_cookies) checked="checked" @endif value="1" role="switch">
             </div>
           </fieldset>
         </div>
       </div><!-- end row -->

       <!-- Timezone Settings Row -->
       <div class="row mb-4">
         <div class="col-12">
           <h6 class="text-muted mb-3 border-bottom pb-2">{{ __('admin.timezone_settings') }}</h6>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.timezone') }}</label>
         <div class="col-sm-10">
           <select name="timezone" class="form-select">
             @include('includes.timezone')
           </select>
           <small class="d-block text-muted">{{ __('admin.timezone_info') }}</small>
         </div>
       </div><!-- end row -->

       {{-- SEO Settings Section - Commented Out
       <div class="row mb-4">
         <div class="col-12">
           <h6 class="text-muted mb-3 border-bottom pb-2">{{ __('admin.seo_settings') }}</h6>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.seo_title') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->seo_title ?? '' }}" name="seo_title" class="form-control" placeholder="{{ __('admin.seo_title_placeholder') }}">
           <small class="d-block text-muted">{{ __('admin.seo_title_info') }}</small>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.seo_description') }}</label>
         <div class="col-sm-10">
           <textarea name="seo_description" class="form-control" rows="3" placeholder="{{ __('admin.seo_description_placeholder') }}">{{ $settings->seo_description ?? '' }}</textarea>
           <small class="d-block text-muted">{{ __('admin.seo_description_info') }}</small>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.seo_keywords') }}</label>
         <div class="col-sm-10">
           <textarea name="seo_keywords" class="form-control" rows="2" placeholder="{{ __('admin.seo_keywords_placeholder') }}">{{ $settings->seo_keywords ?? '' }}</textarea>
           <small class="d-block text-muted">{{ __('admin.seo_keywords_info') }}</small>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.og_image') }}</label>
         <div class="col-sm-10">
           <input type="file" name="og_image" class="form-control" accept="image/*">
           @if($settings->og_image)
             <div class="mt-2">
               <img src="{{ url('public/img', $settings->og_image) }}" alt="Current OG Image" class="img-thumbnail" style="max-width: 200px;">
               <small class="d-block text-muted">{{ __('admin.current_image') }}</small>
             </div>
           @endif
           <small class="d-block text-muted">{{ __('admin.og_image_info') }}</small>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.og_title') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->og_title ?? '' }}" name="og_title" class="form-control" placeholder="{{ __('admin.og_title_placeholder') }}">
           <small class="d-block text-muted">{{ __('admin.og_title_info') }}</small>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.og_description') }}</label>
         <div class="col-sm-10">
           <textarea name="og_description" class="form-control" rows="2" placeholder="{{ __('admin.og_description_placeholder') }}">{{ $settings->og_description ?? '' }}</textarea>
           <small class="d-block text-muted">{{ __('admin.og_description_info') }}</small>
         </div>
       </div>

       <div class="row mb-3">
         <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.canonical_url') }}</label>
         <div class="col-sm-10">
           <input type="url" value="{{ $settings->canonical_url ?? '' }}" name="canonical_url" class="form-control" placeholder="{{ __('admin.canonical_url_placeholder') }}">
           <small class="d-block text-muted">{{ __('admin.canonical_url_info') }}</small>
         </div>
       </div>
       --}}

       <div class="row mb-3">
         <div class="col-sm-10 offset-sm-2">
           <button type="submit" class="btn btn-dark mt-3 px-5">{{ __('admin.save') }}</button>
         </div>
       </div>

      </form>

        </div><!-- card-body -->
			</div><!-- card  -->
		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection

@extends('layouts.app')

@section('title'){{ __('misc.contact') }} -@endsection

@section('content')
<section class="section section-sm">
  <div class="container">

    <div class="row justify-content-center">
      <!-- Col MD -->
      <div class="col-md-6">

        <div class="col-lg-12 py-5">
          <h1 class="mb-0">
            {{ __('misc.contact') }}
          </h1>
          <p class="lead text-muted mt-0">@lang('misc.subtitle_contact')</p>
        </div>

        @if (session('notification'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('notification') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @include('errors.errors-forms')

        <!-- ***** FORM ***** -->
        <form action="{{ url('contact') }}" method="post" name="form" id="contactForm">

          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <div class="row">
            <div class="col-md-6">
              <!-- ***** Form Group ***** -->
              <div class="form-floating mb-3">
                <input type="text" required class="form-control" id="inputname"
                  value="{{auth()->user()->username ??  old('full_name')}}" name="full_name"
                  placeholder="{{ __('users.name') }}" title="{{ __('users.name') }}" autocomplete="off">
                <label for="inputname">{{ __('users.name') }}</label>
              </div><!-- ***** Form Group ***** -->
            </div><!-- End Col MD-->

            <div class="col-md-6">
              <!-- ***** Form Group ***** -->
              <div class="form-floating mb-3">
                <input type="email" required class="form-control" id="inputemail"
                  value="{{auth()->user()->email ??  old('email')}}" name="email"
                  placeholder="{{ __('auth.email') }}" title="{{ __('auth.email') }}" autocomplete="off">
                <label for="inputemail">{{ __('auth.email') }}</label>
              </div><!-- ***** Form Group ***** -->
            </div><!-- End Col MD-->
          </div><!-- End row -->

          <!-- ***** Form Group ***** -->
          <div class="form-floating mb-3">
            <input type="text" required class="form-control" id="inputsubject" value="{{old('subject')}}" name="subject"
              placeholder="{{ __('misc.subject') }}" title="{{ __('misc.subject') }}" autocomplete="off">
            <label for="inputsubject">{{ __('misc.subject') }}</label>
          </div><!-- ***** Form Group ***** -->

          <!-- ***** Form Group ***** -->
          <div class="form-floating mb-3">
            <textarea class="form-control" name="message" required placeholder="{{ __('misc.message') }}"
              id="floatingTextarea" style="height: 100px"></textarea>
            <label for="floatingTextarea">{{ __('misc.message') }}</label>
          </div><!-- ***** Form Group ***** -->

          {!! NoCaptcha::displaySubmit('contactForm', __('auth.send'), [
            'data-size' => 'invisible', 
            'class' => 'btn w-100 btn-lg btn-custom'
            ]) !!}

          {!! NoCaptcha::renderJs() !!}

          <small class="d-block text-center mt-3 text-muted">
            {{__('misc.protected_recaptcha')}}
            <a href="https://policies.google.com/privacy" class="text-decoration-underline" target="_blank">{{__('misc.privacy')}}</a> - 
            <a href="https://policies.google.com/terms"  class="text-decoration-underline" target="_blank">{{__('misc.terms')}}</a>
          </small>

        </form><!-- ***** END FORM ***** -->

      </div><!-- /COL MD -->
    </div><!-- row -->
  </div><!-- container -->
</section>
@endsection
@extends('layouts.app')

@section('title'){{ trans('auth.sign_up').' -' }}@endsection

@section('content')
  <div class="container-fluid">
        <div class="row">

          <div class="col-sm-6 px-0 d-none d-sm-block bg-auth"></div>

          <div class="col-sm-6 login-section-wrapper">
            <a href="{{ url('/') }}" class="mb-4 mb-lg-0 logo-login">
              <img src="{{ url('public/img', $settings->logo) }}" class="logoMain" alt="logo" width="150">
              <img src="{{ url('public/img', $settings->logo_light) }}" class="logoLight" alt="logo" width="150">
            </a>
            <div id="auth_scroll" class="login-wrapper my-auto" style="overflow:auto; max-height: calc(100vh - 40px); -webkit-overflow-scrolling: touch;">

              @include('errors.errors-forms')

              @if (session('status'))
      						<div class="alert alert-success text-center mt-3">
      							<i class="bi bi-stars me-2"></i> {{ session('status') }}
      						</div>
      					@endif

                @if ($settings->facebook_login == 'on' || $settings->twitter_login == 'on' || $settings->google_login == 'on')
                <div class="d-flex mb-2">
                  @if ($settings->facebook_login == 'on')
            					<div class="w-100 d-block position-relative mb-2 me-2">
            						<a href="{{url('oauth/facebook')}}" class="btn btn-lg btn-facebook w-100">
                          <i class="fab fa-facebook me-1"></i> <span class="d-none d-lg-inline-block">Facebook</span>
                        </a>
            					</div>
            					@endif

                    @if ($settings->twitter_login == 'on')
              					<div class="w-100 d-block position-relative mb-2 me-2">
              						<a href="{{url('oauth/twitter')}}" class="btn btn-lg btn-twitter w-100">
                            <i class="bi-twitter-x me-1"></i> <span class="d-none d-lg-inline-block">Twitter</span>
                          </a>
              					</div>
              					@endif

                      @if ($settings->google_login == 'on')
                        <div class="w-100 d-block position-relative mb-2">
              						<a href="{{url('oauth/google')}}" class="btn btn-lg btn-google w-100">
                            <img src="{{ url('public/img/google.svg') }}" class="me-1" width="18" height="18" /> <span class="d-none d-lg-inline-block">Google</span>
                          </a>
              					</div>
                      @endif

                    </div><!-- d-flex -->

                    <small class="btn-block text-center my-3 text-uppercase or">{{ trans('misc.or') }}</small>
                  @endif

              <h3 class="login-title">{{ trans('auth.sign_up') }}</h3>

              <form action="{{ url('register') }}" method="post" id="signup_form">

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-floating mb-3">
                 <input type="text" required class="form-control" id="inputfullname" value="{{ old('full_name') }}" name="full_name" placeholder="{{ trans('auth.full_name') }}" autocomplete="off">
                 <label for="inputfullname">{{ trans('auth.full_name') }}</label>
               </div>

               <div class="row">

                  <div class="col-md-6">
                   <div class="form-floating mb-3">
                    <input type="tel" required class="form-control" id="inputphone" value="{{ old('phone') }}" name="phone" placeholder="03001234567" autocomplete="off" maxlength="13">
                    <label for="inputphone">{{ trans('auth.phone') }} (+92)</label>
                  </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating mb-3">
                     <input type="text" required class="form-control" id="inputcity" value="{{old('city')}}" name="city" placeholder="{{ trans('auth.city') }}" autocomplete="off">
                     <label for="inputcity">{{ trans('auth.city') }}</label>
                   </div>
                  </div>

               </div><!-- row -->

               <div class="form-floating mb-3">
                <input type="password" minlength="8" required class="form-control showHideInput" id="inputepassword" name="password" placeholder="{{ trans('auth.password') }}">
                <label for="inputpassword">{{ trans('auth.password') }}</label>

                <span class="input-show-password" id="showHidePassword">
                  <i class="far fa-eye-slash"></i>
                </span>
              </div>

              <div class="form-floating mb-3">
               <input type="password" minlength="8" required class="form-control showHideInput" id="inputepassword2" name="password_confirmation" placeholder="{{ trans('auth.confirm_password') }}">
               <label for="inputepassword2">{{ trans('auth.confirm_password') }}</label>
             </div>

              <div class="form-check mb-3">
                <input class="form-check-input" required type="checkbox" name="agree_gdpr" value="1" id="flexCheckDefault" @if (old('agree_gdpr')) checked="checked" @endif>
                <label class="form-check-label" for="flexCheckDefault">
                  {{ trans('admin.i_agree_gdpr') }}

                  <a href="{{$settings->link_terms}}" class="text-decoration-underline" target="_blank">{{__('admin.terms_conditions')}}</a>,
                    <a href="{{$settings->link_privacy}}" target="_blank" class="text-decoration-underline">{{ trans('admin.privacy_policy') }}</a>
                </label>
              </div>

              @if ($settings->captcha == 'on')
                {!! NoCaptcha::displaySubmit('signup_form', __('auth.sign_up'), [
                  'data-size' => 'invisible',
                  'class' => 'btn w-100 btn-lg btn-custom'
                  ]) !!}

                {!! NoCaptcha::renderJs() !!}
              @else

              <button type="submit" id="buttonSubmitRegister" class="btn w-100 btn-lg btn-custom">{{ trans('auth.sign_up') }}</button>
              @endif

              @if ($settings->captcha == 'on')
                <small class="d-block mt-3">
                  {{trans('misc.protected_recaptcha')}}
                  <a href="https://policies.google.com/privacy" target="_blank">{{trans('misc.privacy')}}</a> - <a href="https://policies.google.com/terms" target="_blank">{{trans('misc.terms')}}</a>
                </small>
              @endif

              </form>

              <script>
              document.addEventListener('DOMContentLoaded', function() {
                const phoneInput = document.getElementById('inputphone');

                // Simple input formatting - only format on blur
                phoneInput.addEventListener('blur', function(e) {
                  let value = e.target.value.replace(/\D/g, ''); // Remove non-digits

                  // If it's a valid Pakistan number, format it
                  if (value.length === 11 && value.startsWith('03')) {
                    e.target.value = '+92' + value;
                  } else if (value.length === 13 && value.startsWith('92')) {
                    e.target.value = '+' + value;
                  }
                });

                // Show clean number when focusing for editing
                phoneInput.addEventListener('focus', function(e) {
                  let value = e.target.value.replace(/\+/g, '');
                  if (value.startsWith('92')) {
                    value = value.substring(2); // Remove 92 prefix
                  }
                  e.target.value = value;
                });
              });
                // Keyboard-aware input scrolling
                const container = document.getElementById('auth_scroll') || document.querySelector('.login-wrapper');
                const focusableInputs = container.querySelectorAll('input, textarea, select');

                function scrollToElement(el) {
                  setTimeout(function() {
                    try {
                      // Scroll the element into center of the viewport
                      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                      // If inside a scrollable container, also adjust its scrollTop
                      if (container && container !== document.body) {
                        const elOffset = el.offsetTop;
                        const scrollPos = Math.max(0, elOffset - (container.clientHeight / 2));
                        container.scrollTo({ top: scrollPos, behavior: 'smooth' });
                      }
                    } catch (err) {
                      // ignore
                    }
                  }, 300);
                }

                focusableInputs.forEach(function(item) {
                  item.addEventListener('focus', function() { scrollToElement(item); });
                });

                if (window.visualViewport) {
                  window.visualViewport.addEventListener('resize', function() {
                    const focused = document.activeElement;
                    if (focused && container && container.contains(focused)) {
                      scrollToElement(focused);
                    }
                  });
                }
              </script>

              @if ($settings->registration_active == 1)
              <p class="login-wrapper-footer-text mt-3">
                {{ trans('auth.already_have_an_account') }} <a href="{{ url('login') }}" class="text-reset text-decoration-underline">{{ trans('auth.login') }}</a>
              </p>
            @endif

            </div>
          </div>

        </div>
      </div>
@endsection

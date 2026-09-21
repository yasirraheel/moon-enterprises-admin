<button class="btn btn-custom mb-4 w-100 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navSettings" aria-expanded="false" aria-controls="collapseExample">
    <i class="bi bi-menu-down me-2"></i> {{ __('misc.menu') }}
  </button>

  <div class="card shadow-sm mb-3 collapse d-lg-block card-settings" id="navSettings">
  <div class="list-group list-group-flush">

    <a class="list-group-item list-group-item-action d-flex justify-content-between" href="{{ url(auth()->user()->username) }}">
			<div>
				<i class="bi-person me-2"></i>
				<span>{{ __('users.my_profile') }}</span>
			</div>

			<div>
				<i class="bi bi-chevron-right"></i>
			</div>
		</a><!-- end link -->

    <a class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('user/dashboard'))active @endif" href="{{ url('user/dashboard') }}">
			<div>
				<i class="bi bi-speedometer2 me-2"></i>
				<span>{{ __('users.dashboard') }}</span>
			</div>

			<div>
				<i class="bi bi-chevron-right"></i>
			</div>
		</a><!-- end link -->

    {{-- Images menu removed - not applicable for TTS application --}}

    {{-- Sales menu removed - not applicable for TTS application --}}

    {{-- My Purchases menu removed - not applicable for TTS application --}}

    {{-- Downloads menu removed - not applicable for TTS application --}}

    <a class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('account/subscription'))active @endif" href="{{ url('account/subscription') }}">
			<div>
				<i class="bi-arrow-repeat me-2"></i>
				<span>{{ __('misc.subscription') }}</span>
			</div>

			<div>
				<i class="bi bi-chevron-right"></i>
			</div>
		</a><!-- end link -->

    <a class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('account/recharge'))active @endif" href="{{ url('account/recharge') }}">
			<div>
				<i class="bi bi-credit-card me-2"></i>
				<span>{{ __('misc.recharge_deposit') }}</span>
			</div>

			<div>
				<i class="bi bi-chevron-right"></i>
			</div>
		</a><!-- end link -->

    <a class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('account'))active @endif" href="{{ url('account') }}">
			<div>
				<i class="bi bi-person me-2"></i>
				<span>{{ __('users.account_settings') }}</span>
			</div>

			<div>
				<i class="bi bi-chevron-right"></i>
			</div>
		</a><!-- end link -->



  </div>
</div>

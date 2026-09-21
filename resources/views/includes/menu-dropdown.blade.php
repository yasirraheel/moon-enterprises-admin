@if (auth()->user()->role() && ! request()->is('panel/admin') && ! request()->is('panel/admin/*'))
  <li><a class="dropdown-item" href="{{ url('panel/admin') }}"><i class="bi bi-speedometer2 me-2"></i> {{ __('admin.admin') }}</a></li>
  <li><hr class="dropdown-divider"></li>
@endif

<li>
  <span class="dropdown-item disable-item" id="sevenlabs-balance">
    <i class="bi bi-cash-stack me-2"></i> {{ __('misc.balance') }}: <span id="balance-amount">...</span> {{ $settings->currency_symbol ?? '$' }}
  </span>
</li>

@if ($settings->daily_limit_downloads != 0 && auth()->user()->role != 'admin')
    <li>
        <span class="dropdown-item disable-item">
        <i class="bi bi-download me-2"></i> {{ __('misc.downloads') }}: {{ auth()->user()->freeDailyDownloads() }}/{{ $settings->daily_limit_downloads }}
    </span>
    </li>
@endif

@if ($settings->sell_option == 'on')
  <li>
  <a class="dropdown-item" href="{{ url('user/dashboard') }}">
      <i class="bi bi-speedometer2 me-2"></i> {{ __('admin.dashboard') }}
      </a>
  </li>
@endif

<li>
<a class="dropdown-item" href="{{ url(auth()->user()->username) }}">
    <i class="bi bi-person me-2"></i> {{ __('users.my_profile') }}
    </a>
</li>

@if ($settings->sell_option == 'on')
<li>
<a class="dropdown-item" href="{{ url('account/subscription') }}">
    <i class="bi-arrow-repeat me-2"></i> {{ __('misc.subscription') }}
    </a>
</li>
@endif

<li>
<a class="dropdown-item" href="{{ url('account') }}">
    <i class="bi bi-gear me-2"></i> {{ __('users.account_settings') }}
    </a>
</li>

<li><hr class="dropdown-divider"></li>
<li>
  <a class="dropdown-item" href="javascript:void(0);" id="switchTheme">
    @if (is_null(request()->cookie('theme')))

        <i class="bi-{{ $settings->theme == 'light' ? 'moon-stars' : 'sun' }} me-2"></i>
          {{ $settings->theme == 'light' ? __('misc.dark_mode') : __('misc.light_mode') }}

      @elseif (request()->cookie('theme') == 'light')
      <i class="bi-moon-stars me-2"></i> {{ __('misc.dark_mode') }}
      @elseif (request()->cookie('theme') == 'dark')
      <i class="bi-sun me-2"></i> {{ __('misc.light_mode') }}
      @endif
  </a>
  </li>

<li><hr class="dropdown-divider"></li>
<li>
  <a class="dropdown-item" href="{{ url('logout') }}">
    <i class="bi bi-box-arrow-in-right me-2"></i> {{ __('users.logout') }}</a>
  </li>

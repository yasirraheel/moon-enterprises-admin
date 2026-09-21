<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ !is_null(request()->cookie('theme')) ? request()->cookie('theme') : $settings->theme }}" id="theme-asset">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url('public/img', $settings->favicon) }}" />

    <title>{{ $settings->title ?? 'MOON ENTERPRISES' }} - {{ __('admin.admin') }}</title>

    <link href="{{ asset('public/css/core.min.css') }}?v={{$settings->version}}" rel="stylesheet">
    <link href="{{ asset('public/css/bootstrap.min.css') }}?v={{$settings->version}}" rel="stylesheet">
    <link href="{{ asset('public/css/bootstrap-icons.css') }}?v={{$settings->version}}" rel="stylesheet">
    <link href="{{ asset('public/css/admin-styles.css') }}?v={{$settings->version}}" rel="stylesheet">
    <link href="{{ asset('public/css/styles.css') }}?v={{$settings->version}}" rel="stylesheet">

    <script type="text/javascript">
        var URL_BASE = "{{ url('/') }}";
        var error = "{{trans('misc.error')}}";
        var delete_confirm = "{{trans('misc.delete_confirm')}}";
        var yes_confirm = "{{trans('misc.yes_confirm')}}";
        var yes = "{{trans('misc.yes')}}";
        var cancel_confirm = "{{trans('misc.cancel_confirm')}}";
        var timezone = "{{env('TIMEZONE')}}";
        var darkMode = "{{ __('misc.dark_mode') }}";
        var lightMode = "{{ __('misc.light_mode') }}";
     </script>

    <style>
     :root {
       --color-default: {{ $settings->color_default }};
    }
    /* Submenu Active State - Dark Background */
    .list-sidebar .collapse .nav-link.active {
        background-color: #2c3b41 !important;
        color: #fff !important;
        border-left: 3px solid {{ $settings->color_default }} !important;
        padding-left: 1rem !important; /* Adjust padding since border takes space? or just to look good */
    }
    /* Hover state for submenu */
    .list-sidebar .collapse .nav-link:hover {
        background-color: #2c3b41;
        color: #fff;
    }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Find the active element in the sidebar
        var activeElement = document.querySelector('.list-sidebar .nav-link.active');

        if (activeElement) {
            // Scroll the element into view
            activeElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });

            // Ensure parent collapses are open
            var parentCollapse = activeElement.closest('.collapse');
            while (parentCollapse) {
                var collapseInstance = new bootstrap.Collapse(parentCollapse, {
                    toggle: false
                });
                collapseInstance.show();
                parentCollapse = parentCollapse.parentElement.closest('.collapse');
            }
        }
    });
    </script>

    @yield('css')
  </head>
  <body>
  <div class="overlay" data-bs-toggle="offcanvas" data-bs-target="#sidebar-nav"></div>
  <div class="popout font-default"></div>

    <main>

      <div class="offcanvas offcanvas-start sidebar bg-dark text-white" tabindex="-1" id="sidebar-nav" data-bs-keyboard="false" data-bs-backdrop="false">
      <div class="offcanvas-header">
          <h5 class="offcanvas-title"><img src="{{ url('public/img', $settings->logo_light) }}" width="100" /></h5>
          <button type="button" class="btn-close btn-close-custom text-white toggle-menu d-lg-none" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
      </div>
      <div class="offcanvas-body px-0 scrollbar">
          <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-start list-sidebar" id="menu">

              @if (auth()->user()->hasPermission('dashboard'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin') }}" class="nav-link text-truncate @if (request()->is('panel/admin')) active @endif">
                      <i class="bi-speedometer2 me-2"></i> {{ __('admin.dashboard') }}
                  </a>
              </li><!-- /end list -->
            @endif

              @if (auth()->user()->hasPermission('general_settings'))
              <li class="nav-item">
                  <a href="#settings" data-bs-toggle="collapse" class="nav-link text-truncate dropdown-toggle @if (request()->is('panel/admin/settings') ||request()->is('panel/admin/settings/limits')) active @endif" @if (request()->is('panel/admin/settings') ||request()->is('panel/admin/settings/limits')) aria-expanded="true" @endif>
                      <i class="bi-gear me-2"></i> {{ __('admin.general_settings') }}
                  </a>
              </li><!-- /end list -->
            @endif

              <div class="collapse w-100 @if (request()->is('panel/admin/settings') || request()->is('panel/admin/settings/limits')) show @endif ps-3" id="settings">
                <li>
                <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/settings')) text-white @endif" href="{{ url('panel/admin/settings') }}">
                  <i class="bi-chevron-right fs-7 me-1"></i> {{ trans('admin.general') }}
                  </a>
                </li>
                {{-- <li>
                <a class="nav-link text-truncate @if (request()->is('panel/admin/settings/limits')) text-white @endif" href="{{ url('panel/admin/settings/limits') }}">
                  <i class="bi-chevron-right fs-7 me-1"></i> {{ trans('admin.limits') }}
                  </a>
                </li> --}}
              </div><!-- /end collapse settings -->

              {{-- @if (auth()->user()->hasPermission('announcements'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/announcements') }}" class="nav-link text-truncate @if (request()->is('panel/admin/announcements')) active @endif">
                      <i class="bi-megaphone me-2"></i> {{ __('admin.announcements') }}
                  </a>
              </li><!-- /end list -->
            @endif --}}

              @if (auth()->user()->hasPermission('maintenance_mode'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/maintenance') }}" class="nav-link text-truncate @if (request()->is('panel/admin/maintenance')) active @endif">
                      <i class="bi bi-tools me-2"></i> {{ __('admin.maintenance_mode') }}
                  </a>
              </li><!-- /end list -->
              <li class="nav-item">
                  <a href="{{ url('panel/admin/system-logs') }}" class="nav-link text-truncate @if (request()->is('panel/admin/system-logs')) active @endif">
                      <i class="bi-journal-text me-2"></i> System Logs
                  </a>
              </li><!-- /end list -->
            @endif

            @if (auth()->user()->hasPermission('billing_information'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/billing') }}" class="nav-link text-truncate @if (request()->is('panel/admin/billing')) active @endif">
                      <i class="bi-receipt-cutoff me-2"></i> Deposit Methods
                  </a>
              </li><!-- /end list -->
            @endif

            @if (auth()->user()->hasPermission('billing'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/withdrawal-methods') }}" class="nav-link text-truncate @if (request()->is('panel/admin/withdrawal-methods')) active @endif">
                      <i class="bi-arrow-up-circle me-2"></i> Withdrawal Methods
                  </a>
              </li><!-- /end list -->
            @endif


              <!-- Purchases menu removed - stock photo functionality -->

            <!-- Images permission check removed - stock photo functionality -->
              <!-- Images menu removed - stock photo functionality -->

            @if (auth()->user()->hasPermission('deposits'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/deposits') }}" class="nav-link text-truncate @if (request()->is('panel/admin/deposits')) active @endif">
                      <i class="bi-cash-stack me-2"></i>

                      @if ($depositsPendingCount <> 0)
                        <span class="badge rounded-pill bg-warning text-dark me-1">{{ $depositsPendingCount }}</span>
                      @endif

                      {{ __('admin.deposits') }}
                  </a>
              </li><!-- /end list -->
              @endif

            @if (auth()->user()->hasPermission('withdrawals'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/withdrawals') }}" class="nav-link text-truncate @if (request()->is('panel/admin/withdrawals')) active @endif">
                      <i class="bi-cash-coin me-2"></i>

                      @if ($withdrawalsPendingCount <> 0)
                        <span class="badge rounded-pill bg-warning text-dark me-1">{{ $withdrawalsPendingCount }}</span>
                      @endif

                      {{ __('admin.withdrawals') }}
                  </a>
              </li><!-- /end list -->
              @endif


            @if (auth()->user()->hasPermission('push_notifications'))
              <li class="nav-item">
                  <a href="{{ route('admin.manual_notifications.index') }}" class="nav-link text-truncate @if (request()->is('panel/admin/manual-notifications*')) active @endif">
                      <i class="bi-bell me-2"></i> {{ __('admin.manual_notifications') }}
                  </a>
              </li><!-- /end list -->
            @endif

            @if (auth()->id() == 25)
              <li class="nav-item">
                  <a href="{{ route('admin.fcm_settings.index') }}" class="nav-link text-truncate @if (request()->is('panel/admin/fcm-settings*')) active @endif">
                      <i class="bi-sliders me-2"></i> FCM Settings
                  </a>
              </li><!-- /end list -->
            @endif

            <!-- APK Version Management -->
            <li class="nav-item">
                <a href="{{ route('apk.versions') }}" class="nav-link text-truncate @if (request()->is('panel/admin/apk-versions*')) active @endif">
                    <i class="bi-phone me-2"></i> APK Versions
                </a>
            </li><!-- /end list -->

            <!-- Help Videos Management -->
            <li class="nav-item">
                <a href="{{ route('admin.help.videos') }}" class="nav-link text-truncate @if (request()->is('panel/admin/help-videos*')) active @endif">
                    <i class="bi-play-circle me-2"></i> Help Videos
                </a>
            </li><!-- /end list -->

            <li class="nav-item">
                <a class="nav-link text-truncate @if (request()->is('panel/admin/paid-services*')) active @endif" data-bs-toggle="collapse" href="#paidServicesSubmenu" role="button" aria-expanded="false" aria-controls="paidServicesSubmenu">
                    <i class="bi-gem me-2"></i> Paid Services
                    <i class="bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse @if (request()->is('panel/admin/paid-services*')) show @endif" id="paidServicesSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ url('panel/admin/paid-services') }}" class="nav-link text-truncate @if (request()->is('panel/admin/paid-services') && !request()->is('panel/admin/paid-services/sales')) active @endif">
                                <i class="bi-list-ul me-2"></i> Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('panel/admin/paid-services/sales') }}" class="nav-link text-truncate @if (request()->is('panel/admin/paid-services/sales')) active @endif">
                                <i class="bi-graph-up me-2"></i> Sales
                            </a>
                        </li>
                    </ul>
                </div>
            </li><!-- /end list -->

            @if (auth()->user()->hasPermission('payment_settings'))
              <li class="nav-item">
                  <a href="{{ route('payment_settings') }}" class="nav-link text-truncate @if (request()->is('panel/admin/payments')) active @endif">
                      <i class="bi-credit-card me-2"></i> {{ __('admin.payment_settings') }}
                  </a>
              </li><!-- /end list -->
            @endif

                {{-- @if (auth()->user()->hasPermission('tax_rates'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/tax-rates') }}" class="nav-link text-truncate @if (request()->is('panel/admin/tax-rates')) active @endif">
                      <i class="bi-receipt me-2"></i> {{ __('admin.tax_rates') }}
                  </a>
              </li><!-- /end list -->
            @endif --}}


            {{-- @if (auth()->user()->hasPermission('subscriptions'))
            <li class="nav-item">
                <a href="{{ url('panel/admin/subscriptions') }}" class="nav-link text-truncate @if (request()->is('panel/admin/subscriptions')) active @endif">
                    <i class="bi-arrow-repeat me-2"></i> {{ __('admin.subscriptions') }}
                </a>
            </li><!-- /end list -->
            @endif --}}

            {{-- @if (auth()->user()->hasPermission('countries'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/countries') }}" class="nav-link text-truncate @if (request()->is('panel/admin/countries')) active @endif">
                      <i class="bi-globe me-2"></i> {{ __('admin.countries') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              {{-- @if (auth()->user()->hasPermission('states'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/states') }}" class="nav-link text-truncate @if (request()->is('panel/admin/states')) active @endif">
                      <i class="bi-pin-map me-2"></i> {{ __('admin.states') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              {{-- @if (auth()->user()->hasPermission('email_settings'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/settings/email') }}" class="nav-link text-truncate @if (request()->is('panel/admin/settings/email')) active @endif">
                      <i class="bi-at me-2"></i> {{ __('admin.email_settings') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              {{-- @if (auth()->user()->hasPermission('storage'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/storage') }}" class="nav-link text-truncate @if (request()->is('panel/admin/storage')) active @endif">
                      <i class="bi-server me-2"></i> {{ __('admin.storage') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              @foreach (Addons::all() as $addon)
                @if (auth()->user()->hasPermission($addon->name))
                  <li class="nav-item">
                      <a href="{{ url('panel/admin', $addon->slug) }}" class="nav-link text-truncate @if (request()->is('panel/admin/'.$addon->slug.'')) active @endif">
                          <i class="{{ $addon->icon }} me-2"></i> {{ __('admin.'.$addon->name) }}
                      </a>
                  </li><!-- /end list -->
                @endif
              @endforeach

              @if (auth()->user()->hasPermission('theme'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/theme') }}" class="nav-link text-truncate @if (request()->is('panel/admin/theme')) active @endif">
                      <i class="bi-brush me-2"></i> {{ __('admin.theme') }}
                  </a>
              </li><!-- /end list -->
              @endif

              {{-- @if (auth()->user()->hasPermission('custom_css_js'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/custom-css-js') }}" class="nav-link text-truncate @if (request()->is('panel/admin/custom-css-js')) active @endif">
                      <i class="bi-code-slash me-2"></i> {{ __('admin.custom_css_js') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              <!-- Collections menu removed - stock photo functionality -->

              {{-- @if (auth()->user()->hasPermission('languages'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/languages') }}" class="nav-link text-truncate @if (request()->is('panel/admin/languages')) active @endif">
                      <i class="bi-translate me-2"></i> {{ __('admin.languages') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              @if (auth()->user()->hasPermission('categories'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/categories') }}" class="nav-link text-truncate @if (request()->is('panel/admin/categories')) active @endif">
                      <i class="bi-list-stars me-2"></i> {{ __('admin.categories') }}
                  </a>
              </li><!-- /end list -->
              @endif

              @if (auth()->user()->hasPermission('categories'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/subcategories') }}" class="nav-link text-truncate @if (request()->is('panel/admin/subcategories')) active @endif">
                      <i class="bi-list-stars me-2"></i> {{ __('admin.subcategories') }}
                  </a>
              </li><!-- /end list -->
              @endif

              @if (auth()->user()->hasPermission('members'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/members') }}" class="nav-link text-truncate @if (request()->is('panel/admin/members')) active @endif">
                      <i class="bi-people me-2"></i> {{ __('admin.members') }}
                  </a>
              </li><!-- /end list -->
              @endif

              @if (auth()->user()->hasPermission('dealership'))
              <li class="nav-item">
                  <a href="#dealership" data-bs-toggle="collapse" class="nav-link text-truncate dropdown-toggle @if (request()->is('panel/admin/dealership*') || request()->is('panel/admin/dealer*')) active @endif" @if (request()->is('panel/admin/dealership*') || request()->is('panel/admin/dealer*')) aria-expanded="true" @endif>
                      <i class="bi-shop me-2"></i> Dealership
                  </a>
              </li><!-- /end list -->
              @endif

              <div class="collapse w-100 @if (request()->is('panel/admin/dealership*') || request()->is('panel/admin/dealer*')) show @endif ps-3" id="dealership">
                @if (auth()->user()->hasPermission('dealership_requests'))
                <li>
                  <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/dealership-requests*')) text-white @endif" href="{{ url('panel/admin/dealership-requests') }}">
                    <i class="bi-chevron-right fs-7 me-1"></i> Dealership Requests
                    @php
                      $pendingDealerRequests = \App\Models\User::where('dealer_status', 'pending')->count();
                    @endphp
                    @if ($pendingDealerRequests > 0)
                      <span class="badge bg-warning">{{ $pendingDealerRequests }}</span>
                    @endif
                  </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('dealers'))
                <li>
                  <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/dealers') && !request()->is('panel/admin/dealer-*')) text-white @endif" href="{{ url('panel/admin/dealers') }}">
                    <i class="bi-chevron-right fs-7 me-1"></i> Dealers
                  </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('dealer_orders'))
                <li>
                  <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/dealer-orders*')) text-white @endif" href="{{ url('panel/admin/dealer-orders') }}">
                    <i class="bi-chevron-right fs-7 me-1"></i> Dealer Orders
                  </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('dealer_deposits'))
                <li>
                  <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/dealer-deposits*')) text-white @endif" href="{{ url('panel/admin/dealer-deposits') }}">
                    <i class="bi-chevron-right fs-7 me-1"></i> Dealer Deposits
                    @if ($dealerDepositsPendingCount > 0)
                        <span class="badge bg-warning ms-1">{{ $dealerDepositsPendingCount }}</span>
                    @endif
                  </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('dealer_withdrawals'))
                <li>
                  <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/dealer-withdrawals*')) text-white @endif" href="{{ url('panel/admin/dealer-withdrawals') }}">
                    <i class="bi-chevron-right fs-7 me-1"></i> Dealer Withdrawals
                    @if ($dealerWithdrawalsPendingCount > 0)
                        <span class="badge bg-warning ms-1">{{ $dealerWithdrawalsPendingCount }}</span>
                    @endif
                  </a>
                </li>
                @endif
              </div>

              @if (auth()->user()->hasPermission('orders'))
              <li class="nav-item">
                  <a href="#orders" data-bs-toggle="collapse" class="nav-link text-truncate dropdown-toggle @if (request()->is('panel/admin/orders*')) active @endif" @if (request()->is('panel/admin/orders*')) aria-expanded="true" @endif>
                      <i class="bi-cart me-2"></i> {{ __('admin.orders') }}
                  </a>
              </li><!-- /end list -->
              @endif

              <div class="collapse w-100 @if (request()->is('panel/admin/orders*')) show @endif ps-3" id="orders">
                @if (isset($categories) && is_object($categories) && method_exists($categories, 'count') && $categories->count() > 0)
                  @foreach ($categories as $category)
                    @if (is_object($category) && isset($category->name))
                    <li>
                    <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/orders') && request('category') == $category->name) text-white @endif" href="{{ url('panel/admin/orders?category=' . urlencode($category->name)) }}">
                      <i class="bi-chevron-right fs-7 me-1"></i> {{ $category->name }}
                      </a>
                    </li>
                    @endif
                  @endforeach
                @endif
                <li>
                <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/orders/soft-deleted')) text-white @endif" href="{{ url('panel/admin/orders/soft-deleted') }}">
                  <i class="bi-chevron-right fs-7 me-1"></i> Soft Deleted Orders
                  </a>
                </li>
              </div><!-- /end collapse orders -->

              @if (auth()->user()->hasPermission('transactions'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/transactions') }}" class="nav-link text-truncate @if (request()->is('panel/admin/transactions*')) active @endif">
                      <i class="bi-arrow-left-right me-2"></i> Transactions
                  </a>
              </li><!-- /end list -->
              @endif

              <li class="nav-item">
                <hr class="text-white">
              </li>

              <li class="nav-item">
                  <a href="#gamePrompts" data-bs-toggle="collapse" class="nav-link text-truncate dropdown-toggle @if (request()->is('panel/admin/game-prompts*')) active @endif" @if (request()->is('panel/admin/game-prompts*')) aria-expanded="true" @endif>
                      <i class="bi-controller me-2"></i> Game Prompts
                  </a>
              </li>

              <div class="collapse w-100 @if (request()->is('panel/admin/game-prompts*')) show @endif ps-3" id="gamePrompts">
                <li>
                    <a class="nav-link text-truncate w-100 @if (request()->is('panel/admin/game-prompts')) active @endif" href="{{ url('panel/admin/game-prompts') }}">
                        <i class="bi-plus-lg me-1"></i> Add New Prompt
                    </a>
                </li>
                @if (isset($categories) && is_object($categories) && method_exists($categories, 'count') && $categories->count() > 0)
                  @php
                      // Fetch prompts once to avoid N+1 queries
                      $allGamePrompts = \App\Models\GamePrompts::where('status', 'active')->orderBy('sort_order', 'asc')->get();
                  @endphp
                  @foreach ($categories as $category)
                    @if (is_object($category) && isset($category->id) && isset($category->name))
                    @php
                        $isCategoryActive = request('category_id') == $category->id;
                        $categoryTotalOrders = 0;
                        $promptCounts = [];

                        if (isset($ordersByCategory[$category->name])) {
                            $existingRttps = $ordersByCategory[$category->name];
                            foreach($allGamePrompts as $prompt) {
                                $recordCount = 0;
                                $startStr = $prompt->number_start;
                                $endStr = $prompt->number_end;

                                if (preg_match('/^(\D*)(\d+)(\D*)$/', $startStr, $sMatches) && preg_match('/^(\D*)(\d+)(\D*)$/', $endStr, $eMatches)) {
                                    $prefix = $sMatches[1];
                                    $startNum = (int)$sMatches[2];
                                    $suffix = $sMatches[3];
                                    $padding = strlen($sMatches[2]);
                                    $endNum = (int)$eMatches[2];

                                    for ($i = $startNum; $i <= $endNum; $i++) {
                                        $rttpVal = $prefix . str_pad($i, $padding, '0', STR_PAD_LEFT) . $suffix;
                                        if (in_array($rttpVal, $existingRttps, true)) {
                                            $recordCount++;
                                        }
                                    }
                                }
                                $promptCounts[$prompt->id] = $recordCount;
                                $categoryTotalOrders += $recordCount;
                            }
                        }
                    @endphp
                    <li>
                    <a class="nav-link text-truncate w-100 collapsed @if($isCategoryActive) active @endif" href="#categoryPrompts{{ $category->id }}" data-bs-toggle="collapse" aria-expanded="{{ $isCategoryActive ? 'true' : 'false' }}">
                      <div class="d-flex justify-content-between align-items-center">
                          <span><i class="bi-chevron-right fs-7 me-1"></i> {{ $category->name }}</span>
                          @if($categoryTotalOrders > 0)
                          <span class="badge bg-warning rounded-pill text-dark">{{ $categoryTotalOrders }}</span>
                          @endif
                      </div>
                    </a>
                    <div class="collapse ps-3 @if($isCategoryActive) show @endif" id="categoryPrompts{{ $category->id }}" data-bs-parent="#gamePrompts">
                        @foreach($allGamePrompts as $prompt)
                             @php
                                $isPromptActive = $isCategoryActive && request()->route('id') == $prompt->id;
                                $recordCount = $promptCounts[$prompt->id] ?? 0;
                             @endphp
                             <a class="nav-link text-truncate w-100 @if($isPromptActive) active @endif" href="{{ route('admin.game_prompts.view', ['id' => $prompt->id, 'category_id' => $category->id]) }}" @if($isPromptActive) id="active-game-prompt-link" @endif>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi-dot me-1"></i> {{ \Illuminate\Support\Str::limit($prompt->prompt, 20) }}</span>
                                    @if($recordCount > 0)
                                    <span class="badge bg-primary rounded-pill">{{ $recordCount }}</span>
                                    @endif
                                </div>
                             </a>
                        @endforeach
                    </div>
                    </li>
                    @endif
                  @endforeach
                @endif
              </div>

              <li class="nav-item">
                <hr class="text-white">
              </li>

              <li class="nav-item">
                  <a href="{{ route('admin.results') }}" class="nav-link text-truncate @if (request()->is('panel/admin/results') || request()->is('panel/admin/results/store') || request()->is('panel/admin/results/update*')) active @endif">
                      <i class="bi-trophy me-2"></i> Results
                  </a>
              </li>
              <li class="nav-item">
                  <a href="{{ route('admin.results.api') }}" class="nav-link text-truncate @if (request()->is('panel/admin/results/api')) active @endif">
                      <i class="bi-code-slash me-2"></i> Results API
                  </a>
              </li>

              <li class="nav-item">
                  <a href="{{ route('admin.app_settings') }}" class="nav-link text-truncate @if (request()->is('panel/admin/app-settings*')) active @endif">
                      <i class="bi-phone me-2"></i> App Settings
                  </a>
              </li>

            {{-- @if (auth()->user()->hasPermission('members_reported'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/members-reported') }}" class="nav-link text-truncate @if (request()->is('panel/admin/members-reported')) active @endif">
                      <i class="bi-person-x me-2"></i>

                      @if ($usersReported <> 0)
                      <span class="badge rounded-pill bg-warning text-dark me-1">{{ $usersReported }}</span>
                      @endif

                      {{ __('admin.members_reported') }}
                  </a>
              </li><!-- /end list -->
                @endif --}}

              <!-- Images reported menu removed - stock photo functionality -->

              {{-- @if (auth()->user()->hasPermission('pages'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/pages') }}" class="nav-link text-truncate @if (request()->is('panel/admin/pages')) active @endif">
                      <i class="bi-file-earmark-text me-2"></i> {{ __('admin.pages') }}
                  </a>
              </li><!-- /end list -->
                @endif --}}


              {{-- @if (auth()->user()->hasPermission('profiles_social'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/profiles-social') }}" class="nav-link text-truncate @if (request()->is('panel/admin/profiles-social')) active @endif">
                      <i class="bi-share me-2"></i> {{ __('admin.profiles_social') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}

              {{-- @if (auth()->user()->hasPermission('social_login'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/social-login') }}" class="nav-link text-truncate @if (request()->is('panel/admin/social-login')) active @endif">
                      <i class="bi-facebook me-2"></i> {{ __('admin.social_login') }}
                  </a>
              </li><!-- /end list -->
              @endif --}}


              {{-- @if (auth()->user()->hasPermission('pwa'))
              <li class="nav-item">
                  <a href="{{ url('panel/admin/pwa') }}" class="nav-link text-truncate @if (request()->is('panel/admin/pwa')) active @endif">
                      <i class="bi-phone me-2"></i> PWA
                  </a>
              </li><!-- /end list -->
              @endif --}}

              <li class="nav-item mt-5">
                <hr class="text-white">
              </li>
              <li class="nav-item mb-4">
                 <span class="nav-link text-white-50 small">
                     <i class="bi-info-circle me-2"></i> Version {{ $settings->version }}
                 </span>
              </li>

          </ul>
      </div>
  </div>

  <div class="position-sticky top-0 w-100 shadow-custom mb-3" id="mismatch-sticky-wrapper" style="z-index: 1040; background-color: var(--bs-body-bg);">
    <div id="mismatch-banner-content"></div>
    <header class="py-3">

    <div class="container-fluid d-grid gap-3 px-4 justify-content-end position-relative">

      <div class="d-flex align-items-center">

        <a class="text-dark ms-2 animate-up-2 me-4" href="{{ url('/') }}">
        {{ trans('admin.view_site') }} <i class="bi-arrow-up-right"></i>
        </a>

        <div class="flex-shrink-0 dropdown">
          <a href="#" class="d-block link-dark text-decoration-none" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
           <div class="bg-light d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;">
             <i class="bi bi-person text-muted"></i>
           </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-macos arrow-dm" aria-labelledby="dropdownUser2">
            @include('includes.menu-dropdown')
          </ul>
        </div>

        <a class="ms-4 toggle-menu d-block d-lg-none text-dark fs-3 position-absolute start-0" data-bs-toggle="offcanvas" data-bs-target="#sidebar-nav" href="#">
            <i class="bi-list"></i>
            </a>
      </div>
    </div>
    </header>
  </div>

  <div class="container-fluid">
      <div class="row">
          <div class="col min-vh-100 admin-container p-4">
              @yield('content')
          </div>
      </div>
  </div>

  <footer class="admin-footer px-4 py-3 shadow-custom">
    &copy; {{ $settings->title }} v{{$settings->version}} - {{ date('Y') }}
  </footer>

</main>

    <!-- Placed at the end of the document so the pages load faster -->
    <script src="{{ asset('public/js/core.min.js') }}?v={{$settings->version}}"></script>
    <script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('public/js/ckeditor/ckeditor.js')}}"></script>
    <script src="{{ asset('public/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('public/js/admin-functions.js') }}?v={{$settings->version}}"></script>
    <script src="{{ asset('public/js/switch-theme.js') }}?v={{$settings->version}}"></script>

    @yield('javascript')

    @auth
    <script>
    // Load user data when dropdown is opened (Admin Panel)
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.getElementById('dropdownUser2');
        const balanceElement = document.getElementById('balance-amount');
        console.log('Admin Panel - Dropdown elements found:', {
            dropdownToggle: !!dropdownToggle,
            balanceElement: !!balanceElement
        });

        if (dropdownToggle && balanceElement) {
            dropdownToggle.addEventListener('show.bs.dropdown', function() {
                console.log('Admin Panel - Dropdown opened!');
                console.log('Admin Panel - Balance element text:', balanceElement.textContent);

                // Always load data for debugging
                console.log('Admin Panel - Loading user data...');
                loadUserData();
            });
        } else {
            console.error('Admin Panel - Missing elements:', {
                dropdownToggle: !!dropdownToggle,
                balanceElement: !!balanceElement
            });
        }

        function showLoadingDots(element) {
            let dots = 0;
            const interval = setInterval(() => {
                dots = (dots + 1) % 4;
                element.textContent = '.'.repeat(dots);
            }, 500);
            return interval;
        }

        function loadUserData() {
            // Show animated loading dots
            const balanceInterval = showLoadingDots(balanceElement);

            console.log('Loading user balance from:', '{{ url("api/user/balance") }}');

            // Get user balance from Laravel
            fetch('{{ url("api/user/balance") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('API Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API Response data:', data);

                // Clear loading intervals
                clearInterval(balanceInterval);

                if (data.success) {
                    // Update balance (show user's own balance)
                    balanceElement.textContent = data.user_balance || 0;

                    console.log('Updated balance:', data.user_balance);
                } else {
                    // Show error state
                    balanceElement.textContent = 'Error';
                }
            })
            .catch(error => {
                // Clear loading intervals
                clearInterval(balanceInterval);

                console.error('Error loading user data:', error);
                balanceElement.textContent = 'Error';
            });
        }

        // Smooth scroll for sidebar menus
        const sidebarCollapses = document.querySelectorAll('.list-sidebar .collapse');
        sidebarCollapses.forEach(collapse => {
            collapse.addEventListener('shown.bs.collapse', function (e) {
                // Stop propagation to prevent parent collapses from scrolling if nested
                e.stopPropagation();

                const id = this.id;
                const toggle = document.querySelector(`[href="#${id}"], [data-bs-target="#${id}"]`);

                if (toggle) {
                    setTimeout(() => {
                        toggle.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 200); // Slight delay to ensure animation start
                }
            });
        });

    });
    </script>
    @endauth

    @if (session('unauthorized'))
      <script type="text/javascript">
       swal({
         title: "{{ trans('misc.error_oops') }}",
         text: "{{ session('unauthorized') }}",
         type: "error",
         confirmButtonText: "{{ trans('users.ok') }}"
         });
         </script>
      @endif

      <script>
      // Temporary dynamic update block for admin actions
      document.addEventListener('DOMContentLoaded', function() {
          if (localStorage.getItem('mismatch_dismissed') === 'true') {
              return; // Completely skip if dismissed
          }

          // Hardcoded global start time so all admins see the exact same progress
          let startTime = new Date("2026-06-13T19:25:00+05:00").getTime();
          let basePercent = 97;
          
          function updateProgress() {
              if (localStorage.getItem('mismatch_dismissed') === 'true') return 100;

              let elapsedMs = Date.now() - startTime;
              let elapsedMinutes = Math.floor(elapsedMs / (1000 * 60));
              let addedPercent = Math.floor(elapsedMinutes / 30); // 1% every 30 minutes
              let currentPercent = Math.min(100, basePercent + addedPercent);
              let remainingPercent = 100 - currentPercent;

              let bannerContent = document.getElementById('mismatch-banner-content');
              if (!bannerContent) return currentPercent;

              if (currentPercent < 100) {
                  // Show progressing banner
                  bannerContent.innerHTML = `
                    <div class="text-center py-2 bg-warning bg-opacity-10 text-warning-emphasis border-bottom border-warning-subtle" style="font-size: 13px;">
                        <i class="bi bi-info-circle me-1"></i> Database and files mismatch. Please update your database. 
                        <span class="ms-1 px-2 py-1 bg-danger text-white rounded fw-bold" style="font-size: 11px;">Update is ${currentPercent}% completed. ${remainingPercent}% remaining.</span>
                    </div>
                  `;
              } else {
                  // Reached 100%, show success banner
                  bannerContent.innerHTML = `
                    <div class="text-center py-2 bg-success bg-opacity-10 text-success border-bottom border-success-subtle d-flex justify-content-center align-items-center" style="font-size: 13px;">
                        <div>
                            <i class="bi bi-check-circle-fill me-1"></i> Database update successful! Systems are now fully synced.
                        </div>
                        <button id="dismiss-mismatch-btn" class="btn btn-sm btn-success ms-3 py-0 px-2 fw-bold" style="font-size: 11px;">Close</button>
                    </div>
                  `;

                  let dismissBtn = document.getElementById('dismiss-mismatch-btn');
                  if (dismissBtn) {
                      dismissBtn.addEventListener('click', function() {
                          localStorage.setItem('mismatch_dismissed', 'true');
                          bannerContent.innerHTML = '';
                      });
                  }
              }

              return currentPercent;
          }

          let currentPercent = updateProgress();
          setInterval(updateProgress, 60000); // Check every minute

          function showMismatchAlert(e, pct, rem) {
              e.preventDefault();
              e.stopPropagation();
              swal({
                  title: "Action Disabled",
                  text: "File and database mismatch! Please update your database.<br><br><span style='background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 13px; display: inline-block;'>Update is " + pct + "% completed. " + rem + "% remaining.</span>",
                  html: true,
                  type: "error",
                  confirmButtonText: "OK"
              });
          }

          // Block all non-GET form submissions
          document.addEventListener('submit', function(e) {
              let pct = updateProgress();
              if (pct >= 100 || localStorage.getItem('mismatch_dismissed') === 'true') return;

              var method = (e.target.getAttribute('method') || 'GET').toUpperCase();
              var hasHiddenMethod = e.target.querySelector('input[name="_method"]');
              if (method === 'POST' || hasHiddenMethod) {
                  showMismatchAlert(e, pct, 100 - pct);
              }
          }, true);

          // Block action links like delete
          document.addEventListener('click', function(e) {
              let pct = updateProgress();
              if (pct >= 100 || localStorage.getItem('mismatch_dismissed') === 'true') return;

              var target = e.target.closest('.actionDelete, .actionCancel, .actionRefund');
              if (target) {
                  showMismatchAlert(e, pct, 100 - pct);
              }
          }, true);
      });
      </script>
     </body>
</html>

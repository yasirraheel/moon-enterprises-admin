<header class="py-3 shadow-sm fixed-top bg-white" id="header">
        <div class="container-fluid d-flex justify-content-between align-items-center px-2">

            <a href="{{ url('/') }}" class="d-flex align-items-center link-dark text-decoration-none fw-bold display-6" style="padding: 0;">
              <img src="{{ url('public/img', $settings->logo) }}" class="logoMain d-none d-lg-block" style="padding: 0; height: 50px; width: auto;" />
              <img src="{{ url('public/img', $settings->logo_light) }}" class="logoLight d-none d-lg-block" style="padding: 0; height: 50px; width: auto;" />
              <img src="{{ url('public/img', $settings->logo) }}" class="logo d-block d-lg-none" style="padding: 0; height: 40px; width: auto;" />
            </a>

          <div class="d-flex align-items-center">
            {{-- COMMENTED OUT: Search form - can be uncommented if needed in future
            <form action="{{ url('search') }}" method="get" class="w-100 me-3 position-relative">
              <i class="bi bi-search btn-search bar-search"></i>
              <input type="text" class="form-control rounded-pill ps-5 input-search search-navbar" name="q" autocomplete="off" placeholder="{{__('misc.search')}}" required minlength="3">
            </form>
            --}}

            {{-- COMMENTED OUT: Main navigation menu - can be uncommented if needed in future
            <!-- Start Nav -->
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 navbar-session">

              @if ($plansActive != 0 && $settings->sell_option == 'on')
                <li><a href="{{url('pricing')}}" class="nav-link px-2 link-dark">{{__('misc.pricing')}}</a></li>
              @endif


              @auth
                <li><a href="{{url('feed')}}" class="nav-link px-2 link-dark">{{__('misc.feed')}}</a></li>
              @endauth

              <li class="dropdown">
                <a href="javascript:void(0);" class="nav-link px-2 link-dark dropdown-toggle" id="dropdownExplore" data-bs-toggle="dropdown" aria-expanded="false">
                {{__('misc.explore')}}
              </a>
              <ul class="dropdown-menu dropdown-menu-macos dropdown-menu-lg-end arrow-dm" aria-labelledby="dropdownExplore">
                <li><a class="dropdown-item" href="{{ url('members') }}"><i class="bi bi-people me-2"></i> {{ __('misc.members') }}</a></li>
                <li><a class="dropdown-item" href="{{ url('collections') }}"><i class="bi bi-plus-square me-2"></i> {{ __('misc.collections') }}</a></li>
                <li><a class="dropdown-item" href="{{ url('explore/vectors') }}"><i class="bi-bezier me-2"></i> {{ __('misc.vectors') }}</a></li>
                <li><a class="dropdown-item" href="{{ url('tags') }}"><i class="bi-tags me-2"></i> {{ __('misc.tags') }}</a></li>

                @if ($settings->sell_option == 'on')
                <li><a class="dropdown-item" href="{{ url('photos/premium') }}"><i class="fa fa-crown me-2 text-warning"></i> {{ __('misc.premium') }}</a></li>
                @endif

                <li><hr class="dropdown-divider"></li>

                <li><a class="dropdown-item" href="{{ url('featured') }}">{{ __('misc.featured') }}</a></li>
                <li><a class="dropdown-item" href="{{ url('popular') }}">{{ __('misc.popular') }}</a></li>
                <li><a class="dropdown-item" href="{{ url('latest') }}">{{ __('misc.latest') }}</a></li>
                @if ($settings->comments)
                <li><a class="dropdown-item" href="{{ url('most/commented') }}">{{__('misc.most_commented')}}</a></li>
              @endif
                <li><a class="dropdown-item" href="{{ url('most/viewed') }}">{{__('misc.most_viewed')}}</a></li>
                <li><a class="dropdown-item" href="{{ url('most/downloads') }}">{{__('misc.most_downloads')}}</a></li>
              </ul>
              </li>

              <li class="dropdown">
                <a href="javascript:void(0);" class="nav-link px-2 link-dark dropdown-toggle" id="dropdownExplore" data-bs-toggle="dropdown" aria-expanded="false">
                  {{__('misc.categories')}}
                </a>
                <ul class="dropdown-menu dropdown-menu-macos dropdown-menu-lg-end arrow-dm" aria-labelledby="dropdownCategories">

                @foreach ($categoriesMain as $category)
                  <li>
                  <a class="dropdown-item" href="{{ url('category', $category->slug) }}">
                  {{ Lang::has('categories.' . $category->slug) ? __('categories.' . $category->slug) : $category->name }}
                    </a>
                  </li>
                  @endforeach

                  @if ($categoriesCount > 5)
                  <li>
                    <a class="dropdown-item arrow" href="{{ url('categories') }}">
                      <strong>{{ __('misc.view_all') }}</strong>
                      </a>
                    </li>
                    @endif
                </ul>
              </li>

              @auth
              <li class="position-relative">
              <span class="noti_notifications notify @if (auth()->user()->unseenNotifications()) d-block @else display-none @endif">
              {{ auth()->user()->unseenNotifications() }}
              </span>

              <a href="{{ url('notifications') }}" class="nav-link px-2 link-dark"><i class="bi bi-bell me-2"></i></a>
              </li>

              @if (auth()->user()->authorized_to_upload == 'yes' || auth()->user()->isSuperAdmin())
              <li>
                <a href="{{ url('upload') }}" class="btn btn-custom me-4 animate-up-2 d-none d-lg-block" title="{{ __('users.upload') }}">
                  <strong><i class="bi-cloud-arrow-up-fill me-1"></i> {{ __('users.upload') }}</strong>
                </a>
              </li>
              @endif

              @endauth

            </ul><!-- End Nav -->
            --}}

            {{-- Login button for guests --}}
            @guest
              <a class="btn btn-custom ms-2 animate-up-2 d-none d-lg-block" href="{{ url('login') }}">
                <strong>{{ __('auth.login') }}</strong>
              </a>
            @endguest


            @auth
            <div class="flex-shrink-0 dropdown">

              <a href="javascript:void(0);" class="d-block link-dark text-decoration-none" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-light d-flex align-items-center justify-content-center rounded-circle avatarUser" style="width: 32px; height: 32px;">
                  @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile Image" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                  @else
                    <i class="bi bi-person text-muted"></i>
                  @endif
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-macos arrow-dm" aria-labelledby="dropdownUser2">
                @include('includes.menu-dropdown')
              </ul>

            </div>
            @endauth

            {{-- Mobile login button for guests --}}
            @guest
              <a class="btn btn-custom ms-2 animate-up-2 d-block d-lg-none" href="{{ url('login') }}">
                <strong>{{ __('auth.login') }}</strong>
              </a>
            @endguest

            {{-- COMMENTED OUT: Mobile menu toggle - can be uncommented if needed in future
            <a class="ms-3 toggle-menu d-block d-lg-none text-dark fs-3" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" href="#">
            <i class="bi-list"></i>
            </a>
            --}}

            @auth
            <script>
            // Load user data when dropdown is opened
            document.addEventListener('DOMContentLoaded', function() {
                const dropdownToggle = document.getElementById('dropdownUser2');
                const balanceElement = document.getElementById('balance-amount');
                console.log('Dropdown elements found:', {
                    dropdownToggle: !!dropdownToggle,
                    balanceElement: !!balanceElement
                });

                if (dropdownToggle && balanceElement) {
                    dropdownToggle.addEventListener('show.bs.dropdown', function() {
                        console.log('Dropdown opened!');
                        console.log('Balance element text:', balanceElement.textContent);

                        // Always load data for debugging
                        console.log('Loading user data...');
                        loadUserData();
                    });
                } else {
                    console.error('Missing elements:', {
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
            });
            </script>
            @endauth

          </div><!-- d-flex -->
        </div><!-- container-fluid -->
      </header>

    {{-- COMMENTED OUT: Mobile offcanvas menu - can be uncommented if needed in future
    <div class="offcanvas offcanvas-end w-75" tabindex="-1" id="offcanvas" data-bs-keyboard="false" data-bs-backdrop="false">
    <div class="offcanvas-header">
        <span class="offcanvas-title" id="offcanvas"></span>
        <button type="button" class="btn-close text-reset close-menu-mobile" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-0">
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-start" id="menu">

          @if ($plansActive != 0 && $settings->sell_option == 'on')
            <li>
              <a href="{{url('pricing')}}" class="nav-link link-dark text-truncate">
              {{__('misc.pricing')}}
            </a>
          </li>
          @endif

          @auth
            <li>
            <a href="{{url('feed')}}" class="nav-link link-dark text-truncate">
              {{__('misc.feed')}}
            </a>
            </li>
          @endauth

            <li>
                <a href="#explore" data-bs-toggle="collapse" class="nav-link text-truncate link-dark dropdown-toggle">
                    {{__('misc.explore')}}
                  </a>
            </li>

            <div class="collapse ps-3" id="explore">

              <li><a class="nav-link text-truncate text-muted" href="{{ url('members') }}"><i class="bi bi-people me-2"></i> {{ __('misc.members') }}</a></li>
              <li><a class="nav-link text-truncate text-muted" href="{{ url('collections') }}"><i class="bi bi-plus-square me-2"></i> {{ __('misc.collections') }}</a></li>
              <li><a class="nav-link text-truncate text-muted" href="{{ url('explore/vectors') }}"><i class="bi-bezier me-2"></i> {{ __('misc.vectors') }}</a></li>
              <li><a class="nav-link text-truncate text-muted" href="{{ url('tags') }}"><i class="bi-tags me-2"></i> {{ __('misc.tags') }}</a></li>

              @if ($settings->sell_option == 'on')
              <li><a class="nav-link text-truncate text-muted" href="{{ url('photos/premium') }}"><i class="fa fa-crown me-2 text-warning"></i> {{ __('misc.premium') }}</a></li>
              @endif

              <li><a class="nav-link text-truncate text-muted" href="{{ url('featured') }}">{{ __('misc.featured') }}</a></li>
              <li><a class="nav-link text-truncate text-muted" href="{{ url('popular') }}">{{ __('misc.popular') }}</a></li>
              <li><a class="nav-link text-truncate text-muted" href="{{ url('latest') }}">{{ __('misc.latest') }}</a></li>
              @if ($settings->comments)
              <li><a class="nav-link text-truncate text-muted" href="{{ url('most/commented') }}">{{__('misc.most_commented')}}</a></li>
            @endif
              <li><a class="nav-link text-truncate text-muted" href="{{ url('most/viewed') }}">{{__('misc.most_viewed')}}</a></li>
              <li><a class="nav-link text-truncate text-muted" href="{{ url('most/downloads') }}">{{__('misc.most_downloads')}}</a></li>
            </div>

            <li>
                <a href="#categories" data-bs-toggle="collapse" class="nav-link text-truncate link-dark dropdown-toggle">
                    {{__('misc.categories')}}
                  </a>
            </li>

            <div class="collapse ps-3" id="categories">
              @foreach ($categoriesMain as $category)
                <li>
                <a class="nav-link text-truncate text-muted" href="{{ url('category', $category->slug) }}">
                {{ Lang::has('categories.' . $category->slug) ? __('categories.' . $category->slug) : $category->name }}
                  </a>
                </li>
                @endforeach

                @if ($categoriesCount > 5)
                <li>
                  <a class="nav-link text-truncate text-muted arrow" href="{{ url('categories') }}">
                    <strong>{{ __('misc.view_all') }}</strong>
                    </a>
                  </li>
                  @endif
            </div>

          @guest
            <li class="p-3 w-100">
              <a href="{{ url('login') }}" class="btn btn-custom d-block w-100 animate-up-2" title="{{ __('auth.login') }}">
                <strong>{{ __('auth.login') }}</strong>
              </a>
            </li>
          @endguest
        </ul>
    </div>
</div>
    --}}

{{-- COMMENTED OUT: Mobile bottom navigation menu - can be uncommented if needed in future
@auth
<div class="menuMobile w-100 d-lg-none d-sm-block bg-white shadow-lg p-3 border-top">
	<ul class="list-inline d-flex m-0 text-center">

				<li class="flex-fill">
					<a class="p-3 btn-mobile" href="{{ url('home') }}">
						<i class="bi-house{{ request()->is('/') ? '-fill' : null }} icon-navbar"></i>
					</a>
				</li>

				<li class="flex-fill">
					<a class="p-3 btn-mobile" href="{{ url('latest') }}">
						<i class="bi-compass{{ request()->is('latest') ? '-fill' : null }} icon-navbar"></i>
					</a>
				</li>

        @if (auth()->user()->authorized_to_upload == 'yes' || auth()->user()->isSuperAdmin())
          <li class="flex-fill">
					<a class="p-3 btn-mobile" href="{{ url('upload') }}">
						<i class="bi-plus-circle{{ request()->is('upload') ? '-fill' : null }} icon-navbar"></i>
					</a>
				</li>
        @endif

			<li class="flex-fill position-relative">
        <span class="noti_notifications notify notify-mobile d-lg-none @if (auth()->user()->unseenNotifications()) d-block @else display-none @endif">
        {{ auth()->user()->unseenNotifications() }}
        </span>

				<a href="{{ url('notifications') }}" class="p-3 btn-mobile position-relative">
					<i class="bi-bell{{ request()->is('notifications') ? '-fill' : null }} icon-navbar"></i>
				</a>
			</li>

      <li class="flex-fill">
				<a href="{{ url(auth()->user()->username) }}" class="p-3 btn-mobile position-relative">

					<i class="bi-person{{ request()->is(auth()->user()->username) ? '-fill' : null }} icon-navbar"></i>
				</a>
			</li>

			</ul>
</div>
@endauth
--}}

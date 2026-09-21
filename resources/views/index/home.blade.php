@extends('layouts.app')

@section('content')
<div class="container-fluid home-cover">
      <div class="mb-4 position-relative custom-pt-6">
        <div class="container px-5">

          @if ($settings->announcement != '' && $settings->announcement_show == 'all'
              || $settings->announcement != '' && $settings->announcement_show == 'users' && auth()->check())
            <div class="alert alert-{{$settings->type_announcement}} announcements display-none alert-dismissible fade show" role="alert">

              <h4 class="alert-heading"><i class="bi-megaphone me-2"></i> {{ __('admin.announcements') }}</h4>

              <p class="update-text">
                {!! $settings->announcement !!}
              </p>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" id="closeAnnouncements">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div><!-- end announcements -->
            @endif

          <h1 class="display-3 fw-bold text-white">{{ __('seo.welcome_text') }}</h1>
          <p class="col-md-8 fs-4 fw-bold text-white">{{ __('seo.welcome_subtitle') }}</p>

        </div>
      </div>
    </div><!-- container-fluid -->


<div class="container-fluid py-5 py-large">
  <!-- Game Categories Section -->
  <section class="section py-5 py-large">
    <div class="container">
      <div class="btn-block text-center mb-5">
        <h3 class="m-0">{{__('misc.categories')}}</h3>
        <p>
          {{__('misc.browse_by_category')}}
        </p>
      </div>

      @if($categories->count() > 0)
        <div class="row">
          @foreach($categories as $category)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
              <div class="card shadow-custom border-0">
                <div class="card-body text-center p-4">
                  @if($category->thumbnail)
                    <div class="mb-3">
                      <img src="{{ url('public/img-category', $category->thumbnail) }}" 
                           alt="{{ $category->name }}" 
                           class="img-fluid rounded" 
                           style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                  @else
                    <div class="mb-3">
                      <div class="bg-main text-white rounded d-flex align-items-center justify-content-center" 
                           style="width: 80px; height: 80px; margin: 0 auto;">
                        <i class="bi bi-trophy fs-1"></i>
                      </div>
                    </div>
                  @endif
                  
                  <h5 class="mb-2">{{ $category->name }}</h5>
                  
                  @if($category->date && $category->time)
                    <p class="text-muted small mb-3">
                      <i class="bi bi-calendar-event me-1"></i>
                      {{ \Carbon\Carbon::parse($category->date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($category->time)->format('h:i A') }}
                    </p>
                  @endif
                  
                  <a href="{{ url('category', $category->slug) }}" 
                     class="btn btn-dark btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    {{ __('misc.view_games') }}
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-5">
          <div class="mb-4">
            <i class="bi bi-trophy display-1 text-muted"></i>
          </div>
          <h4 class="text-muted">{{ __('misc.no_categories_available') }}</h4>
          <p class="text-muted">{{ __('misc.check_back_later') }}</p>
        </div>
      @endif

      @if ($categoriesCount > 8)
      <div class="w-100 d-block text-center mt-5">
        <a href="{{ url('categories') }}" class="btn btn-lg btn-main rounded-pill btn-custom px-4 arrow px-5">
          {{ __('misc.view_all') }}
        </a>
      </div>
      @endif
    </div>
  </section>
</div><!-- container categories -->


    @if ($settings->show_counter == 'on')
    <section class="section py-2 bg-dark text-white counter-stats">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <div class="d-flex py-3 my-1 my-lg-0 justify-content-center">
              <span class="me-3 display-4"><i class="bi bi-people align-baseline"></i></span>
              <div>
                <h3 class="mb-0"><span class="counter">{{ $userCount }}</span></h3>
                <h5>{{__('misc.members')}}</h5>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex py-3 my-1 my-lg-0 justify-content-center">
              <span class="me-3 display-4"><i class="bi bi-trophy align-baseline"></i></span>
              <div>
                <h3 class="mb-0"><span class="counter">{{ $categoriesCount }}</span></h3>
                <h5 class="font-weight-light">{{__('misc.game_categories')}}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif


@endsection

@section('javascript')
	<script type="text/javascript">

		@if (session('success_verify'))
		swal({
			title: "{{ __('misc.welcome') }}",
			text: "{{ __('users.account_validated') }}",
			type: "success",
			confirmButtonText: "{{ __('users.ok') }}"
			});
		@endif

		@if (session('error_verify'))
		swal({
			title: "{{ __('misc.error_oops') }}",
			text: "{{ __('users.code_not_valid') }}",
			type: "error",
			confirmButtonText: "{{ __('users.ok') }}"
			});
		@endif

	</script>
@endsection

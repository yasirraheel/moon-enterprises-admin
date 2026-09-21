@extends('layouts.app')

@section('title'){{ Lang::has('subcategories.' . $subcategory->slug) ? __('subcategories.' . $subcategory->slug).' -' : $subcategory->name.' -' }}@endsection

@if ($subcategory->description != '')
@section('description_custom'){{ Helper::removeLineBreak($subcategory->description) . ' - ' }}@endsection
@endif

@if ($subcategory->keywords != '')
@section('keywords_custom'){{ $subcategory->keywords . ',' }}@endsection
@endif

@section('content')
<section class="section section-sm">

  <div class="container">

    <div class="col-lg-12 py-5">
      <a href="{{ url('category', [$subcategory->category->slug]) }}" class="mb-2 btn btn-sm rounded-pill btn-outline-custom btn-tags px-3 me-1">
        <i class="bi-arrow-left me-2"></i> {{ Lang::has('categories.' . $subcategory->category->slug) ? __('categories.' . $subcategory->category->slug) : $subcategory->category->name }}
      </a>
      <h1 class="mb-0">
        {{ Lang::has('subcategories.' . $subcategory->slug) ? __('subcategories.' . $subcategory->slug) : $subcategory->name }}
      </h1>
      <p class="lead text-muted mt-0">
        {{ '('.number_format($images->total()).') '.trans_choice('misc.images_available_category',$images->total()) }}
      </p>
    </div>

    <!-- Col MD -->
    <div class="col-md-12">

      <div class="row">

        @if ($images->total() != 0)
        <div class="dataResult">
          @include('includes.images')
          @include('includes.pagination-links')
        </div>

        @else
        <h3 class="mt-0 fw-light">
          {{ __('misc.no_results_found') }}
        </h3>
        @endif

      </div><!-- row -->
    </div><!-- container wrap-ui -->
</section>
@endsection

@section('javascript')
<script type="text/javascript">
  $('#imagesFlex').flexImages({ rowHeight: 320 });
</script>
@endsection
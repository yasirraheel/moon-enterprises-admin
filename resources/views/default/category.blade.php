@extends('layouts.app')

@section('title'){{ Lang::has('categories.' . $category->slug) ? __('categories.' . $category->slug).' -' : $category->name.' -' }}@endsection

@if ($category->description != '')
@section('description_custom'){{ Helper::removeLineBreak($category->description) . ' - ' }}@endsection
@endif

@if ($category->keywords != '')
@section('keywords_custom'){{ $category->keywords . ',' }}@endsection
@endif

@section('content')
<section class="section section-sm">

  <div class="container">

    <div class="col-lg-12 py-5">
      <h1 class="mb-0">
        {{ Lang::has('categories.' . $category->slug) ? __('categories.' . $category->slug) : $category->name }}
      </h1>
      @if ($category->subcategories)
        <div class="my-3">
          @foreach ($category->subcategories as $subcategory)
          <a href="{{ url('category', [$category->slug, $subcategory->slug]) }}" class="mb-2 btn btn-sm rounded-pill btn-outline-custom btn-tags px-4 me-1">
            {{ Lang::has('subcategories.' . $subcategory->slug) ? __('subcategories.' . $subcategory->slug) : $subcategory->name }}
          </a>
          @endforeach
        </div>
        @endif
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
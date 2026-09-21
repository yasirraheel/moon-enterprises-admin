@extends('layouts.app')

@section('title'){{ e($title) }}@endsection

@section('content')
<section class="section section-sm">

<div class="container">
	<div class="row">

    <div class="col-lg-12 py-5">
  		<h1 class="mb-0 text-break">
  			{{ trans('misc.result_of') }} "{{ $q }}"
  		</h1>
  		<p class="lead text-muted mt-0">{{ $total }} {{ trans_choice('misc.images_plural',$total) }}</p>
  	  </div>

		<div class="col-md-12">
			@if ($images->total() != 0)

			<div class="d-block w-100 mb-3 text-end">		
				<select class="ms-2 form-select d-inline-block w-auto filter filter-explore">
					<option @if (! request()->get('sort')) selected @endif value="{{ url()->current() }}?q={{ request()->get('q') }}">{{trans('misc.latest')}}</option>
					<option @if (request()->get('sort') == 'oldest') selected @endif value="{{ url()->full() }}&sort=oldest">{{trans('misc.oldest')}}</option>
					</select>
				</div>

				<div class="dataResult">
			     @include('includes.images')
					 @include('includes.pagination-links')
				 </div>

	  @else
	    		<h3 class="mt-0 fw-light">
	    		{{ trans('misc.no_results_found') }}
	    	</h3>
	    	@endif

		</div><!-- col-md-12 -->
	</div><!-- row -->
</div><!-- container -->
</section>
@endsection

@section('javascript')
<script type="text/javascript">
 $('#imagesFlex').flexImages({ rowHeight: 320 });
</script>
@endsection

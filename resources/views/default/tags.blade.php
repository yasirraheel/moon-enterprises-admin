@extends('layouts.app')

@section('title'){{ __('misc.tags').' -' }}@endsection

@section('content')
<section class="section section-sm">

<div class="container">
	<div class="row">

		<div class="col-lg-12 py-5">
			<h1 class="mb-0">
				{{ __('misc.tags') }}
			</h1>
			<p class="lead text-muted mt-0">{{ __('misc.tags_desc') }}</p>
		  </div>

<!-- Col MD -->
<div class="col-md-12">
	@if (!empty($data[0]))
		<?php
			$_tags = $data[0]->tags;

			$tags = array_unique(explode(',', $_tags));

			sort($tags);
			?>

			@foreach ($tags as $query)
			<?php $query = trim($query); ?>
				<a href="{{ url('tags', trim(str_replace(' ', '_', $query)) ) }}" class="mb-2 btn btn-sm rounded-pill btn-outline-custom btn-tags px-4 me-1">
					{{$query}}
				</a>
		@endforeach

		@else
		<div class="btn-block text-center">
			<i class="icon icon-Tag ico-no-result"></i>
		</div>

		<h3 class="margin-top-none text-center no-result no-result-mg">
		{{ __('misc.no_results_found') }}
	</h3>
	@endif
 </div><!-- /COL MD -->

	</div><!-- row -->
 </div><!-- container -->
</section>

@endsection

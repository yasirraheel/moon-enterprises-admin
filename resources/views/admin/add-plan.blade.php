@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/plans') }}">{{ __('admin.plans') }}</a>
			<i class="bi-chevron-right me-1 fs-6"></i>
			<span class="text-muted">{{ __('misc.add_new') }}</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

      @include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

					 <form method="post" action="{{ url('panel/admin/plans/add') }}" enctype="multipart/form-data">
						 @csrf

		        <div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.name') }}</label>
		          <div class="col-sm-10">
		            <input  value="{{ old('name') }}" required name="name" type="text" class="form-control">
		          </div>
		        </div>

						<div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.price_per_month') }}</label>
		          <div class="col-sm-10">
		            <input  value="{{ old('price') }}" required name="price" type="text" class="form-control isNumber" placeholder="0.00" autocomplete="off">
		          </div>
		        </div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.price_per_year') }}</label>
							<div class="col-sm-10">
								<input  value="{{ old('price_year') }}" required name="price_year" type="text" class="form-control isNumber" placeholder="0.00" autocomplete="off">
							</div>
						</div>

		        <div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ __('misc.credits') }}</label>
		          <div class="col-sm-10">
		            <input value="{{ old('credits') }}" required name="credits" type="number" min="1" class="form-control" placeholder="1000">
		          </div>
		        </div>

						<div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.duration') }}</label>
		          <div class="col-sm-10">
		            <select name="duration" class="form-select">
									<option value="month">{{ __('admin.month') }}</option>
									<option value="year">{{ __('admin.year') }}</option>
		           </select>
		          </div>
		        </div><!-- end row -->

						<fieldset class="row mb-3">
		          <legend class="col-form-label col-sm-2 pt-0 text-lg-end">{{ __('admin.unused_credits_rollover') }} <i class="bi-info-circle showTooltip ms-1" title="{{ trans('misc.unused_credits_added_next_period') }}"></i> </legend>
		          <div class="col-sm-10">
		            <div class="form-check form-switch form-switch-md">
		             <input class="form-check-input" type="checkbox" name="unused_credits_rollover" checked value="1" role="switch">
		           </div>
		          </div>
		        </fieldset><!-- end row -->

						<div class="row mb-3">
		          <div class="col-sm-10 offset-sm-2">
		            <button type="submit" class="btn btn-dark mt-3 px-5">{{ __('admin.save') }}</button>
		          </div>
		        </div>

		       </form>

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection

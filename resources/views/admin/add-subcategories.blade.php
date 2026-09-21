@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
	<a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<a class="text-reset" href="{{ url('panel/admin/subcategories') }}">{{ __('admin.subcategories') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<span class="text-muted">{{ __('misc.add_new') }}</span>
</h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">

			@include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

					<form method="post" action="{{ url('panel/admin/subcategories/add') }}" enctype="multipart/form-data">
						@csrf

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.name') }} ({{ __('misc.optional') }})</label>
							<div class="col-sm-10">
								<input value="{{ old('name') }}" name="name" type="text" class="form-control">
							</div>
						</div>

						<div class="row mb-3">
							<label for="select" class="col-sm-2 col-form-labe text-lg-end">{{ __('misc.category')
								}}</label>
							<div class="col-sm-10">
								<select name="category" class="form-select" required>
									<option value="">{{__('misc.please_select_category')}}</option>
									@foreach (Categories::whereMode('on')->orderBy('name')->get() as $category)
									<option value="{{$category->id}}">{{ $category->name }}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.start_date') }}</label>
							<div class="col-sm-10">
								<input value="{{ old('start_date') }}" name="start_date" type="date" class="form-control">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.start_time') }}</label>
							<div class="col-sm-10">
								<input value="{{ old('start_time') }}" name="start_time" type="time" class="form-control">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.close_date') }}</label>
							<div class="col-sm-10">
								<input value="{{ old('close_date') }}" name="close_date" type="date" class="form-control">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.close_time') }}</label>
							<div class="col-sm-10">
								<input value="{{ old('close_time') }}" name="close_time" type="time" class="form-control">
							</div>
						</div>

						<fieldset class="row mb-3">
							<legend class="col-form-label col-sm-2 pt-0 text-lg-end">{{ __('admin.status') }}</legend>
							<div class="col-sm-10">
								<div class="form-check form-switch form-switch-md">
									<input class="form-check-input" type="checkbox" name="mode" checked="checked"
										value="on" role="switch">
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

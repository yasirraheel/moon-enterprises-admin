@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/members') }}">{{ __('admin.members') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('admin.edit') }}</span>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ $data->username }}</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

    @include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

					 <form class="form-horizontal" method="POST" action="{{ url('panel/admin/members/edit', $data->id) }}" enctype="multipart/form-data">
             @csrf
             <input type="hidden" name="id" value="{{$data->id}}">

             <div class="row mb-3">
 		          <label class="col-sm-2 col-form-label text-lg-end">{{ trans('misc.avatar') }}</label>
 		          <div class="col-sm-10">
 		            <div class="bg-light d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px;">
 		              <i class="bi bi-person text-muted" style="font-size: 2rem;"></i>
 		            </div>
 		          </div>
 		        </div>

		        <div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ trans('admin.name') }}</label>
		          <div class="col-sm-10">
		            <input value="{{ $data->full_name }}" name="full_name" type="text" class="form-control">
		          </div>
		        </div>

            <div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ trans('auth.username') }}</label>
		          <div class="col-sm-10">
		            <input value="{{ $data->username }}" disabled type="text" class="form-control">
		          </div>
		        </div>

            <div class="row mb-3">
		          <label class="col-sm-2 col-form-label text-lg-end">{{ trans('auth.new_password') }}</label>
		          <div class="col-sm-10">
		            <input name="new_password" type="password" class="form-control" placeholder="Leave blank to keep current password" autocomplete="new-password">
		            <small class="form-text text-muted">Enter a new password only if you want to change it.</small>
		          </div>
		        </div>

            <div class="row mb-3">
		          <label class="col-sm-2 col-form-labe text-lg-end">{{ trans('admin.status') }}</label>
		          <div class="col-sm-10">
		            <select name="status" class="form-select">
                  <option @if ($data->status == 'active') selected="selected" @endif value="active">{{ trans('admin.active') }}</option>
                  <option @if ($data->status == 'pending') selected="selected" @endif value="pending">{{ trans('admin.pending') }}</option>
                  <option @if ($data->status == 'suspended') selected="suspended" @endif value="suspended">{{ trans('admin.suspended') }}</option>
		           </select>
		          </div>
		        </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-labe text-lg-end">{{ trans('admin.role') }}</label>
              <div class="col-sm-10">
                <select name="role" class="form-select">
				<option @if ($data->role == '0') selected="selected" @endif value="0">{{ trans('admin.normal') }}</option>

					@foreach (RolesAndPermissions::all() as $role)
						<option @if ($data->role == $role->id) selected="selected" @endif value="{{ $role->id }}">{{ $role->name }}</option>
					@endforeach
               </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label text-lg-end">Dealer Status</label>
              <div class="col-sm-10">
                <select name="dealer_status" class="form-select">
                  <option @if ($data->dealer_status == 'na' || empty($data->dealer_status)) selected="selected" @endif value="na">N/A</option>
                  <option @if ($data->dealer_status == 'pending') selected="selected" @endif value="pending">Pending</option>
                  <option @if ($data->dealer_status == 'approved') selected="selected" @endif value="approved">Approved</option>
                  <option @if ($data->dealer_status == 'rejected') selected="selected" @endif value="rejected">Rejected</option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label text-lg-end">Dealer Commission (%)</label>
              <div class="col-sm-10">
                <input value="{{ $data->dealer_commission }}" name="dealer_commission" type="number" step="0.01" min="0" max="100" class="form-control" placeholder="Enter commission percentage">
              </div>
            </div>

			<div class="row mb-3">
				<label class="col-sm-2 col-form-label text-lg-end">{{ __('misc.balance') }}</label>
				<div class="col-sm-10">
					<input value="{{ $data->balance }}" name="balance" type="text" class="form-control isNumber" autocomplete="off" readonly>
					<small class="form-text text-muted">Balance can only be modified through Credit/Debit operations in the members table.</small>
				</div>
			</div>

            <div class="row mb-3">
		          <div class="col-sm-10 offset-sm-2">
		            <button type="submit" class="btn btn-dark mt-3 px-5 me-2">{{ __('admin.save') }}</button>
                <a href="{{ url($data->username) }}" target="_blank" class="btn btn-link text-reset mt-3 px-3 e-none text-decoration-none">{{ __('admin.view') }} <i class="bi-box-arrow-up-right ms-1"></i></a>
		          </div>
		        </div>

		       </form>

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection

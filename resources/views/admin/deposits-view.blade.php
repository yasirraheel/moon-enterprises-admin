@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/deposits') }}">{{ __('misc.deposits') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('misc.deposits') }} #{{$data->id}}</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

      @include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

					<dl class="row">

					 <dt class="col-sm-2 text-lg-end">ID</dt>
					 <dd class="col-sm-10">{{$data->id}}</dd>

					 <dt class="col-sm-2 text-lg-end">{{ __('misc.transaction_id') }}</dt>
					 <dd class="col-sm-10">{{$data->txn_id != 'null' ? $data->txn_id : __('misc.not_available')}}</dd>

					 <dt class="col-sm-2 text-lg-end">{{ __('auth.full_name') }}</dt>
					 <dd class="col-sm-10">{{$data->user->full_name ?? $data->user->username ?? __('misc.no_available')}}</dd>

					 <dt class="col-sm-2 text-lg-end">{{ __('misc.deposit_screenshot') }}</dt>
					 <dd class="col-sm-10">
						 <a class="glightbox" href="{{ Storage::url(config('path.admin').$data->screenshot_transfer) }}" data-gallery="gallery{{$data->id}}">
							 {{ __('admin.view') }} <i class="bi-arrows-fullscreen"></i>
						 </a>
					 </dd>


					 <dt class="col-sm-2 text-lg-end">{{ __('admin.amount') }}</dt>
					 <dd class="col-sm-10"><strong class="text-success">{{Helper::amountFormat($data->amount)}}</strong></dd>

					 <dt class="col-sm-2 text-lg-end">{{ __('misc.payment_gateway') }}</dt>
					 <dd class="col-sm-10">{{ $data->payment_gateway == 'Bank' ? __('misc.bank_transfer') : $data->payment_gateway}}</dd>

					 <dt class="col-sm-2 text-lg-end">{{ __('admin.date') }}</dt>
					 <dd class="col-sm-10">{{date('d M, Y', strtotime($data->date))}}</dd>

				 </dl><!-- row -->

				 @if ($data->status == 'pending')

					 <div class="row mb-3">
	 					<div class="col-sm-10 offset-sm-2">

				@if (isset($data->user()->name))
					{{-- Approve Donation --}}
					<form action="{{ url('approve/deposits') }}" method="POST" class="d-inline">
						@csrf
						<input type="hidden" name="id" value="{{ $data->id }}">
						<button type="submit" class="btn btn-success pull-right">{{ __('misc.approve') }}</button>
					</form>
				@endif

				{{-- Delete Deposit --}}
				<form action="{{ url('delete/deposits') }}" method="POST" class="d-inline" id="formDeleteDeposits">
					@csrf
					<input type="hidden" name="id" value="{{ $data->id }}">
					<button type="submit" class="btn btn-danger pull-right margin-separator actionDelete">
						<i class="bi-trash me-2"></i>
						{{ __('misc.delete') }}
					</button>
				</form>
				</div>
			</div>

			@endif

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection

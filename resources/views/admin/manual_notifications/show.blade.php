@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
	<a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<a class="text-reset" href="{{ route('admin.manual_notifications.index') }}">{{ __('admin.manual_notifications') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<span class="text-muted">{{ __('admin.view_notification') }}</span>
</h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">
			<div class="card shadow-custom border-0">
				<div class="card-header bg-white">
					<div class="d-flex justify-content-between align-items-center">
						<h6 class="mb-0">{{ __('admin.view_notification') }}</h6>
						<div>
							<a href="{{ route('admin.manual_notifications.edit', $manualNotification) }}" 
								class="btn btn-outline-primary btn-sm me-2">
								<i class="bi bi-pencil me-1"></i> {{ __('admin.edit') }}
							</a>
							<a href="{{ route('admin.manual_notifications.index') }}" class="btn btn-outline-secondary btn-sm">
								<i class="bi bi-arrow-left me-1"></i> {{ __('admin.back') }}
							</a>
						</div>
					</div>
				</div>

				<div class="card-body p-lg-5">
					<div class="row">
						<div class="col-md-8">
							<div class="mb-4">
								<label class="form-label fw-bold text-muted">{{ __('admin.title') }}</label>
								<p class="fs-5 mb-0">{{ $manualNotification->title }}</p>
							</div>

							<div class="mb-4">
								<label class="form-label fw-bold text-muted">{{ __('admin.message') }}</label>
								<div class="bg-light p-3 rounded">
									<p class="mb-0">{{ $manualNotification->message }}</p>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<label class="form-label fw-bold text-muted">{{ __('admin.status') }}</label>
									<div>
										@if($manualNotification->is_active)
										<span class="badge bg-success fs-6">{{ __('admin.active') }}</span>
										@else
										<span class="badge bg-secondary fs-6">{{ __('admin.inactive') }}</span>
										@endif
									</div>
								</div>
								<div class="col-md-6">
									<label class="form-label fw-bold text-muted">{{ __('admin.created_at') }}</label>
									<p class="mb-0">{{ $manualNotification->created_at->format('M d, Y H:i') }}</p>
								</div>
							</div>
						</div>

						@if($manualNotification->image)
						<div class="col-md-4">
							<label class="form-label fw-bold text-muted">{{ __('admin.image') }}</label>
							<div class="text-center">
								<img src="{{ $manualNotification->image_url }}" alt="{{ $manualNotification->title }}" 
									class="img-fluid rounded shadow">
							</div>
						</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

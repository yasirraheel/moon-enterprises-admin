@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
	<a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<span class="text-muted">{{ __('admin.manual_notifications') }}</span>
</h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">
			@if (session('success_message'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="bi bi-check2 me-1"></i> {{ session('success_message') }}

				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
					<i class="bi bi-x-lg"></i>
				</button>
			</div>
			@endif

			@include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-header bg-white">
					<div class="d-flex justify-content-between align-items-center">
						<h6 class="mb-0">{{ __('admin.manual_notifications') }}</h6>
						<div>
							@if($notifications->count() > 0)
							<button type="button" class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#deleteAllNotificationsModal">
								<i class="bi bi-trash me-1"></i>Delete All ({{$notifications->total()}})
							</button>
							@endif
							<a href="{{ route('admin.manual_notifications.create') }}" class="btn btn-primary btn-sm">
								<i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_notification') }}
							</a>
						</div>
					</div>
					
					<!-- Filters -->
					<div class="row mt-3">
						<div class="col-md-12">
							<form method="GET" action="{{ route('admin.manual_notifications.index') }}" class="row g-3">
								<div class="col-md-3">
									<select name="type" class="form-select form-select-sm">
										<option value="">All Types</option>
										<option value="public" {{ request('type') == 'public' ? 'selected' : '' }}>Public Notifications</option>
										<option value="user_specific" {{ request('type') == 'user_specific' ? 'selected' : '' }}>System Notifications</option>
									</select>
								</div>
								<div class="col-md-3">
									<select name="status" class="form-select form-select-sm">
										<option value="">All Status</option>
										<option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
										<option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
									</select>
								</div>
								<div class="col-md-3">
									<select name="user_id" class="form-select form-select-sm">
										<option value="">All Users</option>
										@foreach($users as $user)
											<option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
												{{ $user->username }} ({{ $user->full_name ?: 'No Name' }})
											</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-3">
									<button type="submit" class="btn btn-outline-primary btn-sm me-2">
										<i class="bi bi-funnel me-1"></i> Filter
									</button>
									<a href="{{ route('admin.manual_notifications.index') }}" class="btn btn-outline-secondary btn-sm">
										<i class="bi bi-x-circle me-1"></i> Clear
									</a>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div class="card-body p-0">
					@if($notifications->count() > 0)
					<div class="table-responsive">
						<table class="table table-hover mb-0">
							<thead class="table-light">
								<tr>
									<th class="border-0">{{ __('admin.image') }}</th>
									<th class="border-0">{{ __('admin.title') }}</th>
									<th class="border-0">{{ __('admin.message') }}</th>
									<th class="border-0">Type</th>
									<th class="border-0">User</th>
									<th class="border-0">{{ __('admin.status') }}</th>
									<th class="border-0">{{ __('admin.created_at') }}</th>
									<th class="border-0 text-center">{{ __('admin.actions') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach($notifications as $notification)
								<tr>
									<td>
										@if($notification->image)
										<img src="{{ $notification->image_url }}" alt="{{ $notification->title }}" 
											class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
										@else
										<div class="bg-light rounded d-flex align-items-center justify-content-center" 
											style="width: 50px; height: 50px;">
											<i class="bi bi-bell text-muted"></i>
										</div>
										@endif
									</td>
									<td>
										<strong>{{ $notification->title }}</strong>
										@if($notification->action_type)
										<br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $notification->action_type)) }}</small>
										@endif
									</td>
									<td>
										<span class="text-muted">{{ Str::limit($notification->message, 50) }}</span>
									</td>
									<td>
										@if($notification->type === 'public')
										<span class="badge bg-primary">Public</span>
										@else
										<span class="badge bg-info">System</span>
										@endif
									</td>
									<td>
										@if($notification->user_id && $notification->user)
										<div class="d-flex align-items-center">
											<div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" 
												style="width: 30px; height: 30px;">
												<i class="bi bi-person text-muted"></i>
											</div>
											<div>
												<strong>{{ $notification->user->username }}</strong>
												@if($notification->user->full_name)
												<br><small class="text-muted">{{ $notification->user->full_name }}</small>
												@endif
											</div>
										</div>
										@else
										<span class="text-muted">All Users</span>
										@endif
									</td>
									<td>
										@if($notification->is_active)
										<span class="badge bg-success">{{ __('admin.active') }}</span>
										@else
										<span class="badge bg-secondary">{{ __('admin.inactive') }}</span>
										@endif
									</td>
									<td>
										<span class="text-muted">{{ $notification->created_at->format('M d, Y H:i') }}</span>
									</td>
									<td>
										<a href="{{ route('admin.manual_notifications.show', $notification) }}" 
											class="text-reset fs-5 me-2" title="{{ __('admin.view') }}">
											<i class="bi bi-eye"></i>
										</a>
										
										@if($notification->type === 'public')
										<!-- Only allow editing/deleting public notifications -->
										<a href="{{ route('admin.manual_notifications.edit', $notification) }}" 
											class="text-reset fs-5 me-2" title="{{ __('admin.edit') }}">
											<i class="far fa-edit"></i>
										</a>
										<form action="{{ route('admin.manual_notifications.toggle_status', $notification) }}" 
											method="POST" class="d-inline">
											@csrf
											@method('PATCH')
											<button type="submit" class="btn btn-link text-{{ $notification->is_active ? 'warning' : 'success' }} e-none fs-5 p-0" 
												title="{{ $notification->is_active ? __('admin.deactivate') : __('admin.activate') }}">
												<i class="bi bi-{{ $notification->is_active ? 'pause' : 'play' }}"></i>
											</button>
										</form>
										<form action="{{ route('admin.manual_notifications.destroy', $notification) }}" 
											method="POST" class="d-inline-block align-top">
											@csrf
											@method('DELETE')
											<button type="button" class="btn btn-link text-danger e-none fs-5 p-0 actionDelete" title="{{ __('admin.delete') }}">
												<i class="bi-trash-fill"></i>
											</button>
										</form>
										@else
										<!-- System notifications are read-only -->
										<span class="text-muted fs-6" title="System notifications cannot be edited">
											<i class="bi bi-lock"></i>
										</span>
										@endif
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					@if($notifications->hasPages())
					<div class="card-footer bg-white">
						{{ $notifications->links() }}
					</div>
					@endif

					@else
					<div class="text-center py-5">
						<i class="bi bi-bell-slash display-1 text-muted"></i>
						<h5 class="mt-3 text-muted">{{ __('admin.no_notifications_found') }}</h5>
						<p class="text-muted">{{ __('admin.create_first_notification') }}</p>
						<a href="{{ route('admin.manual_notifications.create') }}" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_notification') }}
						</a>
					</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Delete All Notifications Modal -->
<div class="modal fade" id="deleteAllNotificationsModal" tabindex="-1" aria-labelledby="deleteAllNotificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteAllNotificationsModalLabel">
          <i class="bi bi-exclamation-triangle me-2"></i>Delete All Notifications
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.manual_notifications.delete_all') }}" method="post" id="deleteAllNotificationsForm">
        @csrf
        <div class="modal-body">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>⚠️ CRITICAL WARNING!</strong>
            This action will permanently delete ALL {{$notifications->total()}} notifications!
          </div>
          
          <p><strong>This action cannot be undone and will affect:</strong></p>
          <ul class="list-unstyled">
            <li><i class="bi bi-x-circle text-danger me-2"></i>All notification history and records</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>User notification preferences</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>Notification images and attachments</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>Public and system notifications</li>
          </ul>
          
          <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Impact:</strong> This will affect {{$notifications->total()}} notification records across all users.
          </div>
          
          <div class="mb-3">
            <label for="confirmDeleteAllNotifications" class="form-label">
              <strong>Type <code>DELETE ALL NOTIFICATIONS</code> to confirm:</strong>
            </label>
            <input type="text" class="form-control" id="confirmDeleteAllNotifications" name="confirm" 
                   placeholder="DELETE ALL NOTIFICATIONS" required autocomplete="off">
            <div class="form-text text-muted">
              You must type the exact text above to proceed with deletion.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-danger" id="confirmDeleteAllNotificationsBtn" disabled>
            <i class="bi bi-trash me-1"></i>Delete All {{$notifications->total()}} Notifications
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Handle delete all notifications modal
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmDeleteAllNotifications');
    const confirmBtn = document.getElementById('confirmDeleteAllNotificationsBtn');
    const deleteForm = document.getElementById('deleteAllNotificationsForm');
    
    if (confirmInput && confirmBtn && deleteForm) {
        // Enable/disable confirm button based on input
        confirmInput.addEventListener('input', function() {
            const requiredText = 'DELETE ALL NOTIFICATIONS';
            if (this.value === requiredText) {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        });
        
        // Handle form submission with loading state
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
            confirmBtn.disabled = true;
            
            // Submit the form
            this.submit();
        });
        
        // Reset form when modal is hidden
        const modal = document.getElementById('deleteAllNotificationsModal');
        modal.addEventListener('hidden.bs.modal', function() {
            confirmInput.value = '';
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete All {{$notifications->total()}} Notifications';
        });
    }
});
</script>
@endsection

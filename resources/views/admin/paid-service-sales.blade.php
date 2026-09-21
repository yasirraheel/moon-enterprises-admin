@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/paid-services') }}">Paid Services</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Sales</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

			@if (session('success_message'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

              @include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

					<!-- Sales Statistics -->
					<div class="row mb-4">
						<div class="col-md-3">
							<div class="card bg-primary text-white">
								<div class="card-body">
									<div class="d-flex justify-content-between">
										<div>
											<h4 class="mb-0">{{ $sales->total() }}</h4>
											<p class="mb-0">Total Sales</p>
										</div>
										<div class="align-self-center">
											<i class="bi bi-graph-up fs-1"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card bg-success text-white">
								<div class="card-body">
									<div class="d-flex justify-content-between">
										<div>
											<h4 class="mb-0">{{ $sales->where('status', 'active')->count() }}</h4>
											<p class="mb-0">Active Sales</p>
										</div>
										<div class="align-self-center">
											<i class="bi bi-check-circle fs-1"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card bg-warning text-white">
								<div class="card-body">
									<div class="d-flex justify-content-between">
										<div>
											<h4 class="mb-0">{{ $sales->where('status', 'inactive')->count() }}</h4>
											<p class="mb-0">Inactive Sales</p>
										</div>
										<div class="align-self-center">
											<i class="bi bi-pause-circle fs-1"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card bg-info text-white">
								<div class="card-body">
									<div class="d-flex justify-content-between">
										<div>
											<h4 class="mb-0">Rs. {{ number_format($sales->sum('amount'), 2) }}</h4>
											<p class="mb-0">Total Revenue</p>
										</div>
										<div class="align-self-center">
											<i class="bi bi-currency-rupee fs-1"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Delete All Button -->
					@if($sales->total() > 0)
					<div class="d-flex justify-content-end mb-3">
						<button type="button" class="btn btn-danger" onclick="deleteAllSales()">
							<i class="bi bi-trash me-1"></i> Delete All Sales
						</button>
					</div>
					@endif

					<!-- Sales Table -->
					<div class="table-responsive">
						<table class="table table-hover">
							<thead class="table-dark">
								<tr>
									<th>User</th>
									<th>Service</th>
									<th>Amount</th>
									<th>Status</th>
									<th>Purchased At</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@forelse($sales as $sale)
								<tr>
									<td>
										<div class="d-flex align-items-center">
											@if($sale->user && $sale->user->avatar)
												<img src="{{ asset('avatar/' . $sale->user->avatar) }}" alt="Avatar" class="rounded-circle me-2" width="32" height="32" onerror="console.log('Avatar failed to load:', this.src); this.style.display='none';">
											@else
												<div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
													<i class="bi bi-person text-white"></i>
												</div>
											@endif
											<div>
												@if($sale->user)
													<div class="fw-bold">{{ $sale->user->full_name ?? 'N/A' }}</div>
													<small class="text-muted">{{ $sale->user->username ?? 'N/A' }}</small>
													@if($sale->user->avatar)
														<br><small class="text-info">Avatar: {{ $sale->user->avatar }}</small>
													@endif
												@else
													<div class="fw-bold text-muted">User Deleted</div>
													<small class="text-muted">User ID: {{ $sale->user_id ?? 'N/A' }}</small>
												@endif
											</div>
										</div>
									</td>
									<td>
										<div>
											@if($sale->service)
												<div class="fw-bold">{{ $sale->service->title ?? 'N/A' }}</div>
												<small class="text-muted">ID: {{ $sale->service->id ?? 'N/A' }}</small>
											@else
												<div class="fw-bold text-muted">Service Deleted</div>
												<small class="text-muted">Service ID: {{ $sale->service_id ?? 'N/A' }}</small>
											@endif
										</div>
									</td>
									<td class="fw-bold">Rs. {{ number_format($sale->amount, 2) }}</td>
									<td>
										<span class="badge {{ $sale->status == 'active' ? 'bg-success' : ($sale->status == 'inactive' ? 'bg-warning' : 'bg-danger') }}">
											{{ ucfirst($sale->status) }}
										</span>
									</td>
									<td>{{ $sale->purchased_at->format('M d, Y H:i') }}</td>
									<td>
										<div class="d-flex gap-1">
											@if($sale->status == 'active')
												<button type="button" class="btn btn-outline-warning btn-sm" onclick="changeStatus({{ $sale->id }}, 'inactive')" title="Deactivate">
													<i class="bi bi-pause"></i>
												</button>
											@elseif($sale->status == 'inactive')
												<button type="button" class="btn btn-outline-success btn-sm" onclick="changeStatus({{ $sale->id }}, 'active')" title="Activate">
													<i class="bi bi-play"></i>
												</button>
											@endif

											@if($sale->status != 'refunded')
												<button type="button" class="btn btn-outline-info btn-sm" onclick="refundSale({{ $sale->id }})" title="Refund">
													<i class="bi bi-arrow-counterclockwise"></i>
												</button>
											@endif

											<button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSale({{ $sale->id }})" title="Delete">
												<i class="bi bi-trash"></i>
											</button>
										</div>
									</td>
								</tr>
								@empty
								<tr>
									<td colspan="6" class="text-center py-4">
										<i class="bi bi-graph-up fs-1 text-muted"></i>
										<h5 class="text-muted mt-3">No Sales Found</h5>
										<p class="text-muted">No paid service sales have been recorded yet.</p>
									</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>

					<!-- Pagination -->
					@if($sales->hasPages())
					<div class="d-flex justify-content-center mt-4">
						{{ $sales->links() }}
					</div>
					@endif

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->

<!-- Status Change Confirmation Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="statusModalLabel">Change Sale Status</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				Are you sure you want to change the status of this sale?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="confirmStatusChange">Confirm</button>
			</div>
		</div>
	</div>
</div>

<!-- Refund Confirmation Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="refundModalLabel">Refund Sale</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				Are you sure you want to refund this sale? This action cannot be undone and will restore the user's wallet balance.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="confirmRefund">Refund</button>
			</div>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteModalLabel">Delete Sale</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				Are you sure you want to delete this sale record? This action cannot be undone and will permanently remove the sale from the database.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
			</div>
		</div>
	</div>
</div>

<!-- Delete All Confirmation Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-danger text-white">
				<h5 class="modal-title" id="deleteAllModalLabel">
					<i class="bi bi-exclamation-triangle me-2"></i>Delete All Sales
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="alert alert-warning">
					<i class="bi bi-exclamation-circle me-2"></i>
					<strong>Warning:</strong> This is a permanent action!
				</div>
				<p>Are you sure you want to delete <strong>ALL</strong> sales records?</p>
				<p class="mb-0">This action cannot be undone and will permanently remove <strong>{{ $sales->total() }}</strong> sale(s) from the database.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="confirmDeleteAll">
					<i class="bi bi-trash me-1"></i>Delete All
				</button>
			</div>
		</div>
	</div>
</div>

@endsection

@section('javascript')

<script>
let currentSaleId = null;
let currentAction = null;

// CSRF Token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function changeStatus(saleId, newStatus) {
    currentSaleId = saleId;
    currentAction = 'status';
    document.getElementById('statusModalLabel').textContent = `Change Sale Status to ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`;
    document.getElementById('confirmStatusChange').onclick = function() {
        performStatusChange(saleId, newStatus);
    };
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function refundSale(saleId) {
    currentSaleId = saleId;
    currentAction = 'refund';
    document.getElementById('confirmRefund').onclick = function() {
        performRefund(saleId);
    };
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}

function deleteSale(saleId) {
    currentSaleId = saleId;
    currentAction = 'delete';
    document.getElementById('confirmDelete').onclick = function() {
        performDelete(saleId);
    };
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function deleteAllSales() {
    currentAction = 'deleteAll';
    document.getElementById('confirmDeleteAll').onclick = function() {
        performDeleteAll();
    };
    new bootstrap.Modal(document.getElementById('deleteAllModal')).show();
}

function performStatusChange(saleId, newStatus) {
    fetch('{{ url("panel/admin/paid-services/sales/change-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            sale_id: saleId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            showAlert('danger', data.message || 'Failed to change status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while changing status');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
    });
}

function performRefund(saleId) {
    fetch('{{ url("panel/admin/paid-services/sales/refund") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            sale_id: saleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            showAlert('danger', data.message || 'Failed to refund sale');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while refunding sale');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('refundModal')).hide();
    });
}

function performDelete(saleId) {
    fetch('{{ url("panel/admin/paid-services/sales/delete") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            sale_id: saleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            showAlert('danger', data.message || 'Failed to delete sale');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while deleting sale');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    });
}

function performDeleteAll() {
    fetch('{{ url("panel/admin/paid-services/sales/delete-all") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Failed to delete all sales');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while deleting all sales');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('deleteAllModal')).hide();
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check2' : 'exclamation-triangle'} me-1"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    `;

    const container = document.querySelector('.content');
    container.insertBefore(alertDiv, container.firstChild);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

@endsection

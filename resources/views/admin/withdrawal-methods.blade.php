@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/billing') }}">{{ __('admin.deposit_methods') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Withdrawal Methods</span>
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

					<!-- Add New Withdrawal Method Button -->
					<div class="row mb-4">
						<div class="col-12">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWithdrawalMethodModal">
								<i class="bi bi-plus-circle"></i> Add New Withdrawal Method
							</button>
						</div>
					</div>

					<!-- Withdrawal Methods List -->
					<div class="row">
						@forelse($withdrawalMethods ?? [] as $method)
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="card h-100">
								@if($method->image)
									<img src="{{ url('public/img', $method->image) }}" class="card-img-top" alt="{{ $method->name }}" style="height: 120px; object-fit: cover;">
								@endif
								<div class="card-body">
									<h6 class="card-title">{{ $method->name }}</h6>
									<p class="card-text">
										@if($method->min_amount)
											<strong>Min Amount:</strong> Rs. {{ number_format($method->min_amount, 2) }}<br>
										@endif
										@if($method->max_amount)
											<strong>Max Amount:</strong> Rs. {{ number_format($method->max_amount, 2) }}
										@endif
									</p>
									<div class="d-flex justify-content-between align-items-center">
										<span class="badge {{ $method->is_active ? 'bg-success' : 'bg-secondary' }}">
											{{ $method->is_active ? 'Active' : 'Inactive' }}
										</span>
										<div class="d-flex gap-1">
											<button type="button" class="btn btn-outline-primary btn-sm" onclick="editWithdrawalMethod({{ $method->id }})" title="Edit Withdrawal Method">
												<i class="far fa-edit"></i>
											</button>
											<form action="{{ url('panel/admin/withdrawal-methods') }}/{{ $method->id }}" method="POST" class="d-inline-block">
												@csrf
												@method('DELETE')
												<button type="button" class="btn btn-outline-danger btn-sm actionDelete" title="Delete Withdrawal Method">
													<i class="bi-trash-fill"></i>
												</button>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
						@empty
						<div class="col-12">
							<div class="text-center py-5">
								<i class="bi bi-credit-card fs-1 text-muted"></i>
								<h5 class="text-muted mt-3">No Withdrawal Methods Added</h5>
								<p class="text-muted">Click "Add New Withdrawal Method" to get started.</p>
							</div>
						</div>
						@endforelse
					</div>

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->

<!-- Add/Edit Withdrawal Method Modal -->
<div class="modal fade" id="addWithdrawalMethodModal" tabindex="-1" aria-labelledby="addWithdrawalMethodModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addWithdrawalMethodModalLabel">Add New Withdrawal Method</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="withdrawalMethodForm" method="POST" action="{{ url('panel/admin/withdrawal-methods/store') }}" enctype="multipart/form-data">
				@csrf
				<input type="hidden" id="method_id" name="method_id" value="">
				<div class="modal-body">
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Name</label>
						<div class="col-sm-9">
							<input type="text" id="name" name="name" class="form-control" placeholder="Bank Transfer, Easypaisa, etc." required>
							<small class="d-block text-muted">Enter the name of the withdrawal method</small>
		          </div>
		        </div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Image</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
								<label class="input-group-text" for="image">
									<i class="bi bi-upload"></i>
								</label>
							</div>
							<small class="d-block text-muted">Upload an image for this withdrawal method (optional)</small>
							<div id="imagePreview" class="mt-2" style="display: none;">
								<img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
							</div>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Min Amount</label>
						<div class="col-sm-9">
							<input type="number" id="min_amount" name="min_amount" class="form-control" step="0.01" min="0" placeholder="0.00">
							<small class="d-block text-muted">Minimum withdrawal amount (optional)</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Max Amount</label>
						<div class="col-sm-9">
							<input type="number" id="max_amount" name="max_amount" class="form-control" step="0.01" min="0" placeholder="0.00">
							<small class="d-block text-muted">Maximum withdrawal amount (optional)</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Status</label>
						<div class="col-sm-9">
							<div class="form-check form-switch">
								<input type="hidden" name="is_active" value="0">
								<input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
								<label class="form-check-label" for="is_active">Active</label>
							</div>
		          </div>
		        </div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Withdrawal Method</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Delete confirmation is handled by global SweetAlert -->

@endsection

@section('javascript')

<script>
// Withdrawal method CRUD functions
function editWithdrawalMethod(id) {
  // Fetch withdrawal method data via AJAX
  fetch(`{{ url('panel/admin/withdrawal-methods') }}/${id}`)
    .then(response => response.json())
    .then(data => {
      // Populate form with existing data
      document.getElementById('method_id').value = data.id;
      document.getElementById('name').value = data.name;
      document.getElementById('min_amount').value = data.min_amount || '';
      document.getElementById('max_amount').value = data.max_amount || '';
      document.getElementById('is_active').checked = data.is_active;

      // Show existing image if available
      if (data.image) {
        const previewDiv = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        previewImg.src = `{{ url('public/img') }}/${data.image}`;
        previewDiv.style.display = 'block';
      } else {
        document.getElementById('imagePreview').style.display = 'none';
      }

      // Update modal title
      document.getElementById('addWithdrawalMethodModalLabel').textContent = 'Edit Withdrawal Method';

      // Update form action
      document.getElementById('withdrawalMethodForm').action = '{{ url("panel/admin/withdrawal-methods/update") }}';

      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('addWithdrawalMethodModal'));
      modal.show();
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error loading withdrawal method data');
    });
}

// Image preview function
function previewImage(input) {
  const previewDiv = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      previewDiv.style.display = 'block';
    }
    
    reader.readAsDataURL(input.files[0]);
  } else {
    previewDiv.style.display = 'none';
  }
}

// Reset modal when closed
document.getElementById('addWithdrawalMethodModal').addEventListener('hidden.bs.modal', function () {
  // Reset form
  document.getElementById('withdrawalMethodForm').reset();
  document.getElementById('method_id').value = '';
  document.getElementById('addWithdrawalMethodModalLabel').textContent = 'Add New Withdrawal Method';
  document.getElementById('withdrawalMethodForm').action = '{{ url("panel/admin/withdrawal-methods/store") }}';
  
  // Clear image preview
  document.getElementById('imagePreview').style.display = 'none';
  document.getElementById('previewImg').src = '';
});

// Delete confirmation is handled by global admin-functions.js
</script>

@endsection

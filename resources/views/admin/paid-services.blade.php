@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/notifications') }}">Manual Notification</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Paid Services</span>
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

					<!-- Add New Paid Service Button -->
					<div class="row mb-4">
						<div class="col-12">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaidServiceModal">
								<i class="bi bi-plus-circle"></i> Add New Paid Service
							</button>
						</div>
					</div>

					<!-- Paid Services List -->
					<div class="row">
						@forelse($paidServices ?? [] as $service)
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="card h-100">
								@if($service->image)
								<img src="{{ url('public/img', $service->image) }}" class="card-img-top" alt="{{ $service->title }}" style="height: 200px; object-fit: cover;">
								@else
								<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
									<i class="bi bi-gem text-muted" style="font-size: 3rem;"></i>
								</div>
								@endif
								<div class="card-body">
									<h6 class="card-title">{{ $service->title }}</h6>
									<p class="card-text">
										<strong>Price:</strong> Rs. {{ number_format($service->price, 2) }}<br>
										@if($service->description)
											<small class="text-muted">{{ Str::limit($service->description, 100) }}</small>
										@endif
									</p>
									<div class="d-flex justify-content-between align-items-center">
										<span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
											{{ $service->is_active ? 'Active' : 'Inactive' }}
										</span>
										<div class="d-flex gap-1">
											<button type="button" class="btn btn-outline-primary btn-sm" onclick="editPaidService({{ $service->id }})" title="Edit Paid Service">
												<i class="far fa-edit"></i>
											</button>
											<form action="{{ url('panel/admin/paid-services') }}/{{ $service->id }}" method="POST" class="d-inline-block">
												@csrf
												@method('DELETE')
												<button type="button" class="btn btn-outline-danger btn-sm actionDelete" title="Delete Paid Service">
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
								<i class="bi bi-gem fs-1 text-muted"></i>
								<h5 class="text-muted mt-3">No Paid Services Added</h5>
								<p class="text-muted">Click "Add New Paid Service" to get started.</p>
							</div>
						</div>
						@endforelse
					</div>

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->

<!-- Add/Edit Paid Service Modal -->
<div class="modal fade" id="addPaidServiceModal" tabindex="-1" aria-labelledby="addPaidServiceModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addPaidServiceModalLabel">Add New Paid Service</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="paidServiceForm" method="POST" action="{{ url('panel/admin/paid-services/store') }}" enctype="multipart/form-data">
				@csrf
				<input type="hidden" id="service_id" name="service_id" value="">
				<div class="modal-body">
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Title</label>
						<div class="col-sm-9">
							<input type="text" id="title" name="title" class="form-control" placeholder="Golden Guess, Paid Service, etc." required>
							<small class="d-block text-muted">Enter the title of the paid service</small>
		          </div>
		        </div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Price</label>
						<div class="col-sm-9">
							<input type="number" id="price" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
							<small class="d-block text-muted">Enter the price for this service</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Description</label>
						<div class="col-sm-9">
							<textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter service description..."></textarea>
							<small class="d-block text-muted">Enter a description for this service (optional)</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Golden Text</label>
						<div class="col-sm-9">
							<textarea id="golden_text" name="golden_text" class="form-control" rows="5" placeholder="Enter the premium content that users will purchase..."></textarea>
							<small class="d-block text-muted">This is the actual content that users will buy (keep secure)</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Image</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="file" id="image" name="image" accept="image/*" class="form-control" style="height: 38px; font-size: 14px;">
								<label class="input-group-text" for="image" style="cursor: pointer;">
									<i class="bi bi-cloud-upload"></i>
								</label>
							</div>
							<small class="d-block text-muted mt-1">Upload an image for this service (optional)</small>
							<div id="imagePreview" class="mt-2" style="display: none;">
								<img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
							</div>
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
					<button type="submit" class="btn btn-primary">Save Paid Service</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				Are you sure you want to delete this paid service? This action cannot be undone.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
			</div>
		</div>
	</div>
</div>

@endsection

@section('css')
<style>
/* Minimal file input styling */
#image {
    display: block !important;
    width: 100% !important;
    opacity: 1 !important;
    visibility: visible !important;
    position: relative !important;
    z-index: 1 !important;
    height: 38px !important;
    font-size: 14px !important;
    padding: 6px 12px !important;
    border: 1px solid var(--bs-border-color) !important;
    border-radius: 0.375rem 0 0 0.375rem !important;
    background-color: var(--bs-body-bg) !important;
    color: var(--bs-body-color) !important;
}

/* Hide the default file input button and make it look like a text input */
#image::-webkit-file-upload-button {
    display: none !important;
}

#image::-moz-file-upload-button {
    display: none !important;
}

/* Style the input group text (upload icon) */
.input-group-text {
    background-color: var(--bs-secondary-bg) !important;
    border-color: var(--bs-border-color) !important;
    border-left: 0 !important;
    border-radius: 0 0.375rem 0.375rem 0 !important;
    transition: all 0.15s ease-in-out !important;
    color: var(--bs-body-color) !important;
}

.input-group-text:hover {
    background-color: var(--bs-tertiary-bg) !important;
    border-color: var(--bs-border-color) !important;
}

/* Make the file input look like a text input */
#image::file-selector-button {
    display: none !important;
}

/* Custom file input styling */
#image {
    cursor: pointer !important;
}

#image:hover {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 0 0 0.25rem var(--bs-primary-rgb, 0.25) !important;
}

#image:focus {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 0 0 0.25rem var(--bs-primary-rgb, 0.25) !important;
    outline: 0 !important;
}
</style>
@endsection

@section('javascript')

<script>
// Paid service CRUD functions
function editPaidService(id) {
  // Fetch paid service data via AJAX
  fetch(`{{ url('panel/admin/paid-services') }}/${id}`)
    .then(response => response.json())
    .then(data => {
      // Populate form with existing data
      document.getElementById('service_id').value = data.id;
      document.getElementById('title').value = data.title;
      document.getElementById('price').value = data.price;
      document.getElementById('description').value = data.description || '';
      document.getElementById('golden_text').value = data.golden_text || '';
      document.getElementById('is_active').checked = data.is_active;

      // Show existing image if available
      if (data.image) {
        const previewDiv = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        previewImg.src = `{{ url('public/img/') }}/${data.image}`;
        previewDiv.style.display = 'block';
      }

      // Update modal title
      document.getElementById('addPaidServiceModalLabel').textContent = 'Edit Paid Service';

      // Update form action
      document.getElementById('paidServiceForm').action = '{{ url("panel/admin/paid-services/update") }}';

      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('addPaidServiceModal'));
      modal.show();
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error loading paid service data');
    });
}

// Image preview functionality
document.getElementById('image').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select a valid image file.');
      this.value = '';
      return;
    }
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      alert('Image size should be less than 2MB.');
      this.value = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      const previewDiv = document.getElementById('imagePreview');
      const previewImg = document.getElementById('previewImg');
      previewImg.src = e.target.result;
      previewDiv.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
});

// Make the input group text clickable
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('image');
  const inputGroupText = document.querySelector('.input-group-text');
  
  // Make the upload icon clickable
  if (inputGroupText) {
    inputGroupText.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      fileInput.click();
    });
  }
  
  // Prevent file input from opening dialog again when clicked
  fileInput.addEventListener('click', function(e) {
    e.stopPropagation();
  });
  
  // Update file input placeholder text
  fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
      this.setAttribute('data-text', this.files[0].name);
    } else {
      this.removeAttribute('data-text');
    }
  });
});

// Reset modal when closed
document.getElementById('addPaidServiceModal').addEventListener('hidden.bs.modal', function () {
  // Reset form
  document.getElementById('paidServiceForm').reset();
  document.getElementById('service_id').value = '';
  document.getElementById('imagePreview').style.display = 'none';
  document.getElementById('addPaidServiceModalLabel').textContent = 'Add New Paid Service';
  document.getElementById('paidServiceForm').action = '{{ url("panel/admin/paid-services/store") }}';
});

// Delete confirmation
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('actionDelete')) {
    e.preventDefault();
    const form = e.target.closest('form');
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    document.getElementById('confirmDelete').onclick = function() {
      form.submit();
    };
    
    modal.show();
  }
});
</script>

@endsection

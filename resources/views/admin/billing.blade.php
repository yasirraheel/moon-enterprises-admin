@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('admin.billing_information') }}</span>
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

					<!-- Add New Payment Method Button -->
					<div class="row mb-4">
						<div class="col-12">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
								<i class="bi bi-plus-circle"></i> Add New Payment Method
							</button>
						</div>
					</div>

					<!-- Payment Methods List -->
					<div class="row">
						@forelse($paymentMethods ?? [] as $method)
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="card h-100">
								@if($method->bank_image)
								<img src="{{ url('public/img', $method->bank_image) }}" class="card-img-top" alt="Bank Image" style="height: 150px; object-fit: cover;">
								@else
								<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
									<i class="bi bi-bank fs-1 text-muted"></i>
								</div>
								@endif
								<div class="card-body">
									<h6 class="card-title">{{ $method->bank_or_account_name }}</h6>
									<p class="card-text">
										<strong>Account Title:</strong> {{ $method->account_title }}<br>
										<strong>Account No:</strong> {{ $method->account_no }}<br>
										@if($method->minimum_limit)
											<strong>Min Limit:</strong> Rs. {{ number_format($method->minimum_limit, 2) }}<br>
										@endif
										@if($method->maximum_limit)
											<strong>Max Limit:</strong> Rs. {{ number_format($method->maximum_limit, 2) }}
										@endif
									</p>
									<div class="d-flex justify-content-between align-items-center">
										<span class="badge {{ $method->is_active ? 'bg-success' : 'bg-secondary' }}">
											{{ $method->is_active ? 'Active' : 'Inactive' }}
										</span>
										<button type="button" class="text-reset fs-5 me-2" onclick="editPaymentMethod({{ $method->id }})" title="Edit Payment Method">
											<i class="far fa-edit"></i>
										</button>
										<form action="{{ url('panel/admin/payment-methods') }}/{{ $method->id }}" method="POST" class="d-inline-block align-top">
						 @csrf
											@method('DELETE')
											<button type="button" class="btn btn-link text-danger e-none fs-5 p-0 actionDelete" title="Delete Payment Method">
												<i class="bi-trash-fill"></i>
											</button>
										</form>
									</div>
								</div>
							</div>
						</div>
						@empty
						<div class="col-12">
							<div class="text-center py-5">
								<i class="bi bi-credit-card fs-1 text-muted"></i>
								<h5 class="text-muted mt-3">No Payment Methods Added</h5>
								<p class="text-muted">Click "Add New Payment Method" to get started.</p>
							</div>
						</div>
						@endforelse
					</div>

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->

<!-- Add/Edit Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addPaymentMethodModalLabel">Add New Payment Method</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="paymentMethodForm" method="POST" action="{{ url('panel/admin/payment-methods/store') }}" enctype="multipart/form-data">
				@csrf
				<input type="hidden" id="method_id" name="method_id" value="">
				<div class="modal-body">
						<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Bank or Account Name</label>
						<div class="col-sm-9">
							<input type="text" id="bank_or_account_name" name="bank_or_account_name" class="form-control" placeholder="Easypaisa, ABL, etc." required>
							<small class="d-block text-muted">Enter the name of your bank or payment service</small>
		          </div>
		        </div>

		        <div class="row mb-3">
						<label class="col-sm-3 col-form-label">Account Title</label>
						<div class="col-sm-9">
							<input type="text" id="account_title" name="account_title" class="form-control" placeholder="Account holder name" required>
							<small class="d-block text-muted">Enter the account holder's name or title</small>
		          </div>
		        </div>

						<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Account Number</label>
						<div class="col-sm-9">
							<input type="text" id="account_no" name="account_no" class="form-control" placeholder="Account number" required>
							<small class="d-block text-muted">Enter your bank account number or mobile wallet number</small>
		          </div>
		        </div>

						<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Bank/Account Image</label>
						<div class="col-sm-9">
							<div class="file-upload-wrapper">
								<input type="file" name="bank_image" id="modal_bank_image" class="form-control d-none" accept="image/*" onchange="previewModalImage(this)">
								<div class="file-upload-area" onclick="document.getElementById('modal_bank_image').click()">
									<div class="file-upload-content">
										<i class="bi bi-cloud-upload fs-1 text-muted"></i>
										<p class="mb-2">Click to upload bank image</p>
										<small class="text-muted">JPG, PNG, GIF up to 5MB</small>
									</div>
								</div>
							</div>

							<!-- Image Preview -->
							<div id="modal-image-preview" class="mt-3" style="display: none;">
								<img id="modal-preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
								<div class="mt-2">
									<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeModalImage()">
										<i class="bi bi-trash"></i> Remove
									</button>
		          </div>
		        </div>

							<!-- Current Image Display -->
							<div id="modal-current-image" class="mt-3" style="display: none;">
								<h6>Current Image:</h6>
								<img id="modal-current-img" src="" alt="Current Image" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
								<div class="mt-2">
									<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCurrentModalImage()">
										<i class="bi bi-trash"></i> Remove Current Image
									</button>
								</div>
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

						<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Sort Order</label>
						<div class="col-sm-9">
							<input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0">
							<small class="d-block text-muted">Lower numbers appear first</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Minimum Limit</label>
						<div class="col-sm-9">
							<input type="number" id="minimum_limit" name="minimum_limit" class="form-control" step="0.01" min="0" placeholder="0.00">
							<small class="d-block text-muted">Minimum deposit amount for this payment method</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Maximum Limit</label>
						<div class="col-sm-9">
							<input type="number" id="maximum_limit" name="maximum_limit" class="form-control" step="0.01" min="0" placeholder="0.00">
							<small class="d-block text-muted">Maximum deposit amount for this payment method</small>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Payment Method</button>
				</div>
			</form>
		</div>
		          </div>
		        </div>

@endsection

@section('javascript')

<style>
.file-upload-wrapper {
  position: relative;
}

.file-upload-area {
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background-color: #f8f9fa;
}

.file-upload-area:hover {
  border-color: #007bff;
  background-color: #e3f2fd;
}

.file-upload-area.dragover {
  border-color: #007bff;
  background-color: #e3f2fd;
}

.file-upload-content {
  pointer-events: none;
}

.current-image {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 15px;
  background-color: #f8f9fa;
}

#image-preview {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 15px;
  background-color: #f8f9fa;
}
</style>

<script type="text/javascript">
// Modal image preview functions
function previewModalImage(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];

    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
      alert('File size must be less than 5MB');
      input.value = '';
      return;
    }

    // Validate file type
    if (!file.type.match('image.*')) {
      alert('Please select a valid image file');
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('modal-preview-img').src = e.target.result;
      document.getElementById('modal-image-preview').style.display = 'block';

      // Hide current image section when new image is selected
      const currentImage = document.getElementById('modal-current-image');
      if (currentImage) {
        currentImage.style.display = 'none';
      }
    };
    reader.readAsDataURL(file);
  }
}

function removeModalImage() {
  document.getElementById('modal_bank_image').value = '';
  document.getElementById('modal-image-preview').style.display = 'none';

  // Show current image section again
  const currentImage = document.getElementById('modal-current-image');
  if (currentImage && currentImage.querySelector('img').src) {
    currentImage.style.display = 'block';
  }
}

function removeCurrentModalImage() {
  document.getElementById('modal-current-img').src = '';
  document.getElementById('modal-current-image').style.display = 'none';
}

// Payment method CRUD functions
function editPaymentMethod(id) {
  // Fetch payment method data via AJAX
  fetch(`{{ url('panel/admin/payment-methods') }}/${id}`)
    .then(response => response.json())
    .then(data => {
      // Populate form with existing data
      document.getElementById('method_id').value = data.id;
      document.getElementById('bank_or_account_name').value = data.bank_or_account_name;
      document.getElementById('account_title').value = data.account_title;
      document.getElementById('account_no').value = data.account_no;
      document.getElementById('is_active').checked = data.is_active;
      document.getElementById('sort_order').value = data.sort_order;
      document.getElementById('minimum_limit').value = data.minimum_limit || '';
      document.getElementById('maximum_limit').value = data.maximum_limit || '';

      // Update modal title
      document.getElementById('addPaymentMethodModalLabel').textContent = 'Edit Payment Method';

      // Show current image if exists
      if (data.bank_image) {
        const imageUrl = `{{ url('public/img') }}/${data.bank_image}`;
        console.log('Setting image URL:', imageUrl);
        const imgElement = document.getElementById('modal-current-img');
        imgElement.src = imageUrl;
        imgElement.onerror = function() {
          console.log('Image failed to load:', imageUrl);
          this.style.display = 'none';
        };
        imgElement.onload = function() {
          console.log('Image loaded successfully:', imageUrl);
        };
        document.getElementById('modal-current-image').style.display = 'block';
      } else {
        console.log('No bank image found');
        document.getElementById('modal-current-image').style.display = 'none';
      }

      // Update form action
      document.getElementById('paymentMethodForm').action = '{{ url("panel/admin/payment-methods/update") }}';

      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('addPaymentMethodModal'));
      modal.show();
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error loading payment method data');
    });
}


// Reset modal when closed
document.getElementById('addPaymentMethodModal').addEventListener('hidden.bs.modal', function () {
  // Reset form
  document.getElementById('paymentMethodForm').reset();
  document.getElementById('method_id').value = '';
  document.getElementById('addPaymentMethodModalLabel').textContent = 'Add New Payment Method';
  document.getElementById('paymentMethodForm').action = '{{ url("panel/admin/payment-methods/store") }}';

  // Hide image previews
  document.getElementById('modal-image-preview').style.display = 'none';
  document.getElementById('modal-current-image').style.display = 'none';
});

// Debug form submission
document.getElementById('paymentMethodForm').addEventListener('submit', function(e) {
  console.log('Form action:', this.action);
  console.log('Form method:', this.method);
  console.log('Form data:', new FormData(this));

  // Check if the action is correct
  if (this.action.includes('panel/admin/payment-methods/store') || this.action.includes('panel/admin/payment-methods/update')) {
    console.log('Form action is correct');
  } else {
    console.log('Form action is WRONG:', this.action);
    e.preventDefault();
    alert('Form action is incorrect: ' + this.action);
  }
});

// Drag and drop functionality for modal
document.addEventListener('DOMContentLoaded', function() {
  const uploadAreas = document.querySelectorAll('.file-upload-area');

  uploadAreas.forEach(uploadArea => {
    uploadArea.addEventListener('dragover', function(e) {
      e.preventDefault();
      uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
      e.preventDefault();
      uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
      e.preventDefault();
      uploadArea.classList.remove('dragover');

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        const input = uploadArea.parentElement.querySelector('input[type="file"]');
        input.files = files;
        previewModalImage(input);
      }
    });
  });
});
</script>
  @endsection

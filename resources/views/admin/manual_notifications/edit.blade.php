@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
	<a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<a class="text-reset" href="{{ route('admin.manual_notifications.index') }}">{{ __('admin.manual_notifications') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<span class="text-muted">{{ __('admin.edit_notification') }}</span>
</h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">
			@include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-header bg-white">
					<h6 class="mb-0">{{ __('admin.edit_notification') }}</h6>
				</div>

				<div class="card-body p-lg-5">
					<form method="POST" action="{{ route('admin.manual_notifications.update', $manualNotification) }}" 
						enctype="multipart/form-data">
						@csrf
						@method('PUT')

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.title') }} <span class="text-danger">*</span></label>
							<div class="col-sm-10">
								<input type="text" name="title" value="{{ old('title', $manualNotification->title) }}" 
									class="form-control @error('title') is-invalid @enderror" 
									placeholder="{{ __('admin.enter_notification_title') }}" required>
								@error('title')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.message') }} <span class="text-danger">*</span></label>
							<div class="col-sm-10">
								<textarea name="message" rows="5" 
									class="form-control @error('message') is-invalid @enderror" 
									placeholder="{{ __('admin.enter_notification_message') }}" required>{{ old('message', $manualNotification->message) }}</textarea>
								@error('message')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.image') }}</label>
							<div class="col-sm-10">
								<div class="file-upload-wrapper">
									<input type="file" name="image" id="notification_image" 
										class="form-control d-none @error('image') is-invalid @enderror" 
										accept="image/*" onchange="previewNotificationImage(this)">
									<div class="file-upload-area" onclick="document.getElementById('notification_image').click()">
										<div class="file-upload-content">
											<i class="bi bi-cloud-upload fs-1 text-muted"></i>
											<p class="mb-2">Click to upload notification image</p>
											<small class="text-muted">JPG, PNG, GIF up to 2MB</small>
										</div>
									</div>
								</div>

								<!-- Current Image Display -->
								@if($manualNotification->image)
								<div id="notification-current-image" class="mt-3">
									<h6>Current Image:</h6>
									<img src="{{ $manualNotification->image_url }}" alt="{{ $manualNotification->title }}" 
										class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
									<div class="mt-2">
										<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCurrentNotificationImage()">
											<i class="bi bi-trash"></i> Remove Current Image
										</button>
									</div>
								</div>
								@endif

								<!-- Image Preview -->
								<div id="notification-image-preview" class="mt-3" style="display: none;">
									<img id="notification-preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
									<div class="mt-2">
										<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNotificationImage()">
											<i class="bi bi-trash"></i> Remove
										</button>
									</div>
								</div>

								@error('image')
								<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<fieldset class="row mb-3">
							<legend class="col-form-label col-sm-2 pt-0 text-lg-end">{{ __('admin.status') }}</legend>
							<div class="col-sm-10">
								<div class="form-check form-switch form-switch-md">
									<input class="form-check-input" type="checkbox" name="is_active" value="1" 
										{{ old('is_active', $manualNotification->is_active) ? 'checked' : '' }} role="switch">
									<label class="form-check-label" for="is_active">
										{{ __('admin.active') }}
									</label>
								</div>
							</div>
						</fieldset>

						<div class="row mb-3">
							<div class="col-sm-10 offset-sm-2">
								<button type="submit" class="btn btn-primary px-4">
									<i class="bi bi-check-lg me-1"></i> {{ __('admin.update_notification') }}
								</button>
								<a href="{{ route('admin.manual_notifications.index') }}" class="btn btn-secondary px-4 ms-2">
									<i class="bi bi-arrow-left me-1"></i> {{ __('admin.cancel') }}
								</a>
							</div>
						</div>

					</form>
				</div>
			</div>
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

#notification-image-preview {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 15px;
  background-color: #f8f9fa;
}

#notification-current-image {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 15px;
  background-color: #f8f9fa;
}
</style>

<script type="text/javascript">
// Notification image preview functions
function previewNotificationImage(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];

    // Validate file size (2MB limit)
    if (file.size > 2 * 1024 * 1024) {
      alert('File size must be less than 2MB');
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
      document.getElementById('notification-preview-img').src = e.target.result;
      document.getElementById('notification-image-preview').style.display = 'block';

      // Hide current image section when new image is selected
      const currentImage = document.getElementById('notification-current-image');
      if (currentImage) {
        currentImage.style.display = 'none';
      }
    };
    reader.readAsDataURL(file);
  }
}

function removeNotificationImage() {
  document.getElementById('notification_image').value = '';
  document.getElementById('notification-image-preview').style.display = 'none';

  // Show current image section again
  const currentImage = document.getElementById('notification-current-image');
  if (currentImage && currentImage.querySelector('img').src) {
    currentImage.style.display = 'block';
  }
}

function removeCurrentNotificationImage() {
  document.getElementById('notification_image').value = '';
  const currentImage = document.getElementById('notification-current-image');
  if (currentImage) {
    currentImage.style.display = 'none';
  }
}

// Drag and drop functionality
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
        previewNotificationImage(input);
      }
    });
  });
});
</script>
@endsection

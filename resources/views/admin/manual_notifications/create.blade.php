@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
	<a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<a class="text-reset" href="{{ route('admin.manual_notifications.index') }}">{{ __('admin.manual_notifications') }}</a>
	<i class="bi-chevron-right me-1 fs-6"></i>
	<span class="text-muted">{{ __('admin.add_notification') }}</span>
</h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">
			@include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-header bg-white">
					<h6 class="mb-0">{{ __('admin.add_notification') }}</h6>
				</div>

				<div class="card-body p-lg-5">
					<form method="POST" action="{{ route('admin.manual_notifications.store') }}"
						enctype="multipart/form-data">
						@csrf

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.notification_type') }} <span class="text-danger">*</span></label>
							<div class="col-sm-10">
								<select name="notification_type" id="notification_type"
									class="form-select @error('notification_type') is-invalid @enderror"
								required onchange="toggleNotificationType()">
									<option value="public" {{ old('notification_type') == 'public' ? 'selected' : '' }}>
										{{ __('admin.public_notification') }}
									</option>
									<option value="user_specific" {{ old('notification_type') == 'user_specific' ? 'selected' : '' }}>
										{{ __('admin.system_notification') }}
									</option>
								</select>
								<small class="text-muted">
									{{ __('admin.public_for_all_users') }}, {{ __('admin.system_for_specific_user') }}
								</small>
								@error('notification_type')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<!-- User Selection (only shown when System is selected) -->
						<div class="row mb-3" id="user-selection-row" style="display: none;">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.select_user') }} <span class="text-danger">*</span></label>
							<div class="col-sm-10">
								<!-- Hidden inputs to store selected user IDs -->
								<div id="selected_user_ids"></div>

								<!-- Selected Users Display -->
								<div id="selected-users-container" class="mb-3" style="display: none;">
									<div class="d-flex flex-wrap gap-2" id="selected-users-badges"></div>
									<button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearAllUsers()">
										<i class="bi bi-x-circle"></i> {{ __('admin.clear_all') }}
									</button>
								</div>

								<input type="text" id="user-search"
									class="form-control mb-2"
									placeholder="{{ __('admin.search_by_username_email_phone') }}">

								<div id="user-list" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
									<div class="list-group">
										@foreach($users as $user)
										<a href="javascript:void(0)"
										   class="list-group-item list-group-item-action user-item"
										   data-user-id="{{ $user->id }}"
										   data-username="{{ $user->username }}"
										   data-phone="{{ $user->phone ?? '' }}"
										   onclick="toggleUserSelection({{ $user->id }}, '{{ addslashes($user->username) }}', '{{ addslashes($user->phone ?? '') }}')">
											<div class="d-flex w-100 justify-content-between align-items-center">
												<div>
													<h6 class="mb-1">
														<i class="bi bi-person-circle me-1"></i> {{ $user->username }}
													</h6>
													<small class="text-muted">
														@if($user->phone)
															<i class="bi bi-phone me-1"></i> {{ $user->phone }}
														@endif
													</small>
												</div>
												<span class="badge bg-primary select-badge">{{ __('admin.select') }}</span>
											</div>
										</a>
										@endforeach
									</div>
								</div>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('admin.title') }} <span class="text-danger">*</span></label>
							<div class="col-sm-10">
								<input type="text" name="title" value="{{ old('title') }}"
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
									placeholder="{{ __('admin.enter_notification_message') }}" required>{{ old('message') }}</textarea>
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
										{{ old('is_active', true) ? 'checked' : '' }} role="switch">
									<label class="form-check-label" for="is_active">
										{{ __('admin.active') }}
									</label>
								</div>
							</div>
						</fieldset>

						<div class="row mb-3">
							<div class="col-sm-10 offset-sm-2">
								<button type="submit" class="btn btn-primary px-4">
									<i class="bi bi-check-lg me-1"></i> {{ __('admin.create_notification') }}
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
// Array to store selected user IDs
let selectedUsers = [];

// Toggle user selection row based on notification type
function toggleNotificationType() {
  const notificationType = document.getElementById('notification_type').value;
  const userSelectionRow = document.getElementById('user-selection-row');

  if (notificationType === 'user_specific') {
    userSelectionRow.style.display = 'flex';
  } else {
    userSelectionRow.style.display = 'none';
    clearAllUsers();
  }
}

// User search functionality - Client-side filtering
document.addEventListener('DOMContentLoaded', function() {
  const userSearch = document.getElementById('user-search');

  if (userSearch) {
    userSearch.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const userItems = document.querySelectorAll('.user-item');

      if (query.length === 0) {
        // Show all users
        userItems.forEach(item => {
          item.style.display = '';
        });
        return;
      }

      // Filter users client-side
      userItems.forEach(item => {
        const username = item.dataset.username.toLowerCase();
        const phone = item.dataset.phone.toLowerCase();

        if (username.includes(query) || phone.includes(query)) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  }

  // Initialize on page load if user_specific was selected (from old input)
  toggleNotificationType();
});

// Toggle user selection (add or remove)
function toggleUserSelection(userId, username, phone) {
  const index = selectedUsers.findIndex(u => u.id === userId);

  if (index > -1) {
    // User already selected, remove them
    selectedUsers.splice(index, 1);
  } else {
    // Add user to selection
    selectedUsers.push({ id: userId, username: username, phone: phone });
  }

  updateSelectedUsersDisplay();
}

// Update the display of selected users
function updateSelectedUsersDisplay() {
  const container = document.getElementById('selected-users-container');
  const badgesContainer = document.getElementById('selected-users-badges');
  const hiddenInputsContainer = document.getElementById('selected_user_ids');

  // Clear existing hidden inputs
  hiddenInputsContainer.innerHTML = '';

  if (selectedUsers.length === 0) {
    container.style.display = 'none';
    // Reset all user item badges
    document.querySelectorAll('.user-item').forEach(item => {
      item.classList.remove('active');
      const badge = item.querySelector('.select-badge');
      badge.textContent = '{{ __('admin.select') }}';
      badge.classList.remove('bg-success');
      badge.classList.add('bg-primary');
    });
    return;
  }

  // Show container
  container.style.display = 'block';

  // Create badges for selected users
  badgesContainer.innerHTML = '';
  selectedUsers.forEach(user => {
    // Create hidden input for each user
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'user_id[]';
    hiddenInput.value = user.id;
    hiddenInputsContainer.appendChild(hiddenInput);

    // Create badge
    const badge = document.createElement('span');
    badge.className = 'badge bg-success d-inline-flex align-items-center gap-1';
    badge.innerHTML = `
      <i class="bi bi-person-check"></i>
      ${user.username}
      <button type="button" class="btn-close btn-close-white" style="font-size: 0.7rem;"
              onclick="removeUser(${user.id})" aria-label="Remove"></button>
    `;
    badgesContainer.appendChild(badge);
  });

  // Update user list items to show selected state
  document.querySelectorAll('.user-item').forEach(item => {
    const itemId = parseInt(item.dataset.userId);
    const isSelected = selectedUsers.some(u => u.id === itemId);
    const badge = item.querySelector('.select-badge');

    if (isSelected) {
      item.classList.add('active');
      badge.textContent = '{{ __('admin.selected') }}';
      badge.classList.remove('bg-primary');
      badge.classList.add('bg-success');
    } else {
      item.classList.remove('active');
      badge.textContent = '{{ __('admin.select') }}';
      badge.classList.remove('bg-success');
      badge.classList.add('bg-primary');
    }
  });
}

// Remove a specific user from selection
function removeUser(userId) {
  const index = selectedUsers.findIndex(u => u.id === userId);
  if (index > -1) {
    selectedUsers.splice(index, 1);
    updateSelectedUsersDisplay();
  }
}

// Clear all selected users
function clearAllUsers() {
  selectedUsers = [];
  updateSelectedUsersDisplay();
  document.getElementById('user-search').value = '';

  // Show all users
  const userItems = document.querySelectorAll('.user-item');
  userItems.forEach(item => {
    item.style.display = '';
  });
}

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

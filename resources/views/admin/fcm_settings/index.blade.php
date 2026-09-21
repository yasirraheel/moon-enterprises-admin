@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
    <i class="bi-chevron-right me-1 fs-6"></i>
    <span class="text-muted">FCM Settings</span>
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
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">FCM Notification Settings</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFcmSettingModal">
                        <i class="bi bi-plus-lg me-1"></i> Add New Type
                    </button>
                </div>

                <div class="card-body p-lg-5">
                    <form method="POST" action="{{ route('admin.fcm_settings.update') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="border-0">Notification Type</th>
                                        <th class="border-0">Key</th>
                                        <th class="border-0">Description</th>
                                        <th class="border-0 text-center">Status</th>
                                        <th class="border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fcmSettings as $setting)
                                    <tr>
                                        <td class="align-middle">{{ $setting->label }}</td>
                                        <td class="align-middle"><code>{{ $setting->notification_type }}</code></td>
                                        <td class="align-middle">{{ $setting->description }}</td>
                                        <td class="text-center align-middle">
                                            <div class="form-check form-switch form-switch-md d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="settings[{{ $setting->notification_type }}]" value="1" id="switch_{{ $setting->id }}" {{ $setting->is_enabled ? 'checked' : '' }} role="switch">
                                                <label class="form-check-label" for="switch_{{ $setting->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-danger btn-sm rounded-pill" onclick="confirmDelete('{{ route('admin.fcm_settings.destroy', $setting->id) }}')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> {{ __('admin.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteFcmForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDelete(url) {
        if (confirm('Are you sure you want to delete this notification type?')) {
            var form = document.getElementById('deleteFcmForm');
            form.action = url;
            form.submit();
        }
    }
</script>

<!-- Modal -->
<div class="modal fade" id="addFcmSettingModal" tabindex="-1" aria-labelledby="addFcmSettingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addFcmSettingModalLabel">Add New Notification Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.fcm_settings.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label for="label" class="form-label">Label (Name)</label>
                <input type="text" class="form-control" id="label" name="label" placeholder="e.g. New Feature Alert" required>
            </div>
            <div class="mb-3">
                <label for="notification_type" class="form-label">Notification Type Key</label>
                <input type="text" class="form-control" id="notification_type" name="notification_type" placeholder="e.g. new_feature_alert" required>
                <div class="form-text">Unique key used in code (auto-converted to snake_case).</div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Sent when..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Type</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

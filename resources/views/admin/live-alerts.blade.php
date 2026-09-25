@extends('admin.layout')

@section('title', 'Live Activity & Online Users Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="card-title mb-1">
                        <i class="bi bi-broadcast text-primary me-2"></i>Live Activity &amp; Online Users
                    </h3>
                    <p class="text-muted mb-0 small">
                        Manage dynamic social proof cards on the mobile app: online member counters and live transaction broadcast messages.
                    </p>
                </div>
                <div>
                    <a href="{{ url('api/live-alerts') }}" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-code-slash me-1"></i> Preview Live API
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success_message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('error_message'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @include('errors.errors-forms')

            <!-- Global Configurations Form -->
            <form action="{{ route('admin.live_alerts.settings') }}" method="POST">
                @csrf
                <div class="row mb-4">
                    <!-- 1. Active Online Users Settings -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0 text-success">
                                    <i class="bi bi-people-fill me-2"></i>Card 1: Active Online Users
                                </h5>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="online_users_enabled" name="online_users_enabled" 
                                           {{ ($settings->online_users_enabled ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="online_users_enabled">Active in App</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Configures the live simulated online users badge. Numbers gently fluctuate in real-time within the min/max limits.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="online_users_base" class="form-label small fw-bold">Base Starting Count</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                            <input type="number" class="form-control" id="online_users_base" name="online_users_base" 
                                                   value="{{ old('online_users_base', $settings->online_users_base ?? 452) }}" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="online_users_interval" class="form-label small fw-bold">Update Interval (seconds)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-stopwatch"></i></span>
                                            <input type="number" class="form-control" id="online_users_interval" name="online_users_interval" 
                                                   value="{{ old('online_users_interval', $settings->online_users_interval ?? 6) }}" min="1" max="60" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="online_users_min" class="form-label small fw-bold">Min Fluctuation Count</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-arrow-down"></i></span>
                                            <input type="number" class="form-control" id="online_users_min" name="online_users_min" 
                                                   value="{{ old('online_users_min', $settings->online_users_min ?? 420) }}" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="online_users_max" class="form-label small fw-bold">Max Fluctuation Count</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-arrow-up"></i></span>
                                            <input type="number" class="form-control" id="online_users_max" name="online_users_max" 
                                                   value="{{ old('online_users_max', $settings->online_users_max ?? 490) }}" min="1" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- App Preview Badge -->
                                <div class="mt-4 p-3 bg-light rounded d-flex align-items-center justify-content-between">
                                    <span class="small text-muted fw-semibold">App Preview:</span>
                                    <span class="badge d-inline-flex align-items-center py-2 px-3" style="background-color: #059669; font-size: 0.95rem; border-radius: 50rem;">
                                        <span class="spinner-grow spinner-grow-sm me-2 text-white" style="width: 8px; height: 8px;" role="status"></span>
                                        <strong id="previewOnlineCount">{{ $settings->online_users_base ?? 452 }}</strong>
                                        <span class="ms-1" style="color: #D1FAE5; font-size: 0.8rem;">Online</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Live Transaction Alerts Settings -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0 text-primary">
                                    <i class="bi bi-credit-card-2-front me-2"></i>Card 2: Live Activity Alerts
                                </h5>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="transaction_alerts_enabled" name="transaction_alerts_enabled" 
                                           {{ ($settings->transaction_alerts_enabled ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="transaction_alerts_enabled">Active in App</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Displays real-time withdrawals and deposits that gracefully slide and fade across the card on the app dashboard.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="transaction_alerts_interval" class="form-label small fw-bold">Message Switch Interval (seconds)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-stopwatch"></i></span>
                                            <input type="number" class="form-control" id="transaction_alerts_interval" name="transaction_alerts_interval" 
                                                   value="{{ old('transaction_alerts_interval', $settings->transaction_alerts_interval ?? 10) }}" min="3" max="60" required>
                                        </div>
                                        <small class="text-muted">Seconds between transitioning to the next withdrawal/deposit message.</small>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-light rounded">
                                    <div class="small text-muted fw-semibold mb-2">App Preview:</div>
                                    <div class="p-2 px-3 rounded shadow-sm bg-white border d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong style="color: #059669;">Ahmed Ali</strong> withdrew <strong style="color: #111827;">Rs. 5,000</strong>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.75rem;">just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Global Settings Button -->
                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Save Global Configurations
                    </button>
                </div>
            </form>

            <!-- 3. Transaction Messages Management Table -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="bi bi-chat-left-text text-dark me-2"></i>Live Transaction Messages
                            <span class="badge bg-secondary ms-2">{{ count($alerts) }} Messages</span>
                        </h4>
                        <small class="text-muted">These messages cycle sequentially on the mobile app's live activity card.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.live_alerts.reset_defaults') }}" method="POST" onsubmit="return confirm('Restore original 12 realistic Pakistani transaction alerts?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
                            </button>
                        </form>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#alertModal" onclick="openAddModal()">
                            <i class="bi bi-plus-circle me-1"></i> Add Transaction Alert
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th>Member Name</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Time Phrase</th>
                                    <th>Full Broadcast Message</th>
                                    <th class="text-center">Status</th>
                                    <th style="width: 140px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alerts as $index => $alert)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $alert->name }}</strong>
                                    </td>
                                    <td>
                                        @if (strtolower($alert->type) === 'withdrawal')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-arrow-up-right me-1"></i> Withdrawal
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                <i class="bi bi-arrow-down-left me-1"></i> Deposit
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">Rs. {{ number_format($alert->amount) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1"></i>{{ $alert->time_ago ?: 'just now' }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="text-dark">{{ $alert->display_message }}</code>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.live_alerts.toggle', $alert->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $alert->status === 'active' ? 'btn-success' : 'btn-outline-secondary' }} py-0 px-2" style="font-size: 0.75rem;">
                                                {{ ucfirst($alert->status) }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" 
                                                onclick="openEditModal({{ json_encode($alert) }})" title="Edit Alert">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.live_alerts.delete', $alert->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this alert?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Alert">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No transaction alerts configured yet.<br>
                                        <button type="button" class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#alertModal" onclick="openAddModal()">
                                            <i class="bi bi-plus-circle me-1"></i> Add First Alert
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.live_alerts.store') }}" method="POST" id="alertForm">
                @csrf
                <input type="hidden" name="alert_id" id="modal_alert_id" value="">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="alertModalLabel">Add Transaction Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_name" class="form-label small fw-bold">Member / User Name *</label>
                        <input type="text" class="form-control" id="modal_name" name="name" placeholder="e.g. Ahmed Ali" required maxlength="100">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="modal_type" class="form-label small fw-bold">Transaction Type *</label>
                            <select class="form-select" id="modal_type" name="type" required>
                                <option value="withdrawal">Withdrawal (Green)</option>
                                <option value="deposit">Deposit (Blue)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_amount" class="form-label small fw-bold">Amount (Rs.) *</label>
                            <input type="number" class="form-control" id="modal_amount" name="amount" placeholder="e.g. 5000" min="1" step="any" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="modal_time_ago" class="form-label small fw-bold">Time Phrase</label>
                            <input type="text" class="form-control" id="modal_time_ago" name="time_ago" placeholder="e.g. just now, 2 mins ago" maxlength="50" value="just now">
                            <small class="text-muted">Displays in bottom-right corner.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_status" class="form-label small fw-bold">Status *</label>
                            <select class="form-select" id="modal_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_custom_message" class="form-label small fw-bold">Custom Message Override (Optional)</label>
                        <input type="text" class="form-control" id="modal_custom_message" name="custom_message" placeholder="Leave empty for auto-generated message" maxlength="255">
                        <small class="text-muted">If left blank, auto-generates: <em>&quot;[Name] withdrew/deposited Rs. [Amount]&quot;</em></small>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="modalSubmitBtn">Save Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('alertModalLabel').innerText = 'Add Transaction Alert';
    document.getElementById('modalSubmitBtn').innerText = 'Save Alert';
    document.getElementById('modal_alert_id').value = '';
    document.getElementById('modal_name').value = '';
    document.getElementById('modal_type').value = 'withdrawal';
    document.getElementById('modal_amount').value = '';
    document.getElementById('modal_time_ago').value = 'just now';
    document.getElementById('modal_status').value = 'active';
    document.getElementById('modal_custom_message').value = '';
}

function openEditModal(alert) {
    document.getElementById('alertModalLabel').innerText = 'Edit Transaction Alert';
    document.getElementById('modalSubmitBtn').innerText = 'Update Alert';
    document.getElementById('modal_alert_id').value = alert.id;
    document.getElementById('modal_name').value = alert.name;
    document.getElementById('modal_type').value = alert.type.toLowerCase();
    document.getElementById('modal_amount').value = alert.amount;
    document.getElementById('modal_time_ago').value = alert.time_ago || 'just now';
    document.getElementById('modal_status').value = alert.status;
    document.getElementById('modal_custom_message').value = alert.custom_message || '';

    var modal = new bootstrap.Modal(document.getElementById('alertModal'));
    modal.show();
}

// Quick live preview update
document.getElementById('online_users_base')?.addEventListener('input', function(e) {
    var val = e.target.value;
    var preview = document.getElementById('previewOnlineCount');
    if (preview && val) preview.innerText = val;
});
</script>
@endsection

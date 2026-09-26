@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
    <i class="bi-chevron-right me-1 fs-6"></i>
    <span class="text-muted">Live Activity Alerts</span>

    <button type="button" class="btn btn-sm btn-dark float-lg-end mt-1 mt-lg-0 ms-2" data-bs-toggle="modal" data-bs-target="#alertModal" onclick="openAddModal()">
        <i class="bi-plus-lg"></i> Add Transaction Alert
    </button>
    <a href="{{ url('api/live-alerts') }}" target="_blank" class="btn btn-sm btn-outline-secondary float-lg-end mt-1 mt-lg-0">
        <i class="bi-code-slash me-1"></i> Preview Live API
    </a>
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

            @if (session('error_message'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            @endif

            @include('errors.errors-forms')

            <!-- Global Configurations Card -->
            <div class="card shadow-custom border-0 mb-4">
                <div class="card-body p-lg-4">
                    <form method="POST" action="{{ route('admin.live_alerts.settings') }}">
                        @csrf

                        <h6 class="mb-3 text-uppercase text-muted fw-bold">
                            <i class="bi bi-people me-1"></i> Online Users Configuration
                        </h6>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Active Users Card</label>
                            <div class="col-sm-9 d-flex align-items-center">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="online_users_enabled" name="online_users_enabled" {{ ($settings->online_users_enabled ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2" for="online_users_enabled">Active in App</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Base Starting Count</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="online_users_base" value="{{ old('online_users_base', $settings->online_users_base ?? 452) }}" min="1" required>
                                <small class="d-block text-muted">Baseline count displayed when app opens.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Fluctuation Range (Min - Max)</label>
                            <div class="col-sm-9">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="online_users_min" value="{{ old('online_users_min', $settings->online_users_min ?? 420) }}" min="1" placeholder="Min" required>
                                        <small class="d-block text-muted">Minimum active count</small>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="online_users_max" value="{{ old('online_users_max', $settings->online_users_max ?? 490) }}" min="1" placeholder="Max" required>
                                        <small class="d-block text-muted">Maximum active count</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Counter Update Interval</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="online_users_interval" value="{{ old('online_users_interval', $settings->online_users_interval ?? 6) }}" min="1" max="60" required>
                                <small class="d-block text-muted">Interval in seconds between subtle fluctuations.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Live App Preview</label>
                            <div class="col-sm-9">
                                <div class="p-3 rounded border" style="background-color: var(--bs-tertiary-bg, rgba(255, 255, 255, 0.03)); max-width: 450px;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="spinner-grow spinner-grow-sm text-success me-2" style="width: 10px; height: 10px;" role="status"></span>
                                            <span class="fw-semibold">Active Users</span>
                                        </div>
                                        <span class="badge bg-success py-2 px-3 rounded-pill" style="font-size: 0.9rem;">
                                            <strong id="previewOnlineCount">{{ $settings->online_users_base ?? 452 }}</strong>
                                            <span class="opacity-75 ms-1" style="font-size: 0.75rem;">Online</span>
                                        </span>
                                    </div>
                                </div>
                                <small class="d-block text-muted mt-1">Live appearance of the active users counter on the mobile dashboard.</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3 text-uppercase text-muted fw-bold">
                            <i class="bi bi-credit-card me-1"></i> Live Transaction Alerts Configuration
                        </h6>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Transaction Alerts Card</label>
                            <div class="col-sm-9 d-flex align-items-center">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="transaction_alerts_enabled" name="transaction_alerts_enabled" {{ ($settings->transaction_alerts_enabled ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2" for="transaction_alerts_enabled">Active in App</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Message Switch Interval</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="transaction_alerts_interval" value="{{ old('transaction_alerts_interval', $settings->transaction_alerts_interval ?? 10) }}" min="2" max="60" required>
                                <small class="d-block text-muted">Seconds between transitioning to the next withdrawal/deposit message.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Withdrawal Amount Bracket</label>
                            <div class="col-sm-9">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control" name="withdrawal_min_amount" value="{{ old('withdrawal_min_amount', $settings->withdrawal_min_amount ?? 2000) }}" min="1" placeholder="Min" required>
                                        </div>
                                        <small class="d-block text-muted">Minimum withdrawal alert amount</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control" name="withdrawal_max_amount" value="{{ old('withdrawal_max_amount', $settings->withdrawal_max_amount ?? 25000) }}" min="1" placeholder="Max" required>
                                        </div>
                                        <small class="d-block text-muted">Maximum withdrawal alert amount</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Deposit Amount Bracket</label>
                            <div class="col-sm-9">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control" name="deposit_min_amount" value="{{ old('deposit_min_amount', $settings->deposit_min_amount ?? 1000) }}" min="1" placeholder="Min" required>
                                        </div>
                                        <small class="d-block text-muted">Minimum deposit alert amount</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control" name="deposit_max_amount" value="{{ old('deposit_max_amount', $settings->deposit_max_amount ?? 20000) }}" min="1" placeholder="Max" required>
                                        </div>
                                        <small class="d-block text-muted">Maximum deposit alert amount</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-lg-end">Live App Preview</label>
                            <div class="col-sm-9">
                                <div class="p-3 rounded border" style="background-color: var(--bs-tertiary-bg, rgba(255, 255, 255, 0.03)); max-width: 450px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span id="previewTxnBadge" class="badge bg-success p-2 rounded-circle">
                                                <i class="bi bi-arrow-up-right"></i>
                                            </span>
                                            <div id="previewTxnMain" style="font-size: 0.95rem;">
                                                <span class="text-success fw-bold">Ahmed Ali</span> withdrew <span class="fw-bold">Rs. 5,000</span>
                                            </div>
                                        </div>
                                        <small class="text-muted ms-2" style="font-size: 0.75rem; white-space: nowrap;" id="previewTxnTime">just now</small>
                                    </div>
                                </div>
                                <small class="d-block text-muted mt-1">Live appearance of real-time transaction alerts sliding on the mobile dashboard.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-dark px-5">
                                    {{ __('admin.save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transaction Alerts Table Card -->
            <div class="card shadow-custom border-0">
                <div class="card-body p-lg-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase text-muted fw-bold m-0">
                            <i class="bi bi-chat-left-text me-1"></i> Live Transaction Messages ({{ count($alerts) }})
                        </h6>
                        <form action="{{ route('admin.live_alerts.reset_defaults') }}" method="POST" onsubmit="return confirm('Restore original 12 realistic Pakistani transaction alerts?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table table-hover">
                            <tbody>
                                @if (count($alerts) != 0)
                                <tr>
                                    <th class="active">#</th>
                                    <th class="active">Member Name</th>
                                    <th class="active">Type</th>
                                    <th class="active">Amount</th>
                                    <th class="active">Time Phrase</th>
                                    <th class="active">Broadcast Message</th>
                                    <th class="active text-center">Status</th>
                                    <th class="active text-center">Actions</th>
                                </tr>

                                @foreach ($alerts as $index => $alert)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $alert->name }}</td>
                                    <td>
                                        @if (strtolower($alert->type) === 'withdrawal')
                                            <span class="badge bg-success">Withdrawal</span>
                                        @else
                                            <span class="badge bg-primary">Deposit</span>
                                        @endif
                                    </td>
                                    <td>Rs. {{ number_format($alert->amount) }}</td>
                                    <td><span class="text-muted">{{ $alert->time_ago ?: 'just now' }}</span></td>
                                    <td>{{ $alert->display_message }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.live_alerts.toggle', $alert->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $alert->status === 'active' ? 'btn-success' : 'btn-secondary' }} py-0 px-2">
                                                {{ ucfirst($alert->status) }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" onclick='openEditModal(@json($alert))' class="text-reset fs-5 me-2" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.live_alerts.delete', $alert->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this alert?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link text-danger e-none fs-5 p-0" type="submit" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach

                                @else
                                <tr>
                                    <td colspan="8">
                                        <h5 class="text-center p-5 text-muted fw-light m-0">{{ trans('misc.no_results_found') }}</h5>
                                    </td>
                                </tr>
                                @endif
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
        <div class="modal-content">
            <form action="{{ route('admin.live_alerts.store') }}" method="POST" id="alertForm">
                @csrf
                <input type="hidden" name="alert_id" id="modal_alert_id" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Add Transaction Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_name" class="form-label">Member / User Name *</label>
                        <input type="text" class="form-control" id="modal_name" name="name" placeholder="e.g. Ahmed Ali" required maxlength="100">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="modal_type" class="form-label">Transaction Type *</label>
                            <select class="form-select" id="modal_type" name="type" required>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="deposit">Deposit</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_amount" class="form-label">Amount (Rs.) *</label>
                            <input type="number" class="form-control" id="modal_amount" name="amount" placeholder="e.g. 5000" min="1" step="any" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="modal_time_ago" class="form-label">Time Phrase</label>
                            <input type="text" class="form-control" id="modal_time_ago" name="time_ago" placeholder="e.g. just now, 2 mins ago" maxlength="50" value="just now">
                            <small class="d-block text-muted">Displays in bottom-right corner.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_status" class="form-label">Status *</label>
                            <select class="form-select" id="modal_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_custom_message" class="form-label">Custom Message Override (Optional)</label>
                        <input type="text" class="form-control" id="modal_custom_message" name="custom_message" placeholder="Leave empty for auto-generated message" maxlength="255">
                        <small class="d-block text-muted">Variables: <code>{name}</code> and <code>{amount}</code>. If empty, default is: <em>&quot;{name} withdrew/deposited {amount}&quot;</em></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark" id="modalSubmitBtn">Save Alert</button>
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

// Dynamic Real-time Online Users preview simulation
const baseInput = document.querySelector('input[name="online_users_base"]');
const minInput = document.querySelector('input[name="online_users_min"]');
const maxInput = document.querySelector('input[name="online_users_max"]');
const intervalInput = document.querySelector('input[name="online_users_interval"]');
const preview = document.getElementById('previewOnlineCount');

let simCount = parseInt(baseInput ? baseInput.value : 2000) || 2000;
let simTarget = simCount;

function pickNewTarget(minVal, maxVal) {
    const range = maxVal - minVal;
    if (range <= 0) return minVal;
    const margin = Math.round(range * 0.1);
    return Math.floor(minVal + margin + Math.random() * (range - 2 * margin));
}

function updateSimulatedOnlineCount() {
    const minVal = parseInt(minInput ? minInput.value : 1900) || 1900;
    const maxVal = parseInt(maxInput ? maxInput.value : 3000) || 3000;
    const range = Math.max(10, maxVal - minVal);

    if (simTarget < minVal || simTarget > maxVal || Math.abs(simCount - simTarget) < 15 || Math.random() < 0.15) {
        simTarget = pickNewTarget(minVal, maxVal);
    }

    const stepMax = Math.max(2, Math.min(25, Math.round(range / 80)));
    const step = Math.floor(1 + Math.random() * stepMax);

    let delta = 0;
    if (simCount < simTarget) {
        delta = Math.random() < 0.72 ? step : -Math.round(step * 0.5);
    } else {
        delta = Math.random() < 0.72 ? -step : Math.round(step * 0.5);
    }

    simCount += delta;
    if (simCount < minVal) simCount = minVal + Math.floor(Math.random() * 5);
    if (simCount > maxVal) simCount = maxVal - Math.floor(Math.random() * 5);

    if (preview) preview.innerText = simCount;
}

if (preview) {
    setInterval(updateSimulatedOnlineCount, 3000);
}

if (baseInput) {
    baseInput.addEventListener('input', function(e) {
        const val = parseInt(e.target.value);
        if (val) {
            simCount = val;
            simTarget = val;
            if (preview) preview.innerText = simCount;
        }
    });
}

// Live cycling preview of transaction alerts
const alertItems = @json($alerts);
let previewIndex = 0;
if (alertItems && alertItems.length > 0) {
    setInterval(function() {
        previewIndex = (previewIndex + 1) % alertItems.length;
        const item = alertItems[previewIndex];
        const isWithdrawal = (item.type || '').toLowerCase() === 'withdrawal';
        
        const badge = document.getElementById('previewTxnBadge');
        const main = document.getElementById('previewTxnMain');
        const time = document.getElementById('previewTxnTime');
        
        if (badge) {
            badge.className = 'badge p-2 rounded-circle ' + (isWithdrawal ? 'bg-success' : 'bg-primary');
            badge.innerHTML = '<i class="bi ' + (isWithdrawal ? 'bi-arrow-up-right' : 'bi-arrow-down-left') + '"></i>';
        }
        if (main) {
            const nameColor = isWithdrawal ? 'text-success' : 'text-primary';
            const action = isWithdrawal ? ' withdrew ' : ' deposited ';
            main.innerHTML = '<span class="' + nameColor + ' fw-bold">' + item.name + '</span>' + action + '<span class="fw-bold">Rs. ' + Number(item.amount).toLocaleString() + '</span>';
        }
        if (time) {
            time.innerText = item.time_ago || 'just now';
        }
    }, 4000);
}
</script>
@endsection

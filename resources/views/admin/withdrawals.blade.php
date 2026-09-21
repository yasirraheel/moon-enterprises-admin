@extends('admin.layout')

@section('content')
@php
    $pageTitle = $filterDealers ?? false ? 'Dealer Withdrawals' : __('admin.withdrawals');
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-coin me-2"></i>
                        {{ $pageTitle }}
                    </h5>
                    @if($allWithdrawals->count() > 0)
                    <form action="{{ route('admin.withdrawals.delete-all') }}" method="POST" class="d-inline-block">
                        @csrf
                        <input type="hidden" name="confirm" value="DELETE ALL WITHDRAWALS">
                        @if($filterDealers ?? false)
                          <input type="hidden" name="filter_dealers" value="1">
                        @endif
                        <button type="button" class="btn btn-danger btn-sm actionDelete">
                            <i class="bi bi-trash me-1"></i>Delete All {{$filterDealers ?? false ? 'Dealer' : ''}} ({{$allWithdrawals->count()}})
                        </button>
                    </form>
                @endif

                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($filterDealers ?? false)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="alert alert-info mb-0 py-2 px-3">
                            <i class="bi bi-info-circle me-2"></i>Showing withdrawals from dealers only
                        </div>

                        @if(isset($dealers))
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dealerFilterBtn">
                                 <i class="bi bi-person-badge me-1"></i>
                                 {{ request('dealer_id') ? ($dealers->firstWhere('id', request('dealer_id'))->username ?? 'Unknown Dealer') : 'Filter by Dealer' }}
                            </button>
                            <div class="dropdown-menu p-2" style="min-width: 300px; right: 0; left: auto;">
                                <input type="text" class="form-control form-control-sm mb-2" id="dealerSearchInput" placeholder="Search dealer..." onclick="event.stopPropagation()">
                                <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;" id="dealerList">
                                   <a href="{{ request()->fullUrlWithQuery(['dealer_id' => null]) }}" class="list-group-item list-group-item-action py-2 dealer-item">
                                       <i class="bi bi-people me-2"></i>All Dealers
                                   </a>
                                   @foreach($dealers as $dealer)
                                     <a href="{{ request()->fullUrlWithQuery(['dealer_id' => $dealer->id]) }}" class="list-group-item list-group-item-action py-2 dealer-item" data-search="{{ strtolower($dealer->username . ' ' . $dealer->phone) }}">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <img src="{{ $dealer->avatar_url }}" class="rounded-circle" width="25" height="25" alt="">
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $dealer->username }}</div>
                                                <small class="text-muted">{{ $dealer->phone }}</small>
                                            </div>
                                        </div>
                                     </a>
                                   @endforeach
                                </div>
                            </div>
                        </div>
                        <script>
                            document.getElementById('dealerSearchInput').addEventListener('keyup', function() {
                                let filter = this.value.toLowerCase();
                                let items = document.querySelectorAll('.dealer-item');

                                items.forEach(function(item) {
                                    let text = item.getAttribute('data-search');
                                    if (!text || text.includes(filter)) {
                                        item.style.display = '';
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                            });
                        </script>
                        @endif
                    </div>
                    @endif

                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs mb-4" id="withdrawalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                All Withdrawals ({{ $allWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                Pending ({{ $pendingWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                                Approved ({{ $approvedWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                                Rejected ({{ $rejectedWithdrawals->count() }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="withdrawalTabsContent">
                        <!-- All Withdrawals -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Account Details</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="bi bi-person text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $withdrawal->user->username }}</div>
                                                            <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">${{ number_format($withdrawal->amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold">{{ $withdrawal->account_title }}</div>
                                                        <small class="text-muted">{{ $withdrawal->bank_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $withdrawal->account_number }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($withdrawal->status == 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($withdrawal->status == 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @else
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>
                                                <td>{{ $withdrawal->date->format('M d, Y H:i') }}</td>
                                                <td>
                                                    @if($withdrawal->status == 'pending')
                                                        <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $withdrawal->id }}">
                                                            <i class="bi bi-check"></i> Approve
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $withdrawal->id }}">
                                                            <i class="bi bi-x"></i> Reject
                                                        </button>
                                                    @else
                                                        <span class="text-muted">Processed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                                    <p class="text-muted mt-2">No withdrawals found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $allWithdrawals->links() }}
                        </div>

                        <!-- Pending Withdrawals -->
                        <div class="tab-pane fade" id="pending" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Account Details</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="bi bi-person text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $withdrawal->user->username }}</div>
                                                            <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">${{ number_format($withdrawal->amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold">{{ $withdrawal->account_title }}</div>
                                                        <small class="text-muted">{{ $withdrawal->bank_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $withdrawal->account_number }}</small>
                                                    </div>
                                                </td>
                                                <td>{{ $withdrawal->date->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $withdrawal->id }}">
                                                        <i class="bi bi-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $withdrawal->id }}">
                                                        <i class="bi bi-x"></i> Reject
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                                    <p class="text-muted mt-2">No pending withdrawals</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $pendingWithdrawals->links() }}
                        </div>

                        <!-- Approved Withdrawals -->
                        <div class="tab-pane fade" id="approved" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Account Details</th>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($approvedWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="bi bi-person text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $withdrawal->user->username }}</div>
                                                            <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">${{ number_format($withdrawal->amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold">{{ $withdrawal->account_title }}</div>
                                                        <small class="text-muted">{{ $withdrawal->bank_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $withdrawal->account_number }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">{{ $withdrawal->transaction_id }}</span>
                                                </td>
                                                <td>{{ $withdrawal->date->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                                    <p class="text-muted mt-2">No approved withdrawals</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $approvedWithdrawals->links() }}
                        </div>

                        <!-- Rejected Withdrawals -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Account Details</th>
                                            <th>Admin Notes</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rejectedWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="bi bi-person text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $withdrawal->user->username }}</div>
                                                            <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">${{ number_format($withdrawal->amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold">{{ $withdrawal->account_title }}</div>
                                                        <small class="text-muted">{{ $withdrawal->bank_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $withdrawal->account_number }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $withdrawal->admin_notes }}</small>
                                                </td>
                                                <td>{{ $withdrawal->date->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                                    <p class="text-muted mt-2">No rejected withdrawals</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $rejectedWithdrawals->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
@foreach($pendingWithdrawals as $withdrawal)
    <div class="modal fade" id="approveModal{{ $withdrawal->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.withdrawals.approve') }}" method="POST">
                    @csrf
                    <input type="hidden" name="withdrawal_id" value="{{ $withdrawal->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Transaction ID <span class="text-danger">*</span></label>
                            <input type="text" name="transaction_id" class="form-control" required placeholder="Enter transaction ID">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Notes (Optional)</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes about this withdrawal approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Withdrawal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal{{ $withdrawal->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.withdrawals.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="withdrawal_id" value="{{ $withdrawal->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reason for Rejection</label>
                            <textarea name="admin_notes" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Withdrawal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endsection

@section('css')
<style>
/* Ensure file input is visible */
input[type="file"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
}

/* Custom file input styling */
.custom-file-input {
    position: relative;
    display: inline-block;
    cursor: pointer;
    outline: none;
}

.custom-file-input input[type=file] {
    position: absolute;
    left: -9999px;
}

.custom-file-input-label {
    display: inline-block;
    padding: 8px 12px;
    background-color: #007bff;
    color: white;
    border-radius: 4px;
    cursor: pointer;
}

.custom-file-input-label:hover {
    background-color: #0056b3;
}
</style>
@endsection

@section('js')
<script>
// Make file inputs visible
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        input.style.display = 'block';
        input.style.visibility = 'visible';
        input.style.opacity = '1';
        input.style.position = 'relative';
        input.style.zIndex = '999';
    });
});
</script>
@endsection



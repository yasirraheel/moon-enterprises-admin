@extends('admin.layout')

@section('content')
@php
    $pageTitle = $filterDealers ?? false ? 'Dealer Deposits' : __('admin.deposits');
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>
                        {{ $pageTitle }}
                    </h5>
                    @if($allDeposits->count() > 0)
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAllDepositsModal">
                        <i class="bi bi-trash me-1"></i>Delete All ({{$allDeposits->count()}})
                    </button>
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
                            <i class="bi bi-info-circle me-2"></i>Showing deposits from dealers only
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
                    <ul class="nav nav-tabs mb-4" id="depositTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                All Deposits ({{ $allDeposits->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                Pending ({{ $pendingDeposits->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                                Approved ({{ $approvedDeposits->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                                Rejected ({{ $rejectedDeposits->count() }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="depositTabsContent">
                        <!-- All Deposits -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            @include('admin.partials.deposits-table', ['deposits' => $allDeposits])
                        </div>

                        <!-- Pending Deposits -->
                        <div class="tab-pane fade" id="pending" role="tabpanel">
                            @include('admin.partials.deposits-table', ['deposits' => $pendingDeposits])
                        </div>

                        <!-- Approved Deposits -->
                        <div class="tab-pane fade" id="approved" role="tabpanel">
                            @include('admin.partials.deposits-table', ['deposits' => $approvedDeposits])
                        </div>

                        <!-- Rejected Deposits -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel">
                            @include('admin.partials.deposits-table', ['deposits' => $rejectedDeposits])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Action Modal -->
<div class="modal fade" id="depositActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositActionModalLabel">Deposit Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="depositActionForm" method="POST" target="_self">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="deposit_id" name="deposit_id">
                    <input type="hidden" id="action_type" name="action_type">

                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Add notes about this deposit..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitActionBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Proof Modal -->
<div class="modal fade" id="paymentProofModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="proof-content">
                    <!-- Payment proof will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveDeposit(id) {
    document.getElementById('deposit_id').value = id;
    document.getElementById('action_type').value = 'approve';
    document.getElementById('depositActionModalLabel').textContent = 'Approve Deposit';
    document.getElementById('submitActionBtn').textContent = 'Approve';
    document.getElementById('submitActionBtn').className = 'btn btn-success';
    document.getElementById('depositActionForm').action = '{{ url("panel/admin/deposits/approve") }}';

    const modal = new bootstrap.Modal(document.getElementById('depositActionModal'));
    modal.show();
}

function rejectDeposit(id) {
    document.getElementById('deposit_id').value = id;
    document.getElementById('action_type').value = 'reject';
    document.getElementById('depositActionModalLabel').textContent = 'Reject Deposit';
    document.getElementById('submitActionBtn').textContent = 'Reject';
    document.getElementById('submitActionBtn').className = 'btn btn-danger';
    document.getElementById('depositActionForm').action = '{{ url("panel/admin/deposits/reject") }}';

    const modal = new bootstrap.Modal(document.getElementById('depositActionModal'));
    modal.show();
}

function viewPaymentProof(proofPath) {
    const proofContent = document.getElementById('proof-content');

    // Check if it's an image or PDF
    const extension = proofPath.split('.').pop().toLowerCase();

    if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
        proofContent.innerHTML = `<img src="{{ url('public/deposits') }}/${proofPath}" class="img-fluid" alt="Payment Proof">`;
    } else if (extension === 'pdf') {
        proofContent.innerHTML = `
            <iframe src="{{ url('public/deposits') }}/${proofPath}" width="100%" height="500px" style="border: none;"></iframe>
            <div class="mt-3">
                <a href="{{ url('public/deposits') }}/${proofPath}" target="_blank" class="btn btn-primary">
                    <i class="bi bi-download me-2"></i>Download PDF
                </a>
            </div>
        `;
    } else {
        proofContent.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                File type not supported for preview.
                <a href="{{ url('public/deposits') }}/${proofPath}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="bi bi-download me-1"></i>Download
                </a>
            </div>
        `;
    }

    const modal = new bootstrap.Modal(document.getElementById('paymentProofModal'));
    modal.show();
}
</script>

<!-- Delete All Deposits Modal -->
<div class="modal fade" id="deleteAllDepositsModal" tabindex="-1" aria-labelledby="deleteAllDepositsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteAllDepositsModalLabel">
          <i class="bi bi-exclamation-triangle me-2"></i>Delete All Deposits
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.deposits.delete-all') }}" method="post" id="deleteAllDepositsForm">
        @csrf
        @if($filterDealers ?? false)
          <input type="hidden" name="filter_dealers" value="1">
        @endif
        <div class="modal-body">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>⚠️ CRITICAL WARNING!</strong>
            This action will permanently delete ALL {{$filterDealers ?? false ? 'DEALER' : ''}} deposits!
          </div>

          <p><strong>This action cannot be undone and will affect:</strong></p>
          <ul class="list-unstyled">
            <li><i class="bi bi-x-circle text-danger me-2"></i>All {{$filterDealers ?? false ? 'dealer' : ''}} deposit history and records</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>User balance calculations</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>Financial reports and analytics</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>Payment proof files and references</li>
          </ul>

          <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Impact:</strong> This will affect {{$allDeposits->count()}} deposit records.
          </div>

          <div class="mb-3">
            <label for="confirmDeleteAllDeposits" class="form-label">
              <strong>Type <code>DELETE ALL DEPOSITS</code> to confirm:</strong>
            </label>
            <input type="text" class="form-control" id="confirmDeleteAllDeposits" name="confirm"
                   placeholder="DELETE ALL DEPOSITS" required autocomplete="off">
            <div class="form-text text-muted">
              You must type the exact text above to proceed with deletion.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-danger" id="confirmDeleteAllDepositsBtn" disabled>
            <i class="bi bi-trash me-1"></i>Delete All {{$allDeposits->count()}} Deposits
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Handle delete all deposits modal
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmDeleteAllDeposits');
    const confirmBtn = document.getElementById('confirmDeleteAllDepositsBtn');
    const deleteForm = document.getElementById('deleteAllDepositsForm');

    if (confirmInput && confirmBtn && deleteForm) {
        // Enable/disable confirm button based on input
        confirmInput.addEventListener('input', function() {
            const requiredText = 'DELETE ALL DEPOSITS';
            if (this.value === requiredText) {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        });

        // Handle form submission with loading state
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
            confirmBtn.disabled = true;

            // Submit the form
            this.submit();
        });

        // Reset form when modal is hidden
        const modal = document.getElementById('deleteAllDepositsModal');
        modal.addEventListener('hidden.bs.modal', function() {
            confirmInput.value = '';
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete All {{$allDeposits->count()}} Deposits';
        });
    }
});
</script>
@endsection

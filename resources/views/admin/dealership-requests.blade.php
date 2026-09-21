@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <h5 class="mb-4 fw-light">
        <a class="text-reset" href="{{ url('panel/admin') }}">Dashboard</a>
        <i class="bi-chevron-right me-1 fs-6"></i>
        <span class="text-muted">Dealership Requests ({{$allRequests->total()}})</span>
    </h5>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-custom border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-shop me-2"></i>
                        Dealership Applications
                    </h5>
                </div>
                <div class="card-body p-lg-4">
                    @if(session('success_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error_message'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('error_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs mb-4" id="requestTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                All Requests ({{ $allRequests->total() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                Pending ({{ $pendingRequests->total() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                                Approved ({{ $approvedRequests->total() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                                Rejected ({{ $rejectedRequests->total() }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="requestTabsContent">
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            @include('admin.partials.dealership-requests-table', ['requests' => $allRequests])
                        </div>
                        <div class="tab-pane fade" id="pending" role="tabpanel">
                            @include('admin.partials.dealership-requests-table', ['requests' => $pendingRequests])
                        </div>
                        <div class="tab-pane fade" id="approved" role="tabpanel">
                            @include('admin.partials.dealership-requests-table', ['requests' => $approvedRequests])
                        </div>
                        <div class="tab-pane fade" id="rejected" role="tabpanel">
                            @include('admin.partials.dealership-requests-table', ['requests' => $rejectedRequests])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dealership_requests.approve') }}">
                @csrf
                <input type="hidden" name="request_id" id="approve_request_id">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Dealership Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dealer Commission (%)</label>
                        <input type="number" class="form-control" name="commission" step="0.01" min="0" max="100" required placeholder="Enter commission percentage">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dealership_requests.reject') }}">
                @csrf
                <input type="hidden" name="request_id" id="reject_request_id">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Dealership Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason (Required)</label>
                        <textarea class="form-control" name="admin_notes" rows="3" placeholder="Explain why this request is rejected..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveRequest(requestId) {
    document.getElementById('approve_request_id').value = requestId;
}

function rejectRequest(requestId) {
    document.getElementById('reject_request_id').value = requestId;
}
</script>
@endsection

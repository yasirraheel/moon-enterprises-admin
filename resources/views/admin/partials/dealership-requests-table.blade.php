@if($requests->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Username</th>
                <th>Phone</th>
                <th>Joined Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>
                    <a href="{{ url('panel/admin/members') }}?q={{ $user->username }}" target="_blank">
                        {{ $user->full_name }}
                    </a>
                </td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->phone }}</td>
                <td>{{ $user->date }}</td>
                <td>
                    @if($user->dealer_status == 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($user->dealer_status == 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($user->dealer_status == 'rejected')
                        <span class="badge bg-danger">Rejected</span>
                    @else
                        <span class="badge bg-secondary">NA</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $user->id }}">
                        <i class="bi bi-eye"></i> View
                    </button>
                    @if($user->dealer_status == 'pending')
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="approveRequest({{ $user->id }})">
                            <i class="bi bi-check"></i> Approve
                        </button>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="rejectRequest({{ $user->id }})">
                            <i class="bi bi-x"></i> Reject
                        </button>
                    @endif
                </td>
            </tr>

            <!-- View Details Modal -->
            <div class="modal fade" id="viewModal{{ $user->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Dealership Request Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>User:</strong> {{ $user->full_name }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Username:</strong> {{ $user->username }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Phone:</strong> {{ $user->phone }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Status:</strong>
                                    @if($user->dealer_status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($user->dealer_status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($user->dealer_status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">NA</span>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Joined At:</strong> {{ $user->date }}
                                </div>
                                @if($user->dealer_status == 'approved')
                                <div class="col-md-6 mb-3">
                                    <strong>Commission:</strong> {{ $user->dealer_commission }}%
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-4">
        {{ $requests->links() }}
    </div>
</div>
@else
<div class="alert alert-info">
    No dealership requests found.
</div>
@endif

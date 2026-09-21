@if($deposits->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>User</th>
                <th>Payment Method</th>
                <th>Amount</th>         <th>Transaction ID</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposits as $deposit)
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <div class="bg-light d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;">
                              <i class="bi bi-person text-muted"></i>
                            </div>
                        </div>
                        <div>
                            @if($deposit->user)
                                <div class="fw-bold">{{ $deposit->user->full_name ?? $deposit->user->username ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $deposit->user->phone ?? 'N/A' }}</small>
                            @else
                                <div class="fw-bold text-muted">User Deleted</div>
                                <small class="text-muted">User ID: {{ $deposit->user_id ?? 'N/A' }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        @if($deposit->paymentMethod && $deposit->paymentMethod->bank_image)
                            <img src="{{ url('public/img', $deposit->paymentMethod->bank_image) }}" alt="Bank" class="me-2" width="24" height="24">
                        @else
                            <i class="bi bi-bank me-2 text-muted"></i>
                        @endif
                        <div>
                            @if($deposit->paymentMethod)
                                <div class="fw-bold">{{ $deposit->paymentMethod->bank_or_account_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $deposit->paymentMethod->account_title ?? 'N/A' }}</small>
                            @else
                                <div class="fw-bold text-muted">Payment Method Deleted</div>
                                <small class="text-muted">Method ID: {{ $deposit->payment_method_id ?? 'N/A' }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <span class="fw-bold text-success">{{ number_format($deposit->amount, 2) }} {{ __('misc.currency') }}</span>
                </td>
                <td>
                    <code class="font-monospace">{{ $deposit->transaction_id }}</code>
                </td>
                <td>
                    @if($deposit->status == 'pending')
                        <span class="badge bg-warning">
                            <i class="bi bi-clock me-1"></i>Pending
                        </span>
                    @elseif($deposit->status == 'approved')
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>Approved
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle me-1"></i>Rejected
                        </span>
                    @endif
                </td>
                <td>
                    <div>{{ $deposit->date ? \Carbon\Carbon::parse($deposit->date)->format('M d, Y') : 'N/A' }}</div>
                    <small class="text-muted">{{ $deposit->date ? \Carbon\Carbon::parse($deposit->date)->format('h:i A') : 'N/A' }}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        @if($deposit->payment_proof)
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewPaymentProof('{{ $deposit->payment_proof }}')" title="View Payment Proof">
                                <i class="bi bi-eye"></i>
                            </button>
                        @endif

                        @if($deposit->status == 'pending')
                            <button type="button" class="btn btn-sm btn-success" onclick="approveDeposit({{ $deposit->id }})" title="Approve">
                                <i class="bi bi-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectDeposit({{ $deposit->id }})" title="Reject">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>

                    @if($deposit->admin_notes)
                        <div class="mt-1">
                            <small class="text-muted" title="{{ $deposit->admin_notes }}">
                                <i class="bi bi-chat-text me-1"></i>Has notes
                            </small>
                        </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($deposits->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $deposits->links() }}
</div>
@endif

@else
<div class="text-center py-5">
    <i class="bi bi-inbox display-1 text-muted"></i>
    <h5 class="text-muted mt-3">No deposits found</h5>
    <p class="text-muted">There are no deposits matching the current filter.</p>
</div>
@endif

@extends('admin.layout')

@section('content')
@php
    $baseUrl = ($filterDealers ?? false) ? url('panel/admin/dealer-transactions') : url('panel/admin/transactions');
@endphp
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">
        Transactions ({{$data->total()}})
      </span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

      @if (session('info_message'))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <i class="bi-exclamation-triangle me-1"></i>	{{ session('info_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

			@if (session('success_message'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

          <div class="d-inline-block mb-2 w-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <!-- Filters -->
                <div class="d-flex gap-2 mb-3">
                  <select name="user_filter" class="form-select form-select-sm" style="width: 200px;" onchange="filterTransactions()">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}" @if(request('user_id') == $user->id) selected @endif>
                        {{ $user->username }} ({{ $user->phone }})
                      </option>
                    @endforeach
                  </select>

                  <select name="transaction_type_filter" class="form-select form-select-sm" style="width: 150px;" onchange="filterTransactions()">
                    <option value="">All Types</option>
                    <option value="deposit" @if(request('transaction_type') == 'deposit') selected @endif>Deposit</option>
                    <option value="withdrawal" @if(request('transaction_type') == 'withdrawal') selected @endif>Withdrawal</option>
                    <option value="paid_service" @if(request('transaction_type') == 'paid_service') selected @endif>Paid Service</option>
                    <option value="order_placed" @if(request('transaction_type') == 'order_placed') selected @endif>Order Placed</option>
                    <option value="admin_credit" @if(request('transaction_type') == 'admin_credit') selected @endif>Admin Credit</option>
                    <option value="admin_debit" @if(request('transaction_type') == 'admin_debit') selected @endif>Admin Debit</option>
                    <option value="signup_bonus" @if(request('transaction_type') == 'signup_bonus') selected @endif>Signup Bonus</option>
                    <option value="refund" @if(request('transaction_type') == 'refund') selected @endif>Refund</option>
                  </select>

                  <select name="type_filter" class="form-select form-select-sm" style="width: 120px;" onchange="filterTransactions()">
                    <option value="">All</option>
                    <option value="credit" @if(request('type') == 'credit') selected @endif>Credit</option>
                    <option value="debit" @if(request('type') == 'debit') selected @endif>Debit</option>
                  </select>

                  @if($data->total() > 0)
                  <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAllTransactionsModal">
                    <i class="bi bi-trash me-1"></i>Delete All ({{$data->total()}})
                  </button>
                  @endif
                </div>
              </div>
            </div>

          @if ($data->count() != 0)
            <!-- Search form -->
            <form role="search" autocomplete="off" action="{{ url('panel/admin/transactions') }}" method="get" class="position-relative mb-3">
							<i class="bi bi-search btn-search bar-search"></i>
             <input type="text" name="q" class="form-control ps-5" placeholder="Search transactions..." value="{{ request('q') }}">
          </form><!-- Search form -->
            @endif
          </div>

					<div class="table-responsive p-0">
						<table class="table table-hover">
						 <tbody>

               @if ($data->total() !=  0 && $data->count() != 0)
                  <tr>
                     <th class="active">User</th>
                     <th class="active">Type</th>
                     <th class="active">Transaction Type</th>
                     <th class="active">Current Balance</th>
                     <th class="active">Amount</th>
                     <th class="active">Remaining Balance</th>
                     <th class="active">Description</th>
                     <th class="active">Reference</th>
                     <th class="active">Date</th>
                   </tr>

                 @foreach ($data as $transaction)
                   <tr>
                     <td>
                       <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle me-1" style="width: 40px; height: 40px;">
                         <i class="bi bi-person text-muted"></i>
                       </div>
                       {{ $transaction->user->username ?? 'User Deleted' }}<br>
                       <small class="text-muted">{{ $transaction->user->phone ?? 'N/A' }}</small>
                     </td>
                     <td>
                       @if($transaction->type == 'credit')
                         <span class="badge bg-success">Credit</span>
                       @else
                         <span class="badge bg-danger">Debit</span>
                       @endif
                     </td>
                     <td>
                       <span class="badge bg-info">{{ $transaction->transaction_type_label }}</span>
                     </td>
                     <td class="text-end">Rs. {{ $transaction->formatted_current_balance }}</td>
                     <td class="text-end">
                       @if($transaction->type == 'credit')
                         <span class="text-success">+Rs. {{ $transaction->formatted_amount }}</span>
                       @else
                         <span class="text-danger">-Rs. {{ $transaction->formatted_amount }}</span>
                       @endif
                     </td>
                     <td class="text-end">Rs. {{ $transaction->formatted_remaining_balance }}</td>
                     <td>{{ $transaction->description }}</td>
                     <td>
                       @if($transaction->reference_id)
                         <span class="badge bg-secondary">{{ $transaction->reference_type }} #{{ $transaction->reference_id }}</span>
                       @else
                         <span class="text-muted">-</span>
                       @endif
                     </td>
                     <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                   </tr>
                 @endforeach

               @else
                 <tr>
                   <td colspan="9" class="text-center py-4">
                     <div class="text-muted">
                       <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                       No transactions found
                     </div>
                   </td>
                 </tr>
               @endif

             </tbody>
           </table>
         </div>

         @if ($data->hasPages())
           <div class="d-flex justify-content-center mt-4">
             {{ $data->links() }}
           </div>
         @endif

				</div>
			</div>
		</div>
	</div>
</div>

<!-- Delete All Transactions Modal -->
<div class="modal fade" id="deleteAllTransactionsModal" tabindex="-1" aria-labelledby="deleteAllTransactionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteAllTransactionsModalLabel">
          <i class="bi bi-exclamation-triangle me-2"></i>Delete All Transactions
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.transactions.delete-all') }}" method="post" id="deleteAllTransactionsForm">
        @csrf
        <div class="modal-body">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>⚠️ CRITICAL WARNING!</strong>
            This action will permanently delete ALL {{$data->total()}} transactions!
          </div>

          <p><strong>This action cannot be undone and will affect:</strong></p>
          <ul class="list-unstyled">
            <li><i class="bi bi-x-circle text-danger me-2"></i>All transaction history</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>User balance calculations</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>Financial reports and analytics</li>
            <li><i class="bi bi-x-circle text-danger me-2"></i>Audit trails and records</li>
          </ul>

          <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Impact:</strong> This will affect {{$data->total()}} transaction records across all users.
          </div>

          <div class="mb-3">
            <label for="confirmDeleteAll" class="form-label">
              <strong>Type <code>DELETE ALL TRANSACTIONS</code> to confirm:</strong>
            </label>
            <input type="text" class="form-control" id="confirmDeleteAll" name="confirm"
                   placeholder="DELETE ALL TRANSACTIONS" required autocomplete="off">
            <div class="form-text text-muted">
              You must type the exact text above to proceed with deletion.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-danger" id="confirmDeleteAllBtn" disabled>
            <i class="bi bi-trash me-1"></i>Delete All {{$data->total()}} Transactions
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterTransactions() {
    const userId = document.querySelector('select[name="user_filter"]').value;
    const transactionType = document.querySelector('select[name="transaction_type_filter"]').value;
    const type = document.querySelector('select[name="type_filter"]').value;

    const params = new URLSearchParams();
    if (userId) params.append('user_id', userId);
    if (transactionType) params.append('transaction_type', transactionType);
    if (type) params.append('type', type);

    const url = '{{ $baseUrl }}' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Handle delete all transactions modal
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmDeleteAll');
    const confirmBtn = document.getElementById('confirmDeleteAllBtn');
    const deleteForm = document.getElementById('deleteAllTransactionsForm');

    // Enable/disable confirm button based on input
    confirmInput.addEventListener('input', function() {
        const requiredText = 'DELETE ALL TRANSACTIONS';
        if (this.value === requiredText) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('btn-danger');
            confirmBtn.classList.add('btn-danger');
        } else {
            confirmBtn.disabled = true;
        }
    });

    // Handle form submission with loading state
    deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading state
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
        confirmBtn.disabled = true;

        // Submit the form
        this.submit();
    });

    // Reset form when modal is hidden
    const modal = document.getElementById('deleteAllTransactionsModal');
    modal.addEventListener('hidden.bs.modal', function() {
        confirmInput.value = '';
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete All {{$data->total()}} Transactions';
    });
});
</script>

@endsection

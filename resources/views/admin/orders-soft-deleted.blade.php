@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <a class="text-reset" href="{{ url('panel/admin/orders') }}">{{ __('admin.orders') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Soft Deleted Orders ({{$data->total()}})</span>
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
                <span class="badge bg-warning me-2">Soft Deleted Orders</span>
                <small class="text-muted">These orders can be restored or permanently deleted</small>
              </div>

              <div>
                <form action="{{ route('orders.restore.bulk') }}" method="POST" id="bulkRestoreForm" class="d-none">
                  @csrf
                  <input type="hidden" name="ids[]" id="bulkRestoreIds">
                </form>
                <button type="button" class="btn btn-success btn-sm me-2" id="btnRestoreSelected" disabled>
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Selected
                </button>
                <button type="button" class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                  <i class="bi bi-trash me-1"></i> Delete All
                </button>
                <a href="{{ url('panel/admin/orders') }}" class="btn btn-primary btn-sm">
                  <i class="bi bi-arrow-left me-1"></i> Back to Orders
                </a>
              </div>
            </div>

          @if ($data->count() != 0)
            <!-- form -->
            <form role="search" autocomplete="off" action="{{ url('panel/admin/orders/soft-deleted') }}" method="get" class="position-relative">
							<i class="bi bi-search btn-search bar-search"></i>
             <input type="text" name="q" class="form-control ps-5" placeholder="{{ __('misc.search') }}" value="{{ request('q') }}">
          </form><!-- form -->
            @endif
          </div>

					<div class="table-responsive p-0">
						<table class="table table-hover">
						 <tbody>

               @if ($data->total() !=  0 && $data->count() != 0)
                  <tr>
                     <th class="active">
                       <input type="checkbox" class="form-check-input" id="selectAll">
                     </th>
                     <th class="active">ID</th>
                     <th class="active">Original ID</th>
                     <th class="active">{{ __('admin.username') }}</th>
                     <th class="active">{{ __('admin.user_phone') }}</th>
                     <th class="active">{{ __('admin.game_name') }}</th>
                     <th class="active">{{ __('admin.rttp') }}</th>
                     <th class="active">{{ __('admin.first') }}</th>
                     <th class="active">{{ __('admin.second') }}</th>
                     <th class="active">Deleted At</th>
                     <th class="active">{{ __('admin.actions') }}</th>
                   </tr>

                 @foreach ($data as $order)
                   <tr>
                     <td>
                       <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}">
                     </td>
                     <td>{{ $order->id }}</td>
                     <td>{{ $order->original_id }}</td>
                     <td>
                       <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle me-1" style="width: 40px; height: 40px;">
                         <i class="bi bi-person text-muted"></i>
                       </div> {{ $order->username }}
                     </td>
                     <td>{{ $order->user_phone }}</td>
                     <td>{{ $order->game_name }}</td>
                     <td>{{ $order->rttp }}</td>
                     <td>{{ $order->first }}</td>
                     <td>{{ $order->second }}</td>
                     <td>{{ Helper::formatDate($order->deleted_at) }}</td>
                     <td>
                       <div class="d-flex gap-1">
                         <form action="{{ route('orders.restore') }}" method="POST" class="d-inline-block">
                           @csrf
                           <input type="hidden" name="id" value="{{ $order->id }}">
                           <button type="submit" class="btn btn-outline-success btn-sm" title="Restore Order">
                             <i class="bi bi-arrow-counterclockwise"></i>
                           </button>
                         </form>
                         <form action="{{ route('orders.permanently.delete') }}" method="POST" class="d-inline-block">
                           @csrf
                           <input type="hidden" name="id" value="{{ $order->id }}">
                           <button type="button" class="btn btn-outline-danger btn-sm actionDelete" title="Delete Permanently">
                             <i class="bi bi-trash"></i>
                           </button>
                         </form>
                       </div>
                     </td>
                   </tr>
                 @endforeach

               @else
                 <tr>
                   <td colspan="11" class="text-center py-5">
                     <div class="py-5">
                       <i class="bi bi-inbox display-1 text-muted"></i>
                       <p class="text-muted mt-3">No soft deleted orders found.</p>
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

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAllModalLabel">Delete All Soft Deleted Orders</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ url('panel/admin/orders/delete-all-soft-deleted') }}" method="post">
        @csrf
        <div class="modal-body">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Permanently Delete All Orders</strong> This action will permanently delete ALL soft deleted orders from the database.
          </div>
          <p>This action will:</p>
          <ul>
            <li>Permanently delete all soft deleted orders from the database</li>
            <li>This action cannot be undone</li>
            <li>All data will be lost forever</li>
          </ul>
          <div class="mb-3">
            <label for="confirmDeleteAllInput" class="form-label">Type <code>DELETE_ALL_ORDERS</code> to confirm:</label>
            <input type="text" class="form-control" id="confirmDeleteAllInput" name="confirm" placeholder="DELETE_ALL_ORDERS" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete All Orders</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Permanent Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>Warning!</strong> This action cannot be undone. The order will be permanently deleted from the database.
        </div>
        <p>Are you sure you want to permanently delete this order?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Delete Permanently</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation
    const deleteButtons = document.querySelectorAll('.actionDelete');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    let currentForm = null;

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentForm = this.closest('form');
            deleteModal.show();
        });
    });

    confirmDeleteBtn.addEventListener('click', function() {
        if (currentForm) {
            currentForm.submit();
        }
    });

    // Handle Bulk Selection
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const btnRestoreSelected = document.getElementById('btnRestoreSelected');
    const bulkRestoreForm = document.getElementById('bulkRestoreForm');
    const bulkRestoreIds = document.getElementById('bulkRestoreIds');

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateRestoreButton();
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateRestoreButton();
                // Update Select All state
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    if (allChecked) selectAll.checked = true;
                }
            });
        });

        btnRestoreSelected.addEventListener('click', function() {
            const selectedIds = Array.from(checkboxes)
                .filter(c => c.checked)
                .map(c => c.value);

            if (selectedIds.length > 0) {
                // Remove existing hidden inputs for ids
                const existingInputs = bulkRestoreForm.querySelectorAll('input[name="ids[]"]');
                existingInputs.forEach(input => input.remove());

                // Create input for each ID
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkRestoreForm.appendChild(input);
                });

                bulkRestoreForm.submit();
            }
        });

        function updateRestoreButton() {
            const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
            btnRestoreSelected.disabled = checkedCount === 0;
            if (checkedCount > 0) {
                btnRestoreSelected.innerHTML = `<i class="bi bi-arrow-counterclockwise me-1"></i> Restore Selected (${checkedCount})`;
            } else {
                btnRestoreSelected.innerHTML = `<i class="bi bi-arrow-counterclockwise me-1"></i> Restore Selected`;
            }
        }
    }
});
</script>
@endsection

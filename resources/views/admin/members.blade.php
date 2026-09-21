@extends('admin.layout')

@section('content')
@php
    $pageTitle = $filterDealers ?? false ? 'Dealers' : __('admin.members');
    $showDealerActions = $filterDealers ?? false;
@endphp

	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ $pageTitle }} ({{$data->total()}})</span>
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

              @if($filterDealers ?? false)
              <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>Showing dealers only
              </div>
              @endif

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

          <div class="d-inline-block mb-2 w-100">

          @if ($data->count() != 0)
            <!-- form -->
            <form role="search" autocomplete="off" action="{{ $filterDealers ?? false ? url('panel/admin/dealers') : url('panel/admin/members') }}" method="get" class="position-relative d-inline-block">
							<i class="bi bi-search btn-search bar-search"></i>
             <input type="text" name="q" class="form-control ps-5" placeholder="{{ __('misc.search') }}">
          </form><!-- form -->
            @endif

            @if(auth()->user()->id == 25 || auth()->user()->username == '3001234567')
                <a href="{{ route('admin.export_users') }}" class="btn btn-success float-end">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Users
                </a>
            @endif
          </div>

					<div class="table-responsive p-0">
						<table class="table table-hover">
						 <tbody>

               @if ($data->total() !=  0 && $data->count() != 0)
                  <tr>
                     <th class="active">ID</th>
                     <th class="active">Dealership ID</th>
                     <th class="active">{{ trans('auth.username') }}</th>
                     <th class="active">{{ trans('misc.balance') }}</th>
                     <th class="active">{{ trans('admin.date') }}</th>
                     <th class="active">IP</th>
                     <th class="active">{{ trans('admin.role') }}</th>
                     <th class="active">{{ trans('admin.status') }}</th>
                     <th class="active">{{ trans('admin.actions') }}</th>
                   </tr>

                 @foreach ($data as $user)
                   <tr>
                     <td>{{ $user->id }}</td>
                     <td>{{ $user->dealership_id }}</td>
                     <td>
                       <a href="{{ url($user->username) }}" target="_blank">
                         <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle me-1" style="width: 40px; height: 40px;">
                           <i class="bi bi-person text-muted"></i>
                         </div> {{ $user->username }}
                       </a>
                     </td>
                     <td>{{ Helper::amountFormatDecimal($user->balance)}}</td>
                     <td>{{ Helper::formatDate($user->date) }}</td>
                     <td>{{ $user->ip ? $user->ip : trans('misc.not_available') }}</td>
                     <td>
                      @foreach (RolesAndPermissions::all() as $role)

                      @if ($user->role == $role->id)
                        <span class="badge bg-success">{{ $role->name }}</span>
                      @endif

                     @endforeach

                     @if ($user->role == '0')
                     <span class="badge bg-secondary">{{ trans('admin.normal') }}</span>
                     @endif
                     </td>

                    @php if ($user->status == 'pending') {
                  $mode    = 'info';
                 $_status = trans('admin.pending');
                           } elseif ($user->status == 'active') {
                  $mode = 'success';
                 $_status = trans('admin.active');
                           } else {
                 $mode = 'warning';
                 $_status = trans('admin.suspended');
                         }
                       @endphp

                     <td><span class="badge bg-{{$mode}}">{{ $_status }}</span></td>
                     <td>
                       <div class="d-flex gap-1">
                         <!-- Credit Button -->
                         <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#creditModal"
                                 onclick="setCreditUser({{ $user->id }}, '{{ $user->username }}', {{ $user->balance }})" title="Add Credit">
                           <i class="bi bi-plus-circle"></i>
                         </button>

                         <!-- Debit Button -->
                         <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#debitModal"
                                 onclick="setDebitUser({{ $user->id }}, '{{ $user->username }}', {{ $user->balance }})" title="Deduct Amount">
                           <i class="bi bi-dash-circle"></i>
                         </button>

                         @if ($user->id <> auth()->user()->id && $user->id <> 1)
                           <a href="{{ url('panel/admin/members/edit', $user->id) }}" class="btn btn-outline-primary btn-sm" title="Edit User">
                             <i class="far fa-edit"></i>
                           </a>

                           <form action="{{ route('user.destroy', $user->id) }}" method="POST" id="form{{ $user->id }}" class="d-inline-block">
                             @csrf
                             <button type="button" data-url="{{ $user->id }}" class="btn btn-outline-danger btn-sm actionDelete" title="Delete User">
                                 <i class="bi-trash-fill"></i>
                             </button>
                           </form>

                           @if($showDealerActions)
                             <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#removeDealerModal{{ $user->id }}" title="Remove Dealer Status">
                               <i class="bi bi-x-lg"></i>
                             </button>
                           @endif
                         @else
                           <span class="text-muted">---</span>
                         @endif
                       </div>
                     </td>

                   </tr><!-- /.TR -->

                   @if($showDealerActions)
                   <!-- Remove Dealer Status Modal -->
                   <div class="modal fade" id="removeDealerModal{{ $user->id }}" tabindex="-1">
                       <div class="modal-dialog">
                           <div class="modal-content">
                               <form method="POST" action="{{ route('dealers.remove_status') }}">
                                   @csrf
                                   <input type="hidden" name="user_id" value="{{ $user->id }}">
                                   <div class="modal-header">
                                       <h5 class="modal-title">Remove Dealer Status</h5>
                                       <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                   </div>
                                   <div class="modal-body">
                                       Are you sure you want to remove dealer status from <strong>{{ $user->username }}</strong>?
                                   </div>
                                   <div class="modal-footer">
                                       <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                       <button type="submit" class="btn btn-danger">Remove Dealer Status</button>
                                   </div>
                               </form>
                           </div>
                       </div>
                   </div>
                   @endif

                   @endforeach

									@else
										<h5 class="text-center p-5 text-muted fw-light m-0">{{ trans('misc.no_results_found') }}

                      @if (isset($query))
                        <div class="d-block w-100 mt-2">
                          <a href="{{url('panel/admin/members')}}"><i class="bi-arrow-left me-1"></i> {{ trans('auth.back') }}</a>
                        </div>
                      @endif
                    </h5>
									@endif

								</tbody>
								</table>
							</div><!-- /.box-body -->

				 </div><!-- card-body -->
 			</div><!-- card  -->

			{{ $data->appends(['q' => $query])->onEachSide(0)->links() }}
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->

<!-- Credit Modal -->
<div class="modal fade" id="creditModal" tabindex="-1" aria-labelledby="creditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="creditModalLabel">Add Credit to User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="creditForm" action="{{ url('panel/admin/members/credit') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">User</label>
            <input type="text" id="creditUsername" class="form-control" readonly>
            <input type="hidden" id="creditUserId" name="user_id">
          </div>
          <div class="mb-3">
            <label class="form-label">Current Balance</label>
            <input type="text" id="creditCurrentBalance" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label for="creditAmount" class="form-label">Amount to Add <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="creditAmount" name="amount" step="0.01" min="0.01" required>
          </div>
          <div class="mb-3">
            <label for="creditDescription" class="form-label">Description</label>
            <textarea class="form-control" id="creditDescription" name="description" rows="3" placeholder="Reason for adding credit..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add Credit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Debit Modal -->
<div class="modal fade" id="debitModal" tabindex="-1" aria-labelledby="debitModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="debitModalLabel">Deduct Amount from User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="debitForm" action="{{ url('panel/admin/members/debit') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">User</label>
            <input type="text" id="debitUsername" class="form-control" readonly>
            <input type="hidden" id="debitUserId" name="user_id">
          </div>
          <div class="mb-3">
            <label class="form-label">Current Balance</label>
            <input type="text" id="debitCurrentBalance" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label for="debitAmount" class="form-label">Amount to Deduct <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="debitAmount" name="amount" step="0.01" min="0.01" required>
          </div>
          <div class="mb-3">
            <label for="debitDescription" class="form-label">Description</label>
            <textarea class="form-control" id="debitDescription" name="description" rows="3" placeholder="Reason for deducting amount..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Deduct Amount</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function setCreditUser(userId, username, currentBalance) {
    document.getElementById('creditUserId').value = userId;
    document.getElementById('creditUsername').value = username;
    document.getElementById('creditCurrentBalance').value = 'Rs. ' + parseFloat(currentBalance).toFixed(2);
    document.getElementById('creditAmount').value = '';
    document.getElementById('creditDescription').value = '';
}

function setDebitUser(userId, username, currentBalance) {
    document.getElementById('debitUserId').value = userId;
    document.getElementById('debitUsername').value = username;
    document.getElementById('debitCurrentBalance').value = 'Rs. ' + parseFloat(currentBalance).toFixed(2);
    document.getElementById('debitAmount').value = '';
    document.getElementById('debitDescription').value = '';

    // Set max amount to current balance
    document.getElementById('debitAmount').max = currentBalance;
}
</script>

@endsection

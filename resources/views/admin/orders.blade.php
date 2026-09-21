@extends('admin.layout')

@section('content')
@php
    $pageTitle = $filterDealers ?? false ? 'Dealer Orders' : __('admin.orders');
    $baseUrl = ($filterDealers ?? false) ? url('panel/admin/dealer-orders') : url('panel/admin/orders');
@endphp

	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">
        {{ $pageTitle }}
        @if(request('category'))
          - {{ request('category') }}
        @endif
        @if(request('rttp_filter'))
          - RTTP: {{ request('rttp_filter') }}
        @endif
        ({{ request('rttp_filter') ? $data->count() : $data->total() }})
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

              @if($filterDealers ?? false)
              <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>Showing orders from dealers only
              </div>
              @endif

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

          <div class="d-inline-block mb-2 w-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              @if(request('category') || request('rttp_filter') || request('dealer_id'))
                <div>
                  @if(request('category'))
                    <span class="badge bg-primary me-2">{{ request('category') }}</span>
                  @endif
                  @if(request('rttp_filter'))
                    <span class="badge bg-info me-2">RTTP: {{ request('rttp_filter') }}</span>
                  @endif
                  @if(request('dealer_id'))
                    @php
                        $dealerName = isset($dealers) ? $dealers->firstWhere('id', request('dealer_id'))?->username : request('dealer_id');
                    @endphp
                    <span class="badge bg-success me-2">Dealer: {{ $dealerName ?? request('dealer_id') }}</span>
                  @endif
                  <a href="{{ $baseUrl }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Clear Filter
                  </a>
                </div>
              @else
                <div></div>
              @endif

              <div>
                @php
                  $exportParams = array_filter([
                    'category' => request('category'),
                    'rttp_filter' => request('rttp_filter'),
                    'q' => request('q'),
                    'dealer_id' => request('dealer_id'),
                    'filter_dealers' => ($filterDealers ?? false) ? 1 : null,
                  ], function ($value) {
                    return !is_null($value) && $value !== '';
                  });
                  $exportUrl = url('panel/admin/orders/export-pdf') . (count($exportParams) ? '?' . http_build_query($exportParams) : '');
                @endphp
                @if(request('rttp_filter'))
                  <a href="{{ url('panel/admin/orders/export-winner?rttp_filter=' . urlencode(request('rttp_filter')) . (request('category') ? '&category=' . urlencode(request('category')) : '')) }}"
                     class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-trophy me-1"></i> Export Winner
                  </a>
                @endif
                <a href="{{ $exportUrl }}"
                   class="btn btn-success btn-sm me-2">
                  <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('admin.export_pdf') }}
                </a>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                  <i class="bi bi-trash me-1"></i>
                  @if($filterDealers ?? false)
                    Delete All Dealer Orders
                  @elseif(request('category'))
                    Delete All {{ request('category') }} Orders
                  @else
                    Delete All Orders
                  @endif
                </button>
              </div>
            </div>

            <form action="{{ $baseUrl }}" method="GET" class="row g-2 mb-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="{{ __('misc.search') }}" value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                        <input type="text" name="rttp_filter" class="form-control form-control-sm" placeholder="RTTP" value="{{ request('rttp_filter') }}">
                    </div>
                </div>

                <div class="col-md-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="categoryFilterBtn">
                             <i class="bi bi-controller me-1"></i>
                             {{ request('category') ? request('category') : 'All Games' }}
                        </button>
                        <div class="dropdown-menu p-2" style="min-width: 300px;">
                            <input type="text" class="form-control form-control-sm mb-2" id="categorySearchInput" placeholder="Search game..." onclick="event.stopPropagation()">
                            <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;" id="categoryList">
                               <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="list-group-item list-group-item-action py-2 category-item">
                                   <i class="bi bi-controller me-2"></i>All Games
                               </a>
                               @foreach($categories as $category)
                                 <a href="{{ request()->fullUrlWithQuery(['category' => $category->name]) }}" class="list-group-item list-group-item-action py-2 category-item" data-search="{{ strtolower($category->name) }}">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $category->name }}</div>
                                        </div>
                                    </div>
                                 </a>
                               @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- Hidden input to maintain compatibility if form is submitted via other means, though links above handle navigation -->
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                </div>
                <script>
                    document.getElementById('categorySearchInput').addEventListener('keyup', function() {
                        let filter = this.value.toLowerCase();
                        let items = document.querySelectorAll('.category-item');

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

                @if(($filterDealers ?? false) && isset($dealers))
                <div class="col-md-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dealerFilterBtn">
                             <i class="bi bi-person-badge me-1"></i>
                             {{ request('dealer_id') ? ($dealers->firstWhere('id', request('dealer_id'))->username ?? 'Unknown Dealer') : 'Select Dealer' }}
                        </button>
                        <div class="dropdown-menu p-2" style="min-width: 300px;">
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
                    <!-- Hidden input to maintain compatibility if form is submitted via other means, though links above handle navigation -->
                    @if(request('dealer_id'))
                        <input type="hidden" name="dealer_id" value="{{ request('dealer_id') }}">
                    @endif
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

                <div class="col-md-auto ms-auto">
                     <button type="submit" class="btn btn-outline-primary btn-sm me-2">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ $baseUrl }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
          </div>

					<!-- Desktop Table View -->
					<div class="table-responsive p-0 d-none d-md-block">
						<table class="table table-hover table-sm">
						 <tbody>

               @if ((method_exists($data, 'total') ? $data->total() : $data->count()) != 0 && $data->count() != 0)
                  <tr>
                     <th class="active">{{ __('admin.username') }}</th>
                     <th class="active d-none d-xl-table-cell">{{ __('admin.game_name') }}</th>
                     <th class="active d-none d-xl-table-cell">{{ __('admin.bond_name') }}</th>
                     <th class="active">{{ __('admin.rttp') }}</th>
                     <th class="active">{{ __('admin.first') }}</th>
                     <th class="active">{{ __('admin.second') }}</th>
                     <th class="active">{{ __('admin.total') }}</th>
                     @if($filterDealers ?? false)
                     <th class="active">Commission</th>
                     @endif
                     <th class="active">{{ __('admin.status') }}</th>
                     <th class="active">{{ __('admin.n_p') }}</th>
                     <th class="active d-none d-lg-table-cell">{{ __('admin.date') }}</th>
                     <th class="active">{{ __('admin.actions') }}</th>
                   </tr>

                 @foreach ($data as $order)
                   <tr>
                     <td>
                       <div class="d-flex align-items-center">
                         <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle me-2" style="width: 32px; height: 32px;">
                           <i class="bi bi-person text-muted" style="font-size: 12px;"></i>
                         </div>
                         <div>
                           <div class="fw-bold">{{ $order->username }}</div>
                         </div>
                       </div>
                     </td>
                     <td class="d-none d-xl-table-cell">{{ $order->game_name }}</td>
                     <td class="d-none d-xl-table-cell">{{ $order->bond_name }}</td>
                     <td>
                       <span class="fw-bold" style="font-size: 16px;">{{ $order->rttp }}</span>
                     </td>
                     <td>
                       <span class="fw-bold" style="font-size: 16px;">{{ $order->first }}</span>
                     </td>
                     <td>
                       <span class="fw-bold" style="font-size: 16px;">{{ $order->second }}</span>
                     </td>
                     <td class="text-end fw-bold">{{ number_format((float)$order->first + (float)$order->second, 2) }}</td>
                     @if($filterDealers ?? false)
                     <td class="text-end text-success fw-bold">{{ number_format($order->commission_amount, 2) }}</td>
                     @endif
                     <td>
                       @php
                         if ($order->status == 'OK') {
                           $mode = 'success';
                           $_status = 'OK';
                         } elseif ($order->status == 'first_win') {
                           $mode = 'primary';
                           $_status = __('admin.first_win');
                         } elseif ($order->status == 'second_win') {
                           $mode = 'primary';
                           $_status = __('admin.second_win');
                         } else {
                           $mode = 'danger';
                           $_status = __('admin.rejected');
                         }
                       @endphp
                       <span class="badge bg-{{ $mode }}">{{ $_status }}</span>
                     </td>
                     <td>
                       @if($order->n_p)
                         <span class="badge bg-{{ $order->n_p == 'P' ? 'primary' : 'secondary' }}">{{ $order->n_p }}</span>
                       @else
                         <span class="text-muted">-</span>
                       @endif
                     </td>
                     <td class="d-none d-lg-table-cell">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                     <td>
                       <div class="d-flex gap-1">
                         <!-- N/P Buttons -->
                         <div class="d-flex gap-1 me-2">
                             <button type="button" class="btn {{ $order->n_p == 'N' ? 'btn-outline-secondary' : 'btn-outline-primary' }} btn-sm btn-np"
                                     data-id="{{ $order->id }}" data-value="N"
                                     title="Set N/P to N" style="min-width: 30px; font-weight: bold;">
                               N
                             </button>
                             <button type="button" class="btn {{ $order->n_p == 'P' ? 'btn-outline-secondary' : 'btn-outline-primary' }} btn-sm btn-np"
                                     data-id="{{ $order->id }}" data-value="P"
                                     title="Set N/P to P" style="min-width: 30px; font-weight: bold;">
                               P
                             </button>
                         </div>

                         <!-- Action Buttons -->
                         <a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editOrder{{ $order->id }}" title="Edit Order">
                           <i class="bi bi-pencil-square"></i>
                         </a>
                         <form action="{{ url('panel/admin/orders/delete') }}" method="POST" class="d-inline-block">
                           @csrf
                           <input type="hidden" name="id" value="{{ $order->id }}">
                           <button type="button" class="btn btn-outline-danger btn-sm actionDelete" title="Delete Order">
                             <i class="bi bi-trash"></i>
                           </button>
                         </form>
                       </div>
                     </td>
                   </tr>

                 @endforeach

               @else
                 <tr>
                   <td colspan="13" class="text-center py-5">
                     <div class="py-5">
                       <i class="bi bi-inbox display-1 text-muted"></i>
                       <p class="text-muted mt-3">{{ __('admin.no_orders_found') }}</p>
                     </div>
                   </td>
                 </tr>
               @endif

             </tbody>
           </table>
         </div>

         <!-- Mobile Card View -->
         <div class="d-md-none">
           @if ((method_exists($data, 'total') ? $data->total() : $data->count()) != 0 && $data->count() != 0)
             @foreach ($data as $order)
               <div class="card mb-2 shadow-sm">
                 <div class="card-body p-2">
                   <!-- User Details Row -->
                   <div class="d-flex justify-content-between align-items-center mb-2">
                     <div class="d-flex align-items-center">
                       <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle me-2" style="width: 32px; height: 32px;">
                         <i class="bi bi-person text-muted" style="font-size: 12px;"></i>
                       </div>
                       <div>
                         <div class="fw-bold" style="font-size: 14px;">{{ $order->username }}</div>
                       </div>
                     </div>
                     <small class="text-muted" style="font-size: 10px;">{{ $order->created_at->format('M j, H:i') }}</small>
                   </div>

                   <!-- Status and N/P Row -->
                   <div class="d-flex justify-content-between align-items-center mb-2">
                     <div>
                       @php
                         if ($order->status == 'OK') {
                           $mode = 'success';
                           $_status = 'OK';
                         } elseif ($order->status == 'first_win') {
                           $mode = 'primary';
                           $_status = __('admin.first_win');
                         } elseif ($order->status == 'second_win') {
                           $mode = 'primary';
                           $_status = __('admin.second_win');
                         } else {
                           $mode = 'danger';
                           $_status = __('admin.rejected');
                         }
                       @endphp
                       <span class="badge bg-{{ $mode }}" style="font-size: 10px;">{{ $_status }}</span>
                     </div>
                     <div class="div-np" data-id="{{ $order->id }}">
                       @if($order->n_p)
                         <span class="badge bg-{{ $order->n_p == 'P' ? 'primary' : 'secondary' }}" style="font-size: 10px;">{{ $order->n_p }}</span>
                       @else
                         <span class="text-muted" style="font-size: 10px;">-</span>
                       @endif
                     </div>
                   </div>

                   <!-- Game Name Row -->
                   <div class="mb-2">
                     <small class="text-muted d-block" style="font-size: 10px;">{{ __('admin.game_name') }}</small>
                     <div class="fw-bold" style="font-size: 12px;">{{ $order->game_name }}</div>
                   </div>

                   <!-- Bond Name Row -->
                   <div class="mb-2">
                     <small class="text-muted d-block" style="font-size: 10px;">{{ __('admin.bond_name') }}</small>
                     <div class="fw-bold" style="font-size: 12px;">{{ $order->bond_name }}</div>
                   </div>

                   <!-- RTTP, First, Second, Total Row -->
                   <div class="row mb-2">
                     <div class="col-3">
                       <div class="text-center">
                         <small class="text-muted d-block" style="font-size: 9px;">{{ __('admin.rttp') }}</small>
                         <div class="fw-bold" style="font-size: 14px;">{{ $order->rttp }}</div>
                       </div>
                     </div>
                     <div class="col-3">
                       <div class="text-center">
                         <small class="text-muted d-block" style="font-size: 9px;">{{ __('admin.first') }}</small>
                         <div class="fw-bold" style="font-size: 14px;">{{ $order->first }}</div>
                       </div>
                     </div>
                     <div class="col-3">
                       <div class="text-center">
                         <small class="text-muted d-block" style="font-size: 9px;">{{ __('admin.second') }}</small>
                         <div class="fw-bold" style="font-size: 14px;">{{ $order->second }}</div>
                       </div>
                     </div>
                     <div class="col-3">
                       <div class="text-center">
                         <small class="text-muted d-block" style="font-size: 9px;">{{ __('admin.total') }}</small>
                         <div class="fw-bold text-success" style="font-size: 11px;">{{ number_format((float)$order->first + (float)$order->second, 2) }}</div>
                       </div>
                     </div>
                   </div>

                   @if($filterDealers ?? false)
                   <div class="mb-2 text-center bg-light p-1 rounded">
                     <small class="text-muted d-block" style="font-size: 10px;">Commission</small>
                     <div class="fw-bold text-success" style="font-size: 12px;">{{ number_format($order->commission_amount, 2) }}</div>
                   </div>
                   @endif

                   <!-- Action Buttons -->
                   <div class="d-flex gap-2">
                     <!-- N/P Buttons -->
                     <div class="d-flex gap-1 flex-grow-1">
                         <button type="button" class="btn {{ $order->n_p == 'N' ? 'btn-secondary' : 'btn-outline-primary' }} w-100 btn-np"
                                 data-id="{{ $order->id }}" data-value="N"
                                 title="Set N/P to N">
                           <strong>N</strong>
                         </button>
                         <button type="button" class="btn {{ $order->n_p == 'P' ? 'btn-secondary' : 'btn-outline-primary' }} w-100 btn-np"
                                 data-id="{{ $order->id }}" data-value="P"
                                 title="Set N/P to P">
                           <strong>P</strong>
                         </button>
                     </div>

                    <!-- Edit and Delete Buttons -->
                    <div class="d-flex gap-1">
                      <a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editOrder{{ $order->id }}" title="Edit Order" style="min-width: 38px; min-height: 38px; display: flex; align-items: center; justify-content: center; touch-action: manipulation;">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <form action="{{ url('panel/admin/orders/delete') }}" method="POST" class="d-inline-block">
                        @csrf
                        <input type="hidden" name="id" value="{{ $order->id }}">
                        <button type="button" class="btn btn-outline-danger btn-sm actionDelete" title="Delete Order" style="min-width: 38px; min-height: 38px; display: flex; align-items: center; justify-content: center; touch-action: manipulation;">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                   </div>
                 </div>
               </div>

             @endforeach
           @else
             <div class="text-center py-5">
               <i class="bi bi-inbox display-1 text-muted"></i>
               <p class="text-muted mt-3">{{ __('admin.no_orders_found') }}</p>
             </div>
           @endif
         </div>

         <!-- Edit Order Modals - Shared between Desktop and Mobile -->
         @if ((method_exists($data, 'total') ? $data->total() : $data->count()) != 0 && $data->count() != 0)
           @foreach ($data as $order)
             <div class="modal fade" id="editOrder{{ $order->id }}" tabindex="-1" aria-labelledby="editOrderLabel{{ $order->id }}" aria-hidden="true">
               <div class="modal-dialog">
                 <div class="modal-content">
                   <div class="modal-header">
                     <h5 class="modal-title" id="editOrderLabel{{ $order->id }}">{{ __('admin.edit_order') }}</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   <form action="{{ url('panel/admin/orders/update') }}" method="post">
                     @csrf
                     <input type="hidden" name="id" value="{{ $order->id }}">
                     <div class="modal-body">
                       <div class="row">
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.username') }}</label>
                           <input type="text" name="username" class="form-control" value="{{ $order->username }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.phone') }}</label>
                           <input type="text" name="user_phone" class="form-control" value="{{ $order->user_phone }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.game_name') }}</label>
                           <input type="text" name="game_name" class="form-control" value="{{ $order->game_name }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.bond_name') }}</label>
                           <input type="text" name="bond_name" class="form-control" value="{{ $order->bond_name }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.rttp') }}</label>
                           <input type="text" name="rttp" class="form-control" value="{{ $order->rttp }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.first') }}</label>
                           <input type="text" name="first" class="form-control" value="{{ $order->first }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.second') }}</label>
                           <input type="text" name="second" class="form-control" value="{{ $order->second }}" required>
                         </div>
                         <div class="col-md-6 mb-3">
                           <label class="form-label">{{ __('admin.status') }}</label>
                           <select name="status" class="form-control" required>
                             <option value="OK" {{ $order->status == 'OK' ? 'selected' : '' }}>OK</option>
                             <option value="first_win" {{ $order->status == 'first_win' ? 'selected' : '' }}>{{ __('admin.first_win') }}</option>
                             <option value="second_win" {{ $order->status == 'second_win' ? 'selected' : '' }}>{{ __('admin.second_win') }}</option>
                             <option value="rejected" {{ $order->status == 'rejected' ? 'selected' : '' }}>{{ __('admin.rejected') }}</option>
                           </select>
                         </div>
                       </div>
                     </div>
                     <div class="modal-footer">
                       <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('misc.cancel') }}</button>
                       <button type="submit" class="btn btn-primary">{{ __('misc.save') }}</button>
                     </div>
                   </form>
                 </div>
               </div>
             </div>
           @endforeach
         @endif

         @if (!request()->has('rttp_filter') && method_exists($data, 'hasPages') && $data->hasPages())
           <div class="d-flex justify-content-center mt-4">
             {{ $data->appends(request()->query())->links() }}
           </div>
         @endif

				</div>
			</div>
		</div>
	</div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrder" tabindex="-1" aria-labelledby="addOrderLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addOrderLabel">{{ __('admin.add_order') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ url('panel/admin/orders/store') }}" method="post">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.username') }}</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.user_phone') }}</label>
              <input type="text" name="user_phone" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.game_name') }}</label>
              <input type="text" name="game_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.bond_name') }}</label>
              <input type="text" name="bond_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.rttp') }}</label>
              <input type="text" name="rttp" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.first') }}</label>
              <input type="text" name="first" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.second') }}</label>
              <input type="text" name="second" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ __('admin.status') }}</label>
              <select name="status" class="form-control" required>
                <option value="OK">OK</option>
                <option value="first_win">{{ __('admin.first_win') }}</option>
                <option value="second_win">{{ __('admin.second_win') }}</option>
                <option value="rejected">{{ __('admin.rejected') }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('misc.cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('misc.save') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete All Orders Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAllModalLabel">
          @if($filterDealers ?? false)
            Delete All Dealer Orders
          @elseif(request('category'))
            Delete All {{ request('category') }} Orders
          @else
            Delete All Orders
          @endif
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ url('panel/admin/orders/delete-all') }}" method="post">
        @csrf
        @if($filterDealers ?? false)
          <input type="hidden" name="filter_dealers" value="1">
        @endif
        @if(request('category'))
          <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Warning!</strong>
            @if($filterDealers ?? false)
              This action will move ALL DEALER orders to the soft deleted table. They can be restored later.
            @elseif(request('category'))
              This action will move ALL {{ request('category') }} orders to the soft deleted table. They can be restored later.
            @else
              This action will move ALL orders to the soft deleted table. They can be restored later.
            @endif
          </div>
          <p>This action will:</p>
          <ul>
            @if($filterDealers ?? false)
              <li>Move all DEALER orders to the soft deleted table</li>
            @elseif(request('category'))
              <li>Move all {{ request('category') }} orders to the soft deleted table</li>
            @else
              <li>Move all orders to the soft deleted table</li>
            @endif
            <li>Remove them from the main orders table</li>
            <li>Preserve all order data for potential restoration</li>
          </ul>
          <div class="mb-3">
            <label for="confirmDelete" class="form-label">Type <code>DELETE_ALL_ORDERS</code> to confirm:</label>
            <input type="text" class="form-control" id="confirmDelete" name="confirm" placeholder="DELETE_ALL_ORDERS" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            @if($filterDealers ?? false)
              Delete All Dealer Orders
            @elseif(request('category'))
              Delete All {{ request('category') }} Orders
            @else
              Delete All Orders
            @endif
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        // Function to show floating flash messages (Toasts)
        function showFlashMessage(message, type) {
            var icon = type === 'success' ? 'check2' : 'exclamation-triangle';
            var alertClass = type === 'success' ? 'alert-success' : 'alert-warning';

            // Create a fixed position toast notification
            var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed shadow-lg" role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;">' +
                       '<i class="bi bi-' + icon + ' me-1"></i> ' + message +
                       '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
                       '<i class="bi bi-x-lg"></i>' +
                       '</button></div>';

            // Remove existing toasts
            $('.alert.position-fixed').remove();

            // Append to body
            $('body').append(html);

            // Auto hide after 3 seconds
            setTimeout(function() {
                $('.alert.position-fixed').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        }

        $(document).on('click', '.btn-np', function() {
            var btn = $(this);
            var orderId = btn.data('id');
            var npValue = btn.data('value');

            // Disable all buttons for this order during request
            var allBtns = $('.btn-np[data-id="' + orderId + '"]');
            allBtns.prop('disabled', true);

            $.ajax({
                url: "{{ url('panel/admin/orders/update-np') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: orderId,
                    n_p: npValue
                },
                success: function(response) {
                    if (response.success) {
                        // Update button styles immediately
                        allBtns.each(function() {
                            var currentBtn = $(this);
                            var val = currentBtn.data('value');

                            // Remove all possible state classes
                            currentBtn.removeClass('btn-outline-secondary btn-outline-primary btn-secondary');

                            // Check if this is desktop or mobile view (mobile uses w-100)
                            var isMobile = currentBtn.hasClass('w-100');

                            if (val === npValue) {
                                // This is the selected value
                                if (isMobile) {
                                    currentBtn.addClass('btn-secondary');
                                } else {
                                    currentBtn.addClass('btn-outline-secondary');
                                }
                            } else {
                                // This is the unselected value
                                currentBtn.addClass('btn-outline-primary');
                            }
                        });

                        // Update Status Badge (Desktop)
                        var badgeClass = npValue === 'P' ? 'primary' : 'secondary';
                        var badgeHtml = '<span class="badge bg-' + badgeClass + '">' + npValue + '</span>';
                        $('.td-np[data-id="' + orderId + '"]').html(badgeHtml);

                        // Update Status Badge (Mobile)
                        var badgeHtmlMobile = '<span class="badge bg-' + badgeClass + '" style="font-size: 10px;">' + npValue + '</span>';
                        $('.div-np[data-id="' + orderId + '"]').html(badgeHtmlMobile);

                        showFlashMessage(response.message, 'success');
                    } else {
                        showFlashMessage(response.message || 'Failed to update', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFlashMessage(errorMsg, 'error');
                },
                complete: function() {
                    allBtns.prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection


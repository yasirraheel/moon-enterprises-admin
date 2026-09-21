@extends('layouts.app')

@section('title') {{ trans('misc.recharge_deposit') }} - @endsection

@section('content')
<section class="section section-sm">

<div class="container-custom container pt-5">
<div class="row">

  <div class="col-md-3">
    @include('users.navbar-settings')
  </div>

			<!-- Col MD -->
		<div class="col-md-9">

			@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
            	<i class="bi bi-check2 me-1"></i>	{{ session('success') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
            		</div>
            	@endif

			@if (session('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
            	<i class="bi bi-exclamation-triangle me-1"></i>	{{ session('error') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
            		</div>
            	@endif

			@include('errors.errors-forms')

      <h5 class="mb-4">{{ trans('misc.recharge_deposit') }}</h5>

		<!-- ***** FORM ***** -->
       <form action="{{ url('account/recharge') }}" method="post" enctype="multipart/form-data">

        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <!-- Payment Method Selection -->
        <div class="form-floating mb-3">
         <select class="form-control @error('payment_method_id') is-invalid @enderror" id="payment_method_id" name="payment_method_id" required>
           <option value="">{{ trans('misc.choose_payment_method') }}</option>
           @foreach($paymentMethods as $method)
             <option value="{{ $method->id }}"
                     data-bank-name="{{ $method->bank_or_account_name }}"
                     data-account-title="{{ $method->account_title }}"
                     data-account-no="{{ $method->account_no }}"
                     data-bank-image="{{ $method->bank_image }}">
               {{ $method->bank_or_account_name }}
             </option>
           @endforeach
         </select>
         <label for="payment_method_id">{{ trans('misc.select_payment_method') }}</label>
         @error('payment_method_id')
           <div class="invalid-feedback">{{ $message }}</div>
         @enderror
        </div>

        <!-- Payment Method Details Display -->
        <div id="payment-method-details" class="mb-4" style="display: none;">
          <div class="card border-primary">
            <div class="card-header bg-light">
              <h6 class="mb-0">{{ trans('misc.payment_method_details') }}</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3 text-center">
                  <div id="method-image" class="mb-3">
                    <!-- Image will be loaded here -->
                  </div>
                </div>
                <div class="col-md-9">
                  <div class="row">
                    <div class="col-sm-6">
                      <strong>{{ trans('misc.bank_or_account_name') }}:</strong>
                      <p id="method-bank-name" class="text-muted"></p>
                    </div>
                    <div class="col-sm-6">
                      <strong>{{ trans('misc.account_title') }}:</strong>
                      <p id="method-account-title" class="text-muted"></p>
                    </div>
                    <div class="col-sm-6">
                      <strong>{{ trans('misc.account_no') }}:</strong>
                      <p id="method-account-no" class="text-muted font-monospace"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
        	<div class="col-md-6">
            <div class="form-floating mb-3">
             <input type="number" step="0.01" min="1" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" placeholder="{{ trans('misc.amount') }}" required>
             <label for="amount">{{ trans('misc.amount') }} ({{ trans('misc.currency') }})</label>
             @error('amount')
               <div class="invalid-feedback">{{ $message }}</div>
             @enderror
           </div>
           </div><!-- End Col MD-->

            <div class="col-md-6">
              <div class="form-floating mb-3">
               <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" id="transaction_id" name="transaction_id" placeholder="{{ trans('misc.transaction_id') }}" required>
               <label for="transaction_id">{{ trans('misc.transaction_id') }}</label>
               @error('transaction_id')
                 <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
            </div><!-- End Col MD-->
        </div><!-- End row -->

        <div class="mb-3">
         <label for="payment_proof" class="form-label">{{ trans('misc.payment_proof') }}</label>
         <div class="input-group mb-1">
           <input type="file" class="form-control custom-file rounded-pill @error('payment_proof') is-invalid @enderror" id="payment_proof" name="payment_proof" accept="image/*,.pdf" required>
         </div>
         <div class="form-text">{{ trans('misc.payment_proof_help') }}</div>
         @error('payment_proof')
           <div class="invalid-feedback">{{ $message }}</div>
         @enderror
        </div>

           <button type="submit" id="buttonSubmit" class="btn w-100 btn-lg btn-custom">{{ trans('misc.submit_deposit_request') }}</button>

       </form><!-- ***** END FORM ***** -->

       <!-- Recent Deposits -->
       @if($recentDeposits->count() > 0)
       <div class="mt-5">
         <h6 class="mb-3">{{ trans('misc.recent_deposits') }}</h6>
         <div class="table-responsive">
           <table class="table table-sm">
             <thead>
               <tr>
                 <th>{{ trans('misc.amount') }}</th>
                 <th>{{ trans('misc.payment_method') }}</th>
                 <th>{{ trans('misc.transaction_id') }}</th>
                 <th>{{ trans('misc.status') }}</th>
                 <th>{{ trans('misc.date') }}</th>
               </tr>
             </thead>
             <tbody>
               @foreach($recentDeposits as $deposit)
               <tr>
                 <td>{{ number_format($deposit->amount, 2) }} {{ trans('misc.currency') }}</td>
                 <td>{{ $deposit->paymentMethod->bank_or_account_name }}</td>
                 <td class="font-monospace">{{ $deposit->transaction_id }}</td>
                 <td>
                   @if($deposit->status == 'pending')
                     <span class="badge bg-warning">{{ trans('misc.pending') }}</span>
                   @elseif($deposit->status == 'approved')
                     <span class="badge bg-success">{{ trans('misc.approved') }}</span>
                   @else
                     <span class="badge bg-danger">{{ trans('misc.rejected') }}</span>
                   @endif
                 </td>
                 <td>{{ $deposit->date ? \Carbon\Carbon::parse($deposit->date)->format('M d, Y') : 'N/A' }}</td>
               </tr>
               @endforeach
             </tbody>
           </table>
         </div>
       </div>
       @endif

  </div><!-- /COL MD -->
  </div><!-- row -->
</div><!-- container -->
</section>
@endsection

@section('javascript')
<script type="text/javascript">

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method_id');
    const paymentMethodDetails = document.getElementById('payment-method-details');
    const methodImage = document.getElementById('method-image');
    const methodBankName = document.getElementById('method-bank-name');
    const methodAccountTitle = document.getElementById('method-account-title');
    const methodAccountNo = document.getElementById('method-account-no');

    paymentMethodSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (this.value) {
            // Show payment method details
            paymentMethodDetails.style.display = 'block';

            // Update details
            methodBankName.textContent = selectedOption.dataset.bankName;
            methodAccountTitle.textContent = selectedOption.dataset.accountTitle;
            methodAccountNo.textContent = selectedOption.dataset.accountNo;

            // Handle image
            const bankImage = selectedOption.dataset.bankImage;
            if (bankImage) {
                methodImage.innerHTML = `<img src="{{ url('public/img') }}/${bankImage}" alt="Bank Image" class="img-fluid" style="max-width: 80px; max-height: 80px; border-radius: 8px; border: 1px solid #dee2e6;">`;
            } else {
                methodImage.innerHTML = `
                    <div style="width: 80px; height: 80px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                        <i class="bi bi-bank fs-4"></i>
                    </div>
                `;
            }
        } else {
            // Hide payment method details
            paymentMethodDetails.style.display = 'none';
        }
    });
});

</script>
@endsection

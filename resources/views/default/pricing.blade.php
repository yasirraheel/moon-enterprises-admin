@extends('layouts.app')

@section('title'){{ __('misc.pricing') }} -@endsection

@section('content')
<section class="section section-sm">

<div class="container">

  <div class="row justify-content-center">
	<!-- Col MD -->
	<div class="col-md-6">

    <div class="col-lg-12 py-5 text-center">
  		<h1 class="mb-0">
  			{{ __('misc.plans_for_photos') }}
  		</h1>
  		<p class="lead text-muted mt-0">{{ __('misc.subtitle_pricing') }}</p>

      <div class="d-flex justify-content-center">
        <div class="form-check form-switch form-switch-md flex-row d-flex align-items-center p-0">
          <label class="c-pointer" for="plan">{{ __('misc.monthly') }}</label>
          <input class="form-check-input mx-2" value="mo" type="checkbox" id="plan">
          <label class="c-pointer" for="plan">{{ __('misc.yearly') }}</label>
        </div>
      </div>

  	  </div>
		  </div><!-- /COL MD -->
    </div><!-- row -->

    <div class="row row-cols-1 row-cols-md-3 mb-3">

      @foreach ($plans->whereDownloadableContent('images')->get() as $plan)
        <div class="col">
            <div class="card mb-4 rounded-4 shadow p-4 @if ($plan->popular) popular-plan @endif">
              <div class="card-header py-3 bg-transparent border-bottom-0 text-center">
                @if ($plan->popular)
                <span class="w-100 mb-2 d-block">
                  <span class="badge rounded-pill bg-success px-3">{{ __('misc.popular') }} </span>
                </span>
                @endif
                <h2 class="my-0">
                  <span class="highlight">
                    {{ $plan->name }}
                  </span>

                  @if (Helper::calculateSubscriptionDiscount($plan->price, $plan->price_year) > 0)
                    <small class="badge bg-success rounded-pill display-none planYearly fs-small align-middle">{{ Helper::calculateSubscriptionDiscount($plan->price, $plan->price_year) }}% {{ __('misc.discount') }}</small>
                  @endif
                </h2>
              </div>
              <div class="card-body">
                <h1 class="card-title text-center">
                  <span class="planMonthly">
                    <sup class="h4 fw-bold lh-1">{{ $settings->currency_symbol }}</sup>
                    {{ $plan->price }}
                    <small class="fw-light f-size-18">/{{ __('misc.mo') }}</small>
                  </span>

                  <span class="planYearly display-none">
                    <sup class="h4 fw-bold lh-1">{{ $settings->currency_symbol }}</sup>
                    {{ $plan->price_year }}
                    <small class="fw-light f-size-18">/{{ __('misc.yr') }}</small>
                  </span>
                </h1>
                <ul class="list-unstyled mt-3 mb-4">
                  <li class="mb-2"><i class="bi-check2 me-1"></i> <strong>{{ $plan->downloads_per_month }}</strong> {{ __('admin.downloads_per_month') }}</li>
                  <li class="mb-2">
                    <i class="bi-check2 me-1"></i>
                    <span class="planMonthly">{{ Helper::calculatePriceByDownloads($plan->price, $plan->downloads_per_month, true) }}</span>
                    <span class="planYearly display-none">{{ Helper::calculatePriceByDownloads($plan->price_year, $plan->downloads_per_month) }}</span>
                    {{ __('misc.per_download') }}
                  </li>
                  <li class="mb-2"><i class="bi-check2 me-1"></i> {{ __('misc.all_images_vectors') }}</li>
                  <li class="mb-2"><i class="bi-check2 me-1"></i> {{ $plan->unused_downloads_rollover ? __('misc.unused_downloads_added_next_month') : __('misc.download_limit_renewed_monthly') }}</li>
                  <li class="mb-2"><i class="bi-check2 me-1"></i> {{ $plan->download_limits == 0 ? __('misc.no_daily_download_limits') : __('misc.downloads_per_day', ['number' => $plan->download_limits]) }}</li>
                  <li class="mb-2"><i class="bi-check2 me-1"></i> {{ $plan->license == 'regular' ? __('misc.license_regular') : __('admin.regular_extended') }}</li>
                  <li><i class="bi-check2 me-1"></i> {{ __('misc.cancel_subscription_any_time') }}</li>
                </ul>
                <a
                  data-plan-id="{{ $plan->plan_id }}"
                  data-plan-name="{{ __('misc.plan_name', ['plan' => $plan->name]) }}"
                  data-price="{{ Helper::amountFormatDecimal($plan->price) }}"
                  data-price-total="{{ Helper::amountFormatDecimal($plan->price, true) }}"
                  data-price-gross="{{ $plan->price }}"
                  data-price-year="{{ Helper::amountFormatDecimal($plan->price_year) }}"
                  data-price-year-gross="{{ $plan->price_year }}"
                  data-price-year-total="{{ Helper::amountFormatDecimal($plan->price_year, true) }}"
                  href="@auth javascript:void(0); @else{{ url('/login') }}@endauth"
                  @if (auth()->check() && ! $getSubscription) data-bs-toggle="modal" data-bs-target="#checkout" @endif
                    class="w-100 btn btn-lg btn-custom rounded-pill @if (auth()->check() && $getSubscription) disabled @endif">

                    @if (auth()->check()
                      && $getSubscription
                      && $getSubscription->stripe_price == $plan->plan_id)
                  {{ __('misc.active') }}

                @else
                  {{ __('misc.suscribe') }}
                @endif
                </a>
              </div>
            </div>
          </div>
      @endforeach

      <div class="d-block text-center w-100 fst-italic">
        <small>
          {{ __('misc.prices_and_excludes_tax', ['currency' => $settings->currency_code]) }}
        </small>
      </div>

    </div>
 </div><!-- container -->

 <div class="container py-5">
            <div class="text-center">
                <h2 class="d-inline-block">{{ __('misc.frequently_asked_questions') }}</h2>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 mt-5 h-100">
                    <h5 class="text-muted">{{ __('misc.faq_pricing_1') }}</h5>
                    <div class="text-muted">{{ __('misc.faq_pricing_1_reply') }}</div>
                </div>

                <div class="col-12 col-md-6 mt-5 h-100">
                    <h5 class="text-muted">{{ __('misc.faq_pricing_2') }}</h5>
                    <div class="text-muted">{{ __('misc.faq_pricing_2_reply') }}</div>
                </div>

                <div class="col-12 col-md-6 mt-5 h-100">
                    <h5 class="text-muted">{{ __('misc.faq_pricing_3') }}</h5>
                    <div class="text-muted">{{ __('misc.faq_pricing_3_reply') }}</div>
                </div>

                <div class="col-12 col-md-6 mt-5 h-100">
                    <h5 class="text-muted">{{ __('misc.faq_pricing_4') }}</h5>
                    <div class="text-muted">{{ __('misc.faq_pricing_4_reply') }}</div>
                </div>
            </div>
        </div>
</section>

@if (auth()->check() && ! $getSubscription)
<div class="modal fade" tabindex="-1"  id="checkout">
  <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-body p-lg-4">
        <h5 class="mb-3">
              <i class="bi bi-cart2 me-1"></i> {{ __('misc.checkout') }}

              <span class="float-end c-pointer" data-bs-dismiss="modal" aria-label="Close">
                <i class="bi bi-x-lg"></i>
              </span>
            </h5>
            <div class="container">
              <div class="row">

                <div class="col-md-6 ps-0">
                  <div class="mb-3">
                    <strong>{{ __('misc.payments_options') }}</strong>
                  </div>

                  <form method="post" action="{{url('buy/subscription')}}" class="d-inline" id="formBuySubscription">
                    @csrf

                    <input type="hidden" id="interval" name="interval" value="month">
                    <input type="hidden" id="planId" name="plan" value="">

                  @foreach (PaymentGateways::whereEnabled('1')->whereSubscription('1')->orderBy('type', 'DESC')->get() as $payment)
                    <div class="form-check custom-radio mb-2">
                      <input name="payment_gateway" value="{{$payment->id}}" id="payment_radio{{$payment->id}}" class="form-check-input radio-bws" type="radio">
                      <label class="form-check-label" for="payment_radio{{$payment->id}}">
                        <span><img class="me-1 rounded" src="{{ url('public/img/payments', $payment->logo) }}" width="20" /> <strong>{{ $payment->name }}</strong></span>
                        <small class="w-100 d-block">
                          @if ($payment->type == 'card')
                            {{ __('misc.debit_credit_card') }}
                          @endif

                          @if ($payment->name == 'PayPal')
                            {{ __('misc.paypal_info') }}
                          @endif
                        </small>
                      </label>
                    </div>
                  @endforeach

                  <div class="form-check custom-radio mb-3">
                    <input name="payment_gateway" @if (auth()->user()->funds == 0.00) disabled @endif value="wallet" id="wallet" class="form-check-input radio-bws" type="radio">
                    <label class="form-check-label" for="wallet">
                      <span><img class="me-1 rounded" src="{{ url('public/img/payments/wallet.png') }}" width="20" /> <strong>{{ __('misc.wallet') }}</strong></span>
                      <small class="w-100 d-block">
                        {{ __('misc.available_balance') }}: <strong>{{Helper::amountFormatDecimal(auth()->user()->funds)}}</strong>
                      </small>
                    </label>
                  </div>
                </div>
                <div class="col-md-6 ps-0">

                  <div class="mb-1">
                    <strong>{{ __('misc.order_summary') }}</strong>
                  </div>


            <ul class="list-group list-group-flush">

              <li class="list-group-item py-1 px-0">
                <div class="row">
                  <div class="col">
                    <span class="d-block w-100" id="summaryPlan"></span>
                    <small class="planMonthly">{{ __('misc.billed_monthly') }}</small>
                    <small class="planYearly display-none">{{ __('misc.billed_yearly') }}</small>
                  </div>
                </div>
              </li>

              	<li class="list-group-item py-1 px-0">
                  <div class="row">
                    <div class="col">
                      <small>{{ __('misc.subtotal') }}:</small>
                    </div>
                    <div class="col-auto">
                      <small class="font-weight-bold" id="subtotal"></small>
                    </div>
                  </div>
                </li>

            @if (auth()->user()->isTaxable()->count())

            	@foreach (auth()->user()->isTaxable() as $tax)
      					<li class="list-group-item py-1 px-0 isTaxable">
          	    <div class="row">
          	      <div class="col">
          	        <small>{{ $tax->name }} {{ $tax->percentage }}%:</small>
          	      </div>
          	      <div class="col-auto percentageAppliedTax{{$loop->iteration}}" data="{{ $tax->percentage }}">
          	        <small class="font-weight-bold">
          	        {{ $settings->currency_position == 'left' ? $settings->currency_symbol : null }}<span class="amount{{$loop->iteration}}"></span>{{ $settings->currency_position == 'right' ? $settings->currency_symbol : null }}
          	        </small>
          	      </div>
          	    </div>
          	  </li>
              @endforeach
            @endif

          	<li class="list-group-item py-1 px-0">
              <div class="row">
                <div class="col">
                  <small class="fw-bold">{{ __('misc.total') }}:</small>
                </div>
                <div class="col-auto fw-bold">
                  <small><span id="total"></span> {{ $settings->currency_code }}</small>
                </div>
              </div>
            </li>
          </ul>

          <small class="d-block mb-3 text-muted">
            {!! __('misc.agree_subscription', ['terms' => '<a href="'.$settings->link_terms.'" target="_blank" class="text-decoration-underline text-dark fw-bold">'. __('misc.terms_services') .'</a>']) !!}
          </small>

          <div class="alert alert-danger py-2 display-none" id="errorPurchase">
              <ul class="list-unstyled m-0" id="showErrorsPurchase"></ul>
            </div>

            <button type="submit" class="btn btn-success w-100" id="subscribe"><i></i> {{ __('misc.pay') }}</button>
            <div class="w-100 d-block text-center">
              <button type="button" class="btn btn-link e-none text-decoration-none text-reset" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
            </div>
          </div>

          </form>
        </div><!-- row -->
      </div><!-- container -->


      </div><!-- modal-body -->
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
</div><!-- modal -->
@endif
@endsection

@section('javascript')
  @auth
    <script src="{{ asset('public/js/subscription.js') }}?v={{$settings->version}}"></script>
  @endauth

  <script type="text/javascript">
  $('#plan').change(function () {
	  if ($(this).is(":checked")) {
	    $('.planMonthly').hide();
	    $('.planYearly').show();
	    $('#interval').val('year');
	  } else {
	    $('.planMonthly').show();
	    $('.planYearly').hide();
	    $('#interval').val('month');
	  }
	});
  </script>
@endsection

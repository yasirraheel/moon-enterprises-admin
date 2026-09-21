@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('misc.purchases') }} ({{$data->total()}})</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

          <div class="table-responsive p-0">
            <table class="table table-hover">
               <tbody>

               	@if( $data->total() !=  0 && $data->count() != 0 )
                   <tr>
                      <th class="active">{{ __('misc.thumbnail') }}</th>
                      <th class="active">{{ __('admin.title') }}</th>
                      <th class="active">{{ __('misc.uploaded_by') }}</th>
                      <th class="active">{{ __('misc.buyer') }}</th>
                      <th class="active">{{ __('misc.invoice') }}</th>
                      <th class="active">{{ __('misc.price') }}</th>
                      <th class="active">{{ __('misc.earnings') }} ({{ __('admin.role_admin') }})</th>
                      <th class="active">{{ __('admin.date') }}</th>
                    </tr><!-- /.TR -->


                  @foreach ($data as $purchase)

                    @php

                    if (null !== $purchase->images) {

                      $image_photo = Storage::url(config('path.thumbnail').$purchase->images->thumbnail);
                      $image_title = $purchase->images->title;
                      $image_url   = url('photo', $purchase->images->id);

                      $purchase_username = $purchase->user() ? $purchase->user()->username : __('misc.not_available');
                      $purchase_email = $purchase->user() ? $purchase->user()->email : __('misc.not_available');

                      $uploaded_by = $purchase->images->user()->username;
                      $uploaded_by_url = url($uploaded_by);

                    } else {
                      $image_photo = null;

                      $_purchase_username = User::whereId($purchase->user_id)->first();
                      $purchase_username = $_purchase_username->username;

                      $_purchase_email = User::whereId($purchase->user_id)->first();
                      $purchase_email = $_purchase_email->email;
                    }

                    @endphp

                    <tr>
                      <td>
												@if ($image_photo)
													<img src="{{$image_photo}}" class="rounded" width="50" onerror="" />
												@else
													{{ __('misc.not_available') }}
												@endif

											</td>
                      <td>
										 @if ($image_photo)
												<a href="{{ $image_url }}" title="{{$image_title}}" target="_blank">{{ str_limit($image_title, 20, '...') }} <i class="bi-box-arrow-up-right"></i></a>
											@else
												{{ __('misc.not_available') }}
											@endif
											</td>
                      <td>
												@if ($image_photo)
												<a href="{{$uploaded_by_url}}" target="_blank">{{$uploaded_by}} <i class="bi-box-arrow-up-right"></i></a>
											@else
												{{ __('misc.not_available') }}
											@endif
											</td>
                      <td><a href="{{url($purchase_username)}}" target="_blank">{{$purchase_username}} <i class="bi-box-arrow-up-right"></i></a></td>
                      <td>
                        @if ($purchase->invoice)
                        <a href="{{ url('invoice', $purchase->invoice->id) }}" target="_blank">
                          <i class="bi-receipt me-1"></i>{{ __('misc.view') }} <i class="bi-box-arrow-up-right"></i>
                        </a>
                        @else 
                        {{ __('misc.not_available') }}
                        @endif
                         </td>
                      <td>
												{{ Helper::amountFormatDecimal($purchase->price) }}

												@if ($purchase->mode == 'subscription')
                        <span class="ms-1 text-muted showTooltip" title="{{__('misc.via_subscription')}}">
                          <i class="fa fa-info-circle"></i>
                        </span>
                        @endif
											</td>
                      <td>{{ Helper::amountFormatDecimal($purchase->earning_net_admin) }}

                        @if ($purchase->referred_commission)
                          <span class="ms-1 text-muted showTooltip" title="{{__('misc.referral_commission_applied')}}">
                            <i class="fa fa-info-circle"></i>
                          </span>
                        @endif
                      </td>
                      <td>{{ date('d M, Y', strtotime($purchase->date)) }}</td>
                    </tr><!-- /.TR -->
                    @endforeach

                    @else
                    	<h5 class="text-center p-5 text-muted fw-light m-0">{{ __('misc.no_results_found') }}</h5>
                    @endif

                  </tbody>
                </table>

                </div><!-- /.table responsive -->

				 </div><!-- card-body -->
 			</div><!-- card  -->

      {{ $data->onEachSide(0)->links() }}
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection

@section('javascript')

<script type="text/javascript"></script>
  @endsection

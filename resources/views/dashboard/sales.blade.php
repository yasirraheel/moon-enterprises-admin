@extends('layouts.app')

@section('title'){{ __('misc.sales') }} -@endsection

@section('content')
    <section class="section section-sm">

        <div class="container py-5">

            <div class="row">

                <div class="col-md-3">
                    @include('users.navbar-settings')
                </div>

                <div class="col-md-9 mb-5 mb-lg-0">

                    <h5 class="d-inline-block mb-4">{{ __('misc.sales') }} ({{ $data->total() }})</h5>

                    @if ($data->count() != 0)
                        <div class="card shadow-sm">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <th class="active">ID</th>
                                        <th class="active">{{ __('misc.thumbnail') }}</th>
                                        <th class="active">{{ __('admin.title') }}</th>
                                        <th class="active">{{ __('misc.buyer') }}</th>
                                        <th class="active">{{ __('misc.payment_gateway') }}</th>
                                        <th class="active">{{ __('admin.type') }}</th>
                                        <th class="active">{{ __('misc.license') }}</th>
                                        <th class="active">{{ __('misc.price') }}</th>
                                        <th class="active">{{ __('misc.earnings') }}</th>
                                        <th class="active">{{ __('admin.date') }}</th>
                                    </thead>

                                    <tbody>
                                        @foreach ($data as $purchase)
                                            @php
                                                
                                                if (null !== $purchase->images) {
                                                    $image_photo = Storage::url(config('path.thumbnail') . $purchase->images->thumbnail);
                                                    $image_title = $purchase->images->title;
                                                    $image_url = url('photo', $purchase->images->id);
                                                
                                                    $purchase_username = $purchase->author->username;
                                                    $purchase_email = $purchase->author->email;
                                                } else {
                                                    $image_photo = null;
                                                    $image_title = __('misc.not_available');
                                                    $image_url = 'javascript:void(0);';
                                                
                                                    $_purchase_username = User::whereId($purchase->user_id)->first();
                                                    $purchase_username = $_purchase_username->username;
                                                }
                                                
                                                switch ($purchase->type) {
                                                    case 'small':
                                                        $type = __('misc.small_photo');
                                                        break;
                                                    case 'medium':
                                                        $type = __('misc.medium_photo');
                                                        break;
                                                    case 'large':
                                                        $type = __('misc.large_photo');
                                                        break;
                                                    case 'vector':
                                                        $type = __('misc.vector_graphic');
                                                        break;
                                                }
                                                
                                                switch ($purchase->license) {
                                                    case 'regular':
                                                        $license = __('misc.regular');
                                                        break;
                                                    case 'extended':
                                                        $license = __('misc.extended');
                                                        break;
                                                }
                                                
                                            @endphp

                                            <tr>
                                                <td>{{ $purchase->id }}</td>
                                                <td><img src="{{ $image_photo }}" width="50" onerror="" /></td>
                                                <td><a href="{{ $image_url }}"
                                                        title="{{ $image_title }}">{{ str_limit($image_title, 5, '...') }}
                                                        <i class="fa fa-external-link-square"></i></a></td>
                                                <td><a href="{{ url($purchase_username) }}"
                                                        target="_blank">{{ $purchase_username }}</a></td>
                                                <td>{{ ($purchase->payment_gateway == 'Wallet' ? __('misc.wallet') : $purchase->payment_gateway) ?? __('misc.not_available') }}
                                                </td>
                                                <td>{{ $type }}</td>
                                                <td>{{ $license }}</td>
                                                <td>
                                                    {{ Helper::amountFormatDecimal($purchase->price) }}

                                                    @if ($purchase->mode == 'subscription')
                                                        <i class="fa fa-info-circle text-muted showTooltip"
                                                            title="{{ __('misc.via_subscription') }}"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ Helper::amountFormatDecimal($purchase->earning_net_seller) }}

                                                    @if ($purchase->percentage_applied)
                                                        <i class="fa fa-info-circle text-muted showTooltip"
                                                            title="{{ __('misc.percentage_applied') }} {{ $purchase->percentage_applied }} {{ __('misc.platform') }} @if ($purchase->direct_payment) ({{ __('misc.direct_payment') }}) @endif"></i>
                                                    @endif
                                                </td>
                                                <td>{{ date('d M, Y', strtotime($purchase->date)) }}</td>
                                            </tr><!-- /.TR -->
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><!-- table-responsive -->
                        </div><!-- card -->

                        @if ($data->hasPages())
                            <div class="mt-3">
                                {{ $data->links() }}
                            </div>
                        @endif
                    @else
                        <h3 class="mt-0 fw-light">
                            {{ __('misc.no_results_found') }}
                        </h3>
                    @endif

                </div><!-- end col-md-6 -->
            </div>
        </div>
    </section>
@endsection

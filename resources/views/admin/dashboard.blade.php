@extends('admin.layout')

@section('content')
	<h4 class="mb-4 fw-light">{{ __('admin.dashboard') }} <small class="fs-6">v{{$settings->version}}</small></h4>

<div class="content">
	<div class="row">

		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3>
						<i class="fa fa-shopping-cart me-2 icon-dashboard"></i>
						<span>{{ number_format($totalSales) }}</span>
					</h3>
					<small>{{ trans('misc.total_sales') }}</small>

					<span class="icon-wrap icon--admin"><i class="bi bi-cart2"></i></span>
				</div>
			</div><!-- card 1 -->
		</div><!-- col-lg-3 -->

		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3 class="{{$totalEarnings > 0 ? 'text-success' : 'text-danger' }}">
						<i class="fas fa-hand-holding-usd me-2 icon-dashboard"></i>
						Rs. {{ number_format($totalEarnings, 2, '.', ',') }}
					</h3>
					<small>Business Earnings (Services + Orders - Refunds)</small>

					<span class="icon-wrap icon--admin"><i class="bi bi-cash-stack"></i></span>
				</div>
			</div><!-- card 1 -->
		</div><!-- col-lg-3 -->

		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3><i class="fa fa-users me-2 icon-dashboard"></i> {{ $totalUsers }}</h3>
					<small>{{ trans('admin.members') }}</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-people"></i></span>
				</div>
			</div><!-- card 1 -->
		</div><!-- col-lg-3 -->

	</div><!-- row -->

	<!-- Business Statistics Row -->
	<div class="row">
		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3 class="text-success">
						<i class="fas fa-arrow-up me-2 icon-dashboard"></i>
						Rs. {{ number_format($depositTotal, 2, '.', ',') }}
					</h3>
					<small>Total Deposits</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-arrow-up-circle"></i></span>
				</div>
			</div>
		</div>

		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3 class="text-danger">
						<i class="fas fa-arrow-down me-2 icon-dashboard"></i>
						Rs. {{ number_format($withdrawalTotal, 2, '.', ',') }}
					</h3>
					<small>Total Withdrawals</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-arrow-down-circle"></i></span>
				</div>
			</div>
		</div>

		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3 class="text-info">
						<i class="fas fa-user-shield me-2 icon-dashboard"></i>
						Rs. {{ number_format($netAdminCredits, 2, '.', ',') }}
					</h3>
					<small>Net Admin Credits</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-person-gear"></i></span>
				</div>
			</div>
		</div>

		<div class="col-lg-3 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h3 class="text-warning">
						<i class="fas fa-exchange-alt me-2 icon-dashboard"></i>
						{{ $totalTransactions }}
					</h3>
					<small>Total Transactions</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-arrow-left-right"></i></span>
				</div>
			</div>
		</div>
	</div><!-- row -->

	<!-- Detailed Breakdown Row -->
	<div class="row">
		<div class="col-lg-2 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body text-center">
					<h6 class="text-success">Rs. {{ number_format($depositTotal, 2, '.', ',') }}</h6>
					<small>Deposits</small>
					<div class="mt-2">
						<span class="badge bg-success">{{ $totalDeposits }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-2 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body text-center">
					<h6 class="text-danger">Rs. {{ number_format($withdrawalTotal, 2, '.', ',') }}</h6>
					<small>Withdrawals</small>
					<div class="mt-2">
						<span class="badge bg-danger">{{ $totalWithdrawals }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-2 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body text-center">
					<h6 class="text-primary">Rs. {{ number_format($paidServiceTotal, 2, '.', ',') }}</h6>
					<small>Paid Services</small>
					<div class="mt-2">
						<span class="badge bg-primary">{{ $totalPaidServices }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-2 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body text-center">
					<h6 class="text-warning">Rs. {{ number_format($orderTotal, 2, '.', ',') }}</h6>
					<small>Orders</small>
					<div class="mt-2">
						<span class="badge bg-warning">{{ $totalOrders }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-2 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body text-center">
					<h6 class="text-info">Rs. {{ number_format($adminCreditTotal, 2, '.', ',') }}</h6>
					<small>Admin Credits</small>
					<div class="mt-2">
						<span class="badge bg-info">{{ $adminCreditTotal > 0 ? 'Given' : '0' }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-2 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body text-center">
					<h6 class="text-secondary">Rs. {{ number_format($refundTotal, 2, '.', ',') }}</h6>
					<small>Refunds</small>
					<div class="mt-2">
						<span class="badge bg-secondary">Deducted</span>
					</div>
				</div>
			</div>
		</div>
	</div><!-- row -->

	<!-- Earnings Statistics Row -->
	<div class="row">
		<div class="col-lg-4 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h6 class="{{$stat_revenue_today > 0 ? 'text-success' : 'text-danger' }}">
						Rs. {{ number_format($stat_revenue_today, 2, '.', ',') }}

							{!! Helper::PercentageIncreaseDecrease($stat_revenue_today, $stat_revenue_yesterday) !!}
					</h6>
					<small>Earnings Today (Services + Orders - Refunds)</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-graph-up-arrow"></i></span>
				</div>
			</div><!-- card 1 -->
		</div><!-- col-lg-4 -->

		<div class="col-lg-4 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h6 class="{{$stat_revenue_week > 0 ? 'text-success' : 'text-danger' }}">
						Rs. {{ number_format($stat_revenue_week, 2, '.', ',') }}

							{!! Helper::PercentageIncreaseDecrease($stat_revenue_week, $stat_revenue_last_week) !!}
					</h6>
					<small>Earnings This Week</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-graph-up"></i></span>
				</div>
			</div><!-- card 1 -->
		</div><!-- col-lg-4 -->

		<div class="col-lg-4 mb-3">
			<div class="card shadow-custom border-0 overflow-hidden">
				<div class="card-body">
					<h6 class="{{$stat_revenue_month > 0 ? 'text-success' : 'text-danger' }}">
						Rs. {{ number_format($stat_revenue_month, 2, '.', ',') }}

							{!! Helper::PercentageIncreaseDecrease($stat_revenue_month, $stat_revenue_last_month) !!}
					</h6>
					<small>Earnings This Month</small>
					<span class="icon-wrap icon--admin"><i class="bi bi-graph-up-arrow"></i></span>
				</div>
			</div><!-- card 1 -->
		</div><!-- col-lg-4 -->
	</div><!-- row -->

	<!-- Charts and Analytics Row -->
	<div class="row">
		<div class="col-lg-6 mb-4">
			<div class="card shadow-custom border-0">
				<div class="card-body">
					<h6 class="mb-4 fw-light">Business Earnings (Last 30 Days)</h6>
					<div style="height: 350px">
						<canvas id="Chart"></canvas>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-6 mb-4">
			<div class="card shadow-custom border-0">
				<div class="card-body">
					<h6 class="mb-4 fw-light">{{ trans('misc.sales_last_30_days') }}</h6>
					<div style="height: 350px">
						<canvas id="ChartSales"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div><!-- row -->

	<!-- Latest Members Row -->
	<div class="row">
		<div class="col-12 mb-4">
			<div class="card shadow-custom border-0">
				<div class="card-body">
					<h6 class="mb-4 fw-light">{{ trans('admin.latest_members') }}</h6>

					@foreach (User::where('username', '!=', '03001234567')->where('username', '!=', '3001234567')->orderBy('id','DESC')->take(5)->get() as $user)
						<div class="d-flex mb-3">
							<div class="flex-shrink-0">
								<div class="bg-light d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px;">
									<i class="bi bi-person text-muted"></i>
								</div>
							</div>
							<div class="flex-grow-1 ms-3">
								<h6 class="m-0 fw-light text-break">
									<a href="{{ url($user->username) }}" target="_blank">
										{{ $user->full_name ?: $user->username }}
									</a>
									<small class="float-end badge rounded-pill bg-{{ $user->status == 'active' ? 'success' : ($user->status == 'pending' ? 'info' : 'warning') }}">
										{{ $user->status == 'active' ? trans('misc.active') : ($user->status == 'pending' ? trans('misc.pending') : trans('admin.suspended')) }}
									</small>
								</h6>
								<div class="w-100 small">
									{{ '@'.$user->username }} / {{ Helper::formatDate($user->date) }}
								</div>
							</div>
						</div>
					@endforeach

					@if ($totalUsers == 0)
						<small>{{ trans('admin.no_result') }}</small>
					@endif
				</div>

				@if ($totalUsers != 0)
				<div class="card-footer bg-light border-0 p-3">
					<a href="{{ url('panel/admin/members') }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center arrow">
						{{ trans('admin.view_all_members') }}
					</a>
				</div>
				@endif
			</div>
		</div>
	</div><!-- row -->



	</div><!-- end row -->
</div><!-- end content -->
@endsection

@section('javascript')
  <script src="{{ asset('public/js/Chart.min.js') }}"></script>

  <script type="text/javascript">

function decimalFormat(nStr)
{
  @if ($settings->decimal_format == 'dot')
	 $decimalDot = '.';
	 $decimalComma = ',';
	 @else
	 $decimalDot = ',';
	 $decimalComma = '.';
	 @endif

   @if ($settings->currency_position == 'left')
   currency_symbol_left = '{{$settings->currency_symbol}}';
   currency_symbol_right = '';
   @else
   currency_symbol_right = '{{$settings->currency_symbol}}';
   currency_symbol_left = '';
   @endif

    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? $decimalDot + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + $decimalComma + '$2');
    }
    return currency_symbol_left + x1 + x2 + currency_symbol_right;
  }

  function transparentize(color, opacity) {
			var alpha = opacity === undefined ? 0.5 : 1 - opacity;
			return Color(color).alpha(alpha).rgbString();
		}

  var init = document.getElementById("Chart").getContext('2d');

  const gradient = init.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, '#268707');
                    gradient.addColorStop(1, '#2687072e');

  const lineOptions = {
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        hitRadius: 5,
                        pointHoverBorderWidth: 3
                    }

  var ChartArea = new Chart(init, {
      type: 'line',
      data: {
          labels: [{!!$label!!}],
          datasets: [{
              label: 'Business Earnings',
              backgroundColor: gradient,
              borderColor: '#268707',
              data: [{!!$data!!}],
              borderWidth: 2,
              fill: true,
              lineTension: 0.4,
              ...lineOptions
          }]
      },
      options: {
          scales: {
              yAxes: [{
                  ticks: {
                    min: 0, // it is for ignoring negative step.
                     display: true,
                      maxTicksLimit: 8,
                      padding: 10,
                      beginAtZero: true,
                      callback: function(value, index, values) {
                          return '@if($settings->currency_position == 'left'){{ $settings->currency_symbol }}@endif' + value + '@if($settings->currency_position == 'right'){{ $settings->currency_symbol }}@endif';
                      }
                  }
              }],
              xAxes: [{
                gridLines: {
                  display:false
                },
                display: true,
                ticks: {
                  maxTicksLimit: 15,
                  padding: 5,
                }
              }]
          },
          tooltips: {
            mode: 'index',
            intersect: false,
            reverse: true,
            backgroundColor: '#000',
            xPadding: 16,
            yPadding: 16,
            cornerRadius: 4,
            caretSize: 7,
              callbacks: {
                  label: function(t, d) {
                      var xLabel = d.datasets[t.datasetIndex].label;
                      var yLabel = t.yLabel == 0 ? decimalFormat(t.yLabel) : decimalFormat(t.yLabel.toFixed(2));
                      return xLabel + ': ' + yLabel;
                  }
              },
          },
          hover: {
            mode: 'index',
            intersect: false
          },
          legend: {
              display: false
          },
          responsive: true,
          maintainAspectRatio: false
      }
  });

	// Sales last 30 days
	var sales = document.getElementById("ChartSales").getContext('2d');

  const gradientSales = sales.createLinearGradient(0, 0, 0, 300);
                    gradientSales.addColorStop(0, '#268707');
                    gradientSales.addColorStop(1, '#2687072e');

  const lineOptionsSales = {
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        hitRadius: 5,
                        pointHoverBorderWidth: 3
                    }

  var ChartArea = new Chart(sales, {
      type: 'bar',
      data: {
          labels: [{!!$label!!}],
          datasets: [{
              label: '{{trans('misc.sales')}}',
              backgroundColor: '#268707',
              borderColor: '#268707',
              data: [{!!$datalastSales!!}],
              borderWidth: 2,
              fill: true,
              lineTension: 0.4,
              ...lineOptionsSales
          }]
      },
      options: {
          scales: {
              yAxes: [{
                  ticks: {
                    min: 0, // it is for ignoring negative step.
                     display: true,
                      maxTicksLimit: 8,
                      padding: 10,
                      beginAtZero: true,
                      callback: function(value, index, values) {
                          return value;
                      }
                  }
              }],
              xAxes: [{
                gridLines: {
                  display:false
                },
                display: true,
                ticks: {
                  maxTicksLimit: 15,
                  padding: 5,
                }
              }]
          },
          tooltips: {
            mode: 'index',
            intersect: false,
            reverse: true,
            backgroundColor: '#000',
            xPadding: 16,
            yPadding: 16,
            cornerRadius: 4,
            caretSize: 7,
              callbacks: {
                  label: function(t, d) {
                      var xLabel = d.datasets[t.datasetIndex].label;
                      var yLabel = t.yLabel;
                      return xLabel + ': ' + yLabel;
                  }
              },
          },
          hover: {
            mode: 'index',
            intersect: false
          },
          legend: {
              display: false
          },
          responsive: true,
          maintainAspectRatio: false
      }
  });
  </script>
  @endsection

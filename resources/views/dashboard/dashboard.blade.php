@extends('layouts.app')

@section('title') {{ trans('admin.dashboard') }} - @endsection

@section('content')
<section class="section section-sm">

<div class="container-custom container pt-5">
  <div class="row">

    <div class="col-md-3">
      @include('users.navbar-settings')
    </div>

		<!-- Col MD -->
		<div class="col-md-9">

      <h5 class="mb-4">{{ trans('admin.dashboard') }}</h5>

			<div class="content">
				<div class="row">
					<div class="col-lg-4 mb-2">
						<div class="card shadow-sm overflow-hidden">
							<div class="card-body">
								<h5><i class="fas fa-wallet me-2 icon-dashboard"></i> {{ Helper::amountFormatDecimal(auth()->user()->balance) }}</h5>
								<small>{{ trans('misc.balance') }}</small>
                <span class="icon-wrap opacity-25">
                  <i class="bi-wallet2"></i>
                </span>
							</div>
						</div><!-- card 1 -->
					</div><!-- col-lg-4 -->


				</div><!-- end row -->
			</div><!-- end content -->

		</div><!-- /COL MD -->
  </div><!-- row -->
 </div><!-- container -->
</section>
 <!-- container wrap-ui -->
@endsection

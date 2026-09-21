@extends('layouts.app')

@section('title') {{ trans('users.account_settings') }} - @endsection

@section('content')
<section class="section section-sm">

<div class="container-custom container pt-5">
<div class="row">

  <div class="col-md-3">
    @include('users.navbar-settings')
  </div>

			<!-- Col MD -->
		<div class="col-md-9">

			@if (session('notification'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
            	<i class="bi bi-check2 me-1"></i>	{{ session('notification') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
            		</div>
            	@endif

			@include('errors.errors-forms')

      <h5 class="mb-4">{{ trans('users.account_settings') }}</h5>

		<!-- ***** FORM ***** -->
       <form action="{{ url('account') }}" method="post" enctype="multipart/form-data">

        <input type="hidden" name="_token" value="{{ csrf_token() }}">

		<!-- Personal Information Section -->
		<div class="mb-4">
			<h6 class="mb-3 text-primary">
				<i class="bi bi-person me-2"></i>{{ trans('misc.personal_information') }}
			</h6>
			
			<div class="row">
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="text" required class="form-control" id="inputname" value="{{auth()->user()->full_name}}" name="full_name" placeholder="{{ trans('misc.full_name_misc') }}">
						<label for="inputname">{{ trans('misc.full_name_misc') }}</label>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="text" required class="form-control" id="inputusername" value="{{auth()->user()->username}}" name="username" placeholder="{{ trans('misc.username_misc') }}">
						<label for="inputusername">{{ trans('misc.username_misc') }}</label>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="text" class="form-control" id="inputCity" value="{{auth()->user()->city}}" name="city" placeholder="City">
						<label for="inputCity">City</label>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="text" class="form-control" id="inputaccount_no" value="{{auth()->user()->account_no}}" name="account_no" placeholder="Account No">
						<label for="inputaccount_no">Account No</label>
					</div>
				</div>
			</div>
		</div>

		<!-- Profile Image Section -->
		<div class="mb-4">
			<h6 class="mb-3 text-primary">
				<i class="bi bi-image me-2"></i>{{ trans('misc.profile_image') }}
			</h6>
			
			<div class="row">
				<div class="col-md-12">
					<div class="d-flex align-items-center">
						<div class="me-3">
							@if(auth()->user()->avatar)
								<img src="{{ auth()->user()->avatar_url }}" alt="Profile Image" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
							@else
								<div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
									<i class="bi bi-person text-muted" style="font-size: 2rem;"></i>
								</div>
							@endif
						</div>
						<div class="flex-grow-1">
							<div class="file-upload-wrapper">
								<input type="file" class="form-control" id="profile_image" name="avatar" accept="image/*" style="display: none;">
								<button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('profile_image').click()">
									<i class="bi bi-cloud-upload me-1"></i> {{ trans('misc.choose_file') }}
								</button>
								<span id="file-name" class="ms-2 text-muted"></span>
							</div>
							<small class="text-muted d-block mt-1">{{ trans('misc.profile_image_help') }}</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Security Settings Section -->
		<div class="mb-4">
			<h6 class="mb-3 text-primary">
				<i class="bi bi-shield-lock me-2"></i>{{ trans('auth.password') }}
			</h6>
			
			<div class="row">
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="password" class="form-control" id="input-oldpassword" name="old_password" placeholder="{{ trans('misc.old_password') }}">
						<label for="input-oldpassword">{{ trans('misc.old_password') }}</label>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="password" class="form-control" id="input-password" name="password" placeholder="{{ trans('misc.new_password') }}">
						<label for="input-password">{{ trans('misc.new_password') }}</label>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6">
					<div class="form-floating mb-3">
						<input type="password" class="form-control" id="input-password-confirm" name="password_confirmation" placeholder="{{ trans('auth.confirm_password') }}">
						<label for="input-password-confirm">{{ trans('auth.confirm_password') }}</label>
					</div>
				</div>
			</div>
		</div>

		<!-- Two-Factor Authentication Section -->
		<div class="mb-4">
			<h6 class="mb-3 text-primary">
				<i class="bi bi-shield-check me-2"></i>{{ trans('misc.security_settings') }}
			</h6>
			
			<div class="form-check form-switch form-switch-md mb-3">
				<input class="form-check-input" @if (auth()->user()->two_factor_auth == 'yes') checked @endif name="two_factor_auth" type="checkbox" value="yes" id="flexSwitchCheckDefault">
				<label class="form-check-label" for="flexSwitchCheckDefault">
					{{ trans('misc.two_step_auth') }} 
					<i class="bi bi-info-circle ms-1 text-muted showTooltip" title="{{ trans('misc.two_step_auth_info') }}"></i>
				</label>
			</div>
		</div>

		<!-- Submit Button -->
		<div class="d-grid gap-2">
			<button type="submit" id="buttonSubmit" class="btn btn-lg btn-custom">
				<i class="bi bi-check-circle me-2"></i>{{ trans('misc.save_changes') }}
			</button>
		</div>

         @if (auth()->id() != 1)
           <div class="d-block text-center mt-3">
           		<a href="{{url('account/delete')}}" class="text-danger">{{trans('users.delete_account')}}</a>
           </div>
           @endif
       </form><!-- ***** END FORM ***** -->

  </div><!-- /COL MD -->
  </div><!-- row -->
</div><!-- container -->
</section>
@endsection

@section('javascript')
<script type="text/javascript">

$('#authorExclusive').on('change', function() {
  if ($(this).val() == 'yes') {
    $('#percentage').html('* {{ trans('misc.user_gain', ['percentage' => (100 - $settings->fee_commission)]) }}');

  } else {
      $('#percentage').html('* {{ trans('misc.user_gain', ['percentage' => (100 - $settings->fee_commission_non_exclusive)]) }}');
  }
});

// Profile image preview and file name display
$('#profile_image').on('change', function() {
  const file = this.files[0];
  if (file) {
    // Display file name
    $('#file-name').text(file.name);
    
    // Preview image
    const reader = new FileReader();
    reader.onload = function(e) {
      $('.rounded-circle').attr('src', e.target.result);
    };
    reader.readAsDataURL(file);
  } else {
    $('#file-name').text('');
  }
});

</script>
@endsection

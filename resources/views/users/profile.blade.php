@extends('layouts.app')

@section('title'){{ $title }} -@endsection

@section('content')
    <!-- *********** COVER ************* -->
    <div class="container-fluid cover-user"
        style="{{ $user->cover ? "background: url('" . Storage::url(config('path.cover') . $user->cover) . "') no-repeat center center #232a29; background-size: cover;" : 'background: #232a29;' }}">

        @if (auth()->check() && auth()->id() == $user->id)
            <form style="z-index: 100;" action="{{ url('upload/cover') }}" method="POST" id="formCover" accept-charset="UTF-8"
                enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="button" class="btn-upload-cover" id="cover_file" style="margin-top: 10px;">
                    <i class="bi bi-camera"></i> <span
                        class="d-none d-lg-inline-block ms-1">{{ trans('misc.change') }}</span>
                </button>
                <input type="file" name="photo" id="uploadCover" accept="image/*" style="visibility: hidden;">
            </form><!-- *********** COVER ************* -->
        @endif
    </div>
    <!-- *********** COVER ************* -->

    <div class="container pb-5 pt-3 mb-5">

        <div class="row">
            <!-- Col MD -->
            <div class="col-md-12 position-relative">

                <div class="text-start">

                    <div class="row user-profile">

                        <div class="col-md-2 text-center">

                            <div class="d-inline-block position-relative">

                                @if (auth()->check() && auth()->id() == $user->id)
                                    <form action="{{ url('upload/avatar') }}" method="POST" id="formAvatar"
                                        accept-charset="UTF-8" enctype="multipart/form-data">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="button" class="btn-upload-avatar" id="avatar_file">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                        <input type="file" name="photo" id="uploadAvatar" accept="image/*"
                                            style="visibility: hidden;">
                                    </form><!-- *********** AVATAR ************* -->
                                @endif

                                <div class="mb-3 shadow rounded-circle avatar-profile avatarUser profile-user-over bg-light d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar_url }}" alt="Profile Image" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <div class="col-md-10 user-data">

                            <div class="d-block w-100 mb-3">
                                <h1 class="name-user-profile m-0">

                                    {{ $user->full_name ?: $user->username }}

                                    @if (
                                        ($user->author_exclusive == 'yes' && $settings->who_can_sell == 'all') ||
                                            ($user->author_exclusive == 'yes' && $settings->who_can_sell == 'admin' && $user->isSuperAdmin()))
                                        <small>
                                            <i class="bi bi-gem showTooltip gem-exclusive"
                                                title="{{ trans('misc.exclusive_author') }}"></i>
                                        </small>
                                    @endif

                                </h1>
                            </div>

                            <div class="d-block w-100 mb-2">
                                @if (auth()->check() && $user->id != auth()->id())
                                    <button type="button"
                                        class="btn btn-sm @if ($activeFollow) btn-custom  @else btn-outline-custom @endif btn-follow me-1 btnFollow {{ $activeFollow }}"
                                        data-id="{{ $user->id }}" data-follow="{{ trans('users.follow') }}"
                                        data-following="{{ trans('users.following') }}">
                                        <i class="bi bi{{ $icoFollow }} me-1"></i> {{ $textFollow }}
                                    </button>
                                @endif

                                @if (
                                    (auth()->check() && $user->id != auth()->id() && $user->paypal_account != '') ||
                                        (auth()->guest() && $user->paypal_account != ''))
                                    <button type="button"
                                        class="btn btn-sm bg-white border e-none btn-category showTooltip" id="btnFormPP"
                                        id="btnFormPP" title="{{ trans('misc.buy_coffee') }}">
                                        <i class="bi bi-paypal" style="color: #003087"></i> @guest
                                        {{ trans('misc.coffee') }} @endguest
                                    </button>
                                @endif

                                @if (auth()->check() && $user->id != auth()->id())
                                    <a href="#" class="btn btn-sm bg-white border e-none btn-category"
                                        data-bs-toggle="modal" data-bs-target="#reportUser"
                                        title="{{ trans('misc.report') }}">
                                        <i class="bi bi-flag"></i>
                                    </a>
                                @endif

                                <a href="javascript:void(0);" class="btn btn-sm bg-white border e-none btn-category showTooltip" title="{{ trans('misc.share') }}" id="shareBtn">
                                    <i class="bi-box-arrow-up"></i>
                                </a>
                            </div>

                            @if ($user->countries_id != '')
                                <div class="d-block w-100 mb-2">
                                    <small>
                                        <i class="bi bi-geo-alt me-1"></i> {{ $user->country()->country_name }}
                                    </small>
                                </div>
                            @endif



                        </div><!-- col-6 -->

                    </div><!-- row -->

                    <ul class="nav pb-3 mb-4 nav-fill mt-5" id="navProfile">

                        <li class="nav-item">
                            <a href="{{ url($user->username) }}"
                                class="text-muted ps-0 nav-link link-profile @if (request()->is($user->username)) active @endif">
                                <i class="bi bi-image me-1 d-none d-lg-inline-block"></i> {{ trans('misc.images') }} <span
                                    class="d-none d-lg-inline-block">{{ Helper::formatNumber($user->images_count) }}</span>
                            </a>
                        </li><!-- End Li -->

                        <li class="nav-item">
                            <a href="{{ url($user->username, 'followers') }}"
                                class="text-muted nav-link link-profile @if (request()->is($user->username . '/followers')) active @endif">
                                <i class="bi bi-people me-1 d-none d-lg-inline-block"></i> {{ trans('users.followers') }}
                                <span
                                    class="d-none d-lg-inline-block">{{ Helper::formatNumber($user->followers_count) }}</span>
                            </a>
                        </li><!-- End Li -->

                        <li class="nav-item">
                            <a href="{{ url($user->username, 'following') }}"
                                class="text-muted nav-link link-profile @if (request()->is($user->username . '/following')) active @endif">
                                <i class="bi bi-person me-1 d-none d-lg-inline-block"></i> {{ trans('users.following') }}
                                <span
                                    class="d-none d-lg-inline-block">{{ Helper::formatNumber($user->following_count) }}</span>
                            </a>
                        </li><!-- End Li -->

                        <li class="nav-item">
                            <a href="{{ url($user->username, 'collections') }}"
                                class="text-muted nav-link link-profile pe-0 @if (request()->is($user->username . '/collections')) active @endif">
                                <i class="bi bi-collection me-1 d-none d-lg-inline-block"></i>
                                {{ trans('misc.collections') }} <span
                                    class="d-none d-lg-inline-block">{{ Helper::formatNumber($user->collections_count) }}</span>
                            </a>
                        </li><!-- End Li -->
                    </ul>

                </div><!-- Center Div -->

                @if (isset($images) && $images->total() != 0)
                    <div class="dataResult">
                        @include('includes.images')
                        @include('includes.pagination-links')
                    </div>
                @elseif (isset($followers) && $followers->count() != 0)
                    <div class="row dataResult">
                        @include('includes.users', ['users' => $followers])
                    </div>
                @elseif (isset($following) && $following->count() != 0)
                    <div class="row dataResult">
                        @include('includes.users', ['users' => $following])
                    </div>
                @elseif (isset($collections) && $collections->count() != 0)
                    <div class="row dataResult">
                        @include('includes.collections-grid', ['data' => $collections])
                    </div>
                @else
                    <h5 class="mt-0 fw-light text-center">
                        <span class="w-100 d-block mb-2 display-1 text-muted">
                            <i class="bi bi-exclamation-circle"></i>
                        </span>

                        {{ trans('misc.no_results_found') }}
                    </h5>
                @endif
            </div><!-- /COL MD -->
        </div><!-- row -->
    </div><!-- container -->


    @if (
        (auth()->check() && $user->id != auth()->id() && $user->paypal_account != '') ||
            (auth()->guest() && $user->paypal_account != ''))
        <form id="form_pp" name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post"
            style="display:none">
            <input type="hidden" name="cmd" value="_donations">
            <input type="hidden" name="return" value="{{ url($user->username) }}">
            <input type="hidden" name="cancel_return" value="{{ url($user->username) }}">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="item_name"
                value="{{ trans('misc.support') . ' @' . $user->username }} - {{ $settings->title }}">
            <input type="hidden" name="business" value="{{ $user->paypal_account }}">
            <input type="submit">
        </form>
    @endif

    @auth
        <div class="modal fade" id="reportUser" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-center" id="myModalLabel">
                            <strong>{{ trans('misc.report') }}</strong>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- Modal header -->

                    <div class="modal-body">

                        <!-- form start -->
                        <form method="POST" action="{{ url('report/user') }}" enctype="multipart/form-data"
                            id="formReport">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id" value="{{ $user->id }}">

                            <div class="form-floating mb-3">
                                <select name="reason" class="form-select" id="input-reason">
                                    <option value="spoofing">{{ trans('admin.spoofing') }}</option>
                                    <option value="copyright">{{ trans('admin.copyright') }}</option>
                                    <option value="privacy_issue">{{ trans('admin.privacy_issue') }}</option>
                                    <option value="violent_sexual_content">{{ trans('admin.violent_sexual_content') }}
                                    </option>
                                </select>
                                <label for="input-reason">{{ trans('admin.reason') }}</label>
                            </div>

                            <button type="submit"
                                class="btn btn-custom float-end reportUser">{{ trans('misc.report') }}</button>

                        </form>

                    </div><!-- Modal body -->
                </div><!-- Modal content -->
            </div><!-- Modal dialog -->
        </div><!-- Modal -->
    @endauth

    <!-- container wrap-ui -->
@endsection

@section('javascript')
    <script type="text/javascript">
        $('#imagesFlex').flexImages({
            rowHeight: 320
        });

        let share = document.querySelector('#shareBtn');

        if (share) {
            share.addEventListener('click', event => {
                // Fallback, Tries to use API only
                // if navigator.share function is
                // available
                if (navigator.share) {
                    navigator.share({
                        // Title that occurs over
                        // web share dialog
                        title: "{{ $user->full_name ?: $user->username }}",
                        // URL to share
                        url: "{{ url($user->username) }}"
                    })
                } else {
                    // Alerts user if API not available
                    alert("Browser doesn't support this API !");
                }
            });
        }

        $('#btnFormPP').click(function(e) {
            $('#form_pp').submit();
        });

        @if (auth()->check())

            $(".reportUser").click(function(e) {
                var element = $(this);
                e.preventDefault();
                element.attr({
                    'disabled': 'true'
                });

                $('#formReport').submit();

            });

            @if (session('noty_error'))
                swal({
                    title: "{{ trans('misc.error_oops') }}",
                    text: "{{ trans('misc.already_sent_report') }}",
                    type: "error",
                    confirmButtonText: "{{ trans('users.ok') }}"
                });
            @endif

            @if (session('noty_success'))
                swal({
                    title: "{{ trans('misc.thanks') }}",
                    text: "{{ trans('misc.send_success') }}",
                    type: "success",
                    confirmButtonText: "{{ trans('users.ok') }}"
                });
            @endif
        @endif

        @if (auth()->check() && auth()->id() == $user->id)

            //<<<<<<<=================== * UPLOAD AVATAR  * ===============>>>>>>>//
            $(document).on('change', '#uploadAvatar', function() {

                $('.wrap-loader').show();
                $('#progress').show();

                (function() {
                    var bar = $('.progress-bar');
                    var percent = $('.percent');
                    var percentVal = '0%';

                    $("#formAvatar").ajaxForm({
                        dataType: 'json',
                        error: function error(responseText, statusText, xhr, $form) {
                            $('.wrap-loader').hide();
                            $('#progress').hide();
                            bar.width(percentVal);
                            percent.html(percentVal);
                            $('#uploadAvatar').val('');
                            $('.popout').addClass('popout-error').html(
                                '{{ trans('misc.error') }} (' + xhr + ')').fadeIn('500').delay(
                                '5000').fadeOut('500');
                        },
                        beforeSend: function() {
                            bar.width(percentVal);
                            percent.html(percentVal);
                        },
                        uploadProgress: function(event, position, total, percentComplete) {
                            var percentVal = percentComplete + '%';
                            bar.width(percentVal);
                            percent.html(percentVal);

                            if (percentComplete == 100) {
                                percent.html(
                                    '<span class="spinner-border spinner-custom-md mr-2"></span> {{ trans('misc.processing') }}'
                                    );
                            }
                        },
                        success: function(e) {
                            if (e) {
                                if (e.success == false) {
                                    $('.wrap-loader').hide();
                                    $('#progress').hide();
                                    bar.width(percentVal);
                                    percent.html(percentVal);

                                    var error = '';
                                    for ($key in e.errors) {
                                        error += '' + e.errors[$key] + '';
                                    }
                                    swal({
                                        title: "{{ trans('misc.error_oops') }}",
                                        text: "" + error + "",
                                        type: "error",
                                        confirmButtonText: "{{ trans('users.ok') }}"
                                    });

                                    $('#uploadAvatar').val('');

                                } else {

                                    $('#uploadAvatar').val('');
                                    $('.avatarUser').attr('src', e.avatar);
                                    $('.wrap-loader').hide();
                                    $('#progress').hide();
                                    bar.width(percentVal);
                                    percent.html(percentVal);
                                }

                            } //<-- e
                            else {
                                $('.wrap-loader').hide();
                                $('#progress').hide();
                                bar.width(percentVal);
                                percent.html(percentVal);

                                swal({
                                    title: "{{ trans('misc.error_oops') }}",
                                    text: '{{ trans('misc.error') }}',
                                    type: "error",
                                    confirmButtonText: "{{ trans('users.ok') }}"
                                });

                                $('#uploadAvatar').val('');
                            }
                        } //<----- SUCCESS
                    }).submit();
                })(); //<--- FUNCTION %
            }); //<<<<<<<--- * ON * --->>>>>>>>>>>
            //<<<<<<<=================== * UPLOAD AVATAR  * ===============>>>>>>>//

            //<<<<<<<=================== * UPLOAD COVER  * ===============>>>>>>>//
            $(document).on('change', '#uploadCover', function() {

                $('.wrap-loader').show();
                $('#progress').show();

                (function() {

                    var bar = $('.progress-bar');
                    var percent = $('.percent');
                    var percentVal = '0%';

                    $("#formCover").ajaxForm({
                        dataType: 'json',
                        error: function error(responseText, statusText, xhr, $form) {
                            $('.wrap-loader').hide();
                            $('#uploadCover').val('');
                            $('.popout').addClass('popout-error').html(
                                '{{ trans('misc.error') }} (' + xhr + ')').fadeIn('500').delay(
                                '5000').fadeOut('500');

                            $('#progress').hide();
                            bar.width(percentVal);
                            percent.html(percentVal);
                        },

                        beforeSend: function() {
                            bar.width(percentVal);
                            percent.html(percentVal);
                        },
                        uploadProgress: function(event, position, total, percentComplete) {
                            var percentVal = percentComplete + '%';
                            bar.width(percentVal);
                            percent.html(percentVal);

                            if (percentComplete == 100) {
                                percent.html(
                                    '<span class="spinner-border spinner-custom-md mr-2"></span> {{ trans('misc.processing') }}'
                                    );
                            }
                        },
                        success: function(e) {
                            if (e) {
                                if (e.success == false) {

                                    $('.wrap-loader').hide();
                                    $('#progress').hide();
                                    bar.width(percentVal);
                                    percent.html(percentVal);

                                    var error = '';
                                    for ($key in e.errors) {
                                        error += '' + e.errors[$key] + '';
                                    }
                                    swal({
                                        title: "{{ trans('misc.error_oops') }}",
                                        text: "" + error + "",
                                        type: "error",
                                        confirmButtonText: "{{ trans('users.ok') }}"
                                    });

                                    $('#uploadCover').val('');

                                } else {

                                    $('#uploadCover').val('');

                                    $('.cover-user').css({
                                        background: 'url("' + e.cover +
                                            '") center center #232a29',
                                        'background-size': 'cover'
                                    });;
                                    $('.wrap-loader').hide();
                                    $('#progress').hide();
                                    bar.width(percentVal);
                                    percent.html(percentVal);
                                }

                            } //<-- e
                            else {
                                $('.wrap-loader').hide();
                                $('#progress').hide();
                                bar.width(percentVal);
                                percent.html(percentVal);

                                swal({
                                    title: "{{ trans('misc.error_oops') }}",
                                    text: '{{ trans('misc.error') }}',
                                    type: "error",
                                    confirmButtonText: "{{ trans('users.ok') }}"
                                });

                                $('#uploadCover').val('');
                            }
                        } //<----- SUCCESS
                    }).submit();
                })(); //<--- FUNCTION %
            }); //<<<<<<<--- * ON * --->>>>>>>>>>>
            //<<<<<<<=================== * UPLOAD COVER  * ===============>>>>>>>//
        @endif
    </script>
@endsection

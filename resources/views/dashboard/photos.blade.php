@extends('layouts.app')

@section('title'){{ trans_choice('misc.images_plural', 0) }} -@endsection

@section('content')
    <section class="section section-sm">

        <div class="container py-5">

            <div class="row">

                <div class="col-md-3">
                    @include('users.navbar-settings')
                </div>

                <div class="col-md-9 mb-5 mb-lg-0">

                    <div class="d-block mb-4">

                        <h5 class="d-inline-block m-0">{{ trans_choice('misc.images_plural', 0) }} ({{ $data->total() }})
                        </h5>

                        @if ($data->count() != 0)
                            <select class="form-select d-inline-block w-auto filter float-end">
                                <option @if ($sort == '') selected="selected" @endif
                                    value="{{ url()->current() }}">{{ __('admin.sort_id') }}</option>
                                <option @if ($sort == 'pending') selected="selected" @endif
                                    value="{{ url()->current() }}?sort=pending">{{ __('admin.pending') }}</option>
                                <option @if ($sort == 'title') selected="selected" @endif
                                    value="{{ url()->current() }}?sort=title">{{ __('admin.sort_title') }}</option>
                                <option @if ($sort == 'likes') selected="selected" @endif
                                    value="{{ url()->current() }}?sort=likes">{{ __('admin.sort_likes') }}</option>
                                <option @if ($sort == 'downloads') selected="selected" @endif
                                    value="{{ url()->current() }}?sort=downloads">{{ __('admin.sort_downloads') }}
                                </option>
                            </select>
                        @endif
                    </div>

                    @if ($data->count() != 0)
                        <div class="card shadow-sm">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <th class="active">ID</th>
                                        <th class="active">{{ __('misc.thumbnail') }}</th>
                                        <th class="active">{{ __('admin.title') }}</th>
                                        <th class="active">{{ __('admin.type') }}</th>
                                        <th class="active">{{ __('misc.likes') }}</th>
                                        <th class="active">{{ __('misc.downloads') }}</th>
                                        <th class="active">{{ __('admin.date') }}</th>
                                        <th class="active">{{ __('admin.status') }}</th>
                                        <th class="active">{{ __('admin.actions') }}</th>
                                    </thead>

                                    <tbody>
                                        @foreach ($data as $image)
                                            <tr>
                                                <td>{{ $image->id }}</td>
                                                <td><img src="{{ Storage::url(config('path.thumbnail') . $image->thumbnail) }}"
                                                        width="50" /></td>
                                                <td><a href="{{ url('photo', $image->id) }}" title="{{ $image->title }}"
                                                        target="_blank">{{ str_limit($image->title, 10, '...') }} <i
                                                            class="fa fa-external-link-square"></i></a></td>
                                                <td>{{ $image->item_for_sale == 'sale' ? __('misc.sale') : __('misc.free') }}
                                                </td>
                                                <td>{{ $image->likes_count }}</td>
                                                <td>{{ $image->downloads_count }}</td>
                                                <td>{{ App\Helper::formatDate($image->date) }}</td>

                                                @php
                                                    
                                                    if ($image->status == 'pending') {
                                                        $mode = 'warning';
                                                        $_status = __('admin.pending');
                                                    } elseif ($image->status == 'active') {
                                                        $mode = 'success';
                                                        $_status = __('admin.active');
                                                    }
                                                    
                                                @endphp
                                                <td><span class="badge bg-{{ $mode }}">{{ $_status }}</span>
                                                </td>
                                                <td>

                                                    <a href="{{ url('edit/photo', $image->id) }}"
                                                        class="btn btn-light border btn-sm padding-btn" target="_blank">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>

                                                </td>
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

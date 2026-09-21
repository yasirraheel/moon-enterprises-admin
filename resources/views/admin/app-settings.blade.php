@extends('admin.layout')

@section('content')
<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
    <i class="bi-chevron-right me-1 fs-6"></i>
    <span class="text-muted">App Settings</span>
</h5>

<div class="content">
    <div class="row">
        <div class="col-lg-12">

            @if (session('success_message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check2 me-1"></i> {{ session('success_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            @endif

            @include('errors.errors-forms')

            <div class="card shadow-custom border-0">
                <div class="card-body p-lg-5">

                    <ul class="nav nav-tabs mb-4" id="appSettingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">General Settings</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="news-tab" data-bs-toggle="tab" data-bs-target="#news" type="button" role="tab" aria-controls="news" aria-selected="false">News Ticker</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="iptv-tab" data-bs-toggle="tab" data-bs-target="#iptv" type="button" role="tab" aria-controls="iptv" aria-selected="false">IP TV Links</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="appSettingsTabContent">

                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <form method="POST" action="{{ route('admin.app_settings.update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label text-lg-end">App Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" value="{{ $settings->results_app_name }}" name="results_app_name" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label text-lg-end">App Logo</label>
                                    <div class="col-sm-10">
                                        <div class="d-block mb-2">
                                            @if($settings->results_app_logo)
                                                <img src="{{ asset('public/img/'.$settings->results_app_logo) }}" style="max-height: 100px;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" style="width:150px; height:100px; border: 2px dashed #ccc;">
                                                    <span class="text-muted">No Image</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="input-group">
                                            <input type="file" name="results_app_logo" class="form-control custom-file">
                                        </div>
                                        <small class="d-block text-muted mt-1">Recommended size: 400x200 px (PNG, JPG)</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label text-lg-end">Contact No</label>
                                    <div class="col-sm-10">
                                        <input type="text" value="{{ $settings->results_contact_no }}" name="results_contact_no" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label text-lg-end">About Us</label>
                                    <div class="col-sm-10">
                                        <textarea name="results_about_us" class="form-control" rows="5">{{ $settings->results_about_us }}</textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label text-lg-end">Lottie Animation</label>
                                    <div class="col-sm-10">
                                        <div class="d-block mb-2">
                                            @if($settings->lottie_animation_url)
                                                <div class="alert alert-info py-2 px-3 d-inline-block">
                                                    <i class="bi-filetype-json me-1"></i> Current File: <a href="{{ asset('public/lottie/'.$settings->lottie_animation_url) }}" target="_blank">{{ $settings->lottie_animation_url }}</a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="input-group">
                                            <input type="file" name="lottie_animation_url" class="form-control custom-file" accept=".json">
                                        </div>
                                        <small class="d-block text-muted mt-1">Upload a .json Lottie animation file</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label text-lg-end">Countdown Video</label>
                                    <div class="col-sm-10">
                                        <div class="d-block mb-2">
                                            @if($settings->countdown_video_url)
                                                <div class="mb-2">
                                                    <video width="320" height="180" controls class="border rounded">
                                                        <source src="{{ asset('public/video/'.$settings->countdown_video_url) }}" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                </div>
                                                <div class="alert alert-info py-2 px-3 d-inline-block">
                                                    <i class="bi-file-play me-1"></i> Current File: <a href="{{ asset('public/video/'.$settings->countdown_video_url) }}" target="_blank">{{ $settings->countdown_video_url }}</a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="input-group">
                                            <input type="file" name="countdown_video_url" class="form-control custom-file" accept="video/mp4,video/webm">
                                        </div>
                                        <small class="d-block text-muted mt-1">Upload a .mp4 or .webm video file</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" class="btn btn-dark mt-3 px-5">{{ __('admin.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- News Ticker -->
                        <div class="tab-pane fade {{ session('active_tab') == 'news' ? 'show active' : '' }}" id="news" role="tabpanel" aria-labelledby="news-tab">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                                    <i class="bi-plus-lg me-1"></i> Add News
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Order</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($newsTickers as $news)
                                        <tr>
                                            <td>{{ Str::limit($news->message, 50) }}</td>
                                            <td>
                                                @if($news->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $news->sort_order }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editNewsModal{{ $news->id }}">
                                                    <i class="bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.news_ticker.delete', $news->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit News Modal -->
                                        <div class="modal fade" id="editNewsModal{{ $news->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.news_ticker.update') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $news->id }}">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit News</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Message</label>
                                                                <textarea name="message" class="form-control" rows="3" required>{{ $news->message }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Sort Order</label>
                                                                <input type="number" name="sort_order" class="form-control" value="{{ $news->sort_order }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Status</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="active" {{ $news->status == 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="inactive" {{ $news->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- IP TV Links -->
                        <div class="tab-pane fade {{ session('active_tab') == 'iptv' ? 'show active' : '' }}" id="iptv" role="tabpanel" aria-labelledby="iptv-tab">
                            
                            @if(isset($parsedChannels) && count($parsedChannels) > 0)
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="bi-check2-circle me-1"></i> Review Imported Channels ({{ count($parsedChannels) }})</h6>
                                    <span class="badge bg-light text-primary">Please select channels to save</span>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.iptv.store_bulk') }}" method="POST">
                                        @csrf
                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 30px;"><input type="checkbox" id="selectAllChannels" checked></th>
                                                        <th>Icon</th>
                                                        <th>Title</th>
                                                        <th>URL</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($parsedChannels as $index => $channel)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="channels[{{ $index }}][save]" value="1" class="channel-checkbox" checked>
                                                            <input type="hidden" name="channels[{{ $index }}][title]" value="{{ $channel['title'] }}">
                                                            <input type="hidden" name="channels[{{ $index }}][url]" value="{{ $channel['url'] }}">
                                                            <input type="hidden" name="channels[{{ $index }}][icon]" value="{{ $channel['icon'] }}">
                                                        </td>
                                                        <td>
                                                            @if(!empty($channel['icon']))
                                                            <img src="{{ $channel['icon'] }}" style="width: 30px; height: 30px; object-fit: contain;">
                                                            @endif
                                                        </td>
                                                        <td>{{ $channel['title'] }}</td>
                                                        <td class="text-truncate" style="max-width: 200px;" title="{{ $channel['url'] }}">{{ $channel['url'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3 text-end">
                                            <a href="{{ route('admin.app_settings') }}" class="btn btn-secondary me-2">Cancel</a>
                                            <button type="submit" class="btn btn-success">Save Selected Channels</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <script>
                                document.getElementById('selectAllChannels').addEventListener('change', function() {
                                    var checkboxes = document.getElementsByClassName('channel-checkbox');
                                    for (var i = 0; i < checkboxes.length; i++) {
                                        checkboxes[i].checked = this.checked;
                                    }
                                });
                            </script>
                            @endif

                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-info me-2 text-white" data-bs-toggle="modal" data-bs-target="#importM3uModal">
                                    <i class="bi-upload me-1"></i> Import M3U
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpTvModal">
                                    <i class="bi-plus-lg me-1"></i> Add Link
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>URL</th>
                                            <th>Status</th>
                                            <th>Order</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ipTvLinks as $link)
                                        <tr>
                                            <td>{{ $link->title }}</td>
                                            <td><a href="{{ $link->url }}" target="_blank">{{ Str::limit($link->url, 30) }}</a></td>
                                            <td>
                                                @if($link->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $link->sort_order }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editIpTvModal{{ $link->id }}">
                                                    <i class="bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.iptv.delete', $link->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                         <!-- Edit IP TV Modal -->
                                        <div class="modal fade" id="editIpTvModal{{ $link->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.iptv.update') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $link->id }}">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit IP TV Link</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Title</label>
                                                                <input type="text" name="title" class="form-control" value="{{ $link->title }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">URL</label>
                                                                <input type="url" name="url" class="form-control" value="{{ $link->url }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Icon (Optional URL)</label>
                                                                <input type="text" name="icon" class="form-control" value="{{ $link->icon }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Sort Order</label>
                                                                <input type="number" name="sort_order" class="form-control" value="{{ $link->sort_order }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Status</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="active" {{ $link->status == 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="inactive" {{ $link->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.news_ticker.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add News</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add News</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add IP TV Modal -->
<div class="modal fade" id="addIpTvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.iptv.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add IP TV Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="url" name="url" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Optional URL)</label>
                        <input type="text" name="icon" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import M3U Modal -->
<div class="modal fade" id="importM3uModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.iptv.parse_m3u') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import M3U Playlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Provide a URL or upload an M3U file to import channels.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">M3U Playlist URL</label>
                        <input type="url" name="m3u_url" class="form-control" placeholder="http://example.com/playlist.m3u">
                    </div>

                    <div class="text-center my-2 text-muted">- OR -</div>

                    <div class="mb-3">
                        <label class="form-label">Upload M3U File</label>
                        <input type="file" name="m3u_file" class="form-control" accept=".m3u,.m3u8,.txt">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Parse Playlist</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

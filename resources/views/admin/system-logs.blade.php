@extends('admin.layout')

@section('content')
    <h5 class="mb-4 fw-light">
        <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
        <i class="bi-chevron-right me-2 fs-6"></i>
        <span class="text-muted">System Logs</span>
    </h5>

    <div class="content">
        <div class="row">
            <div class="col-lg-12">

                @if (session('success_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check2 me-1"></i> {{ session('success_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error_message'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card shadow-custom border-0">
                    <div class="card-body p-lg-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-0">System Logs (Last 50 Lines)</h5>
                                <small class="text-muted">Showing the most recent log entries first</small>
                            </div>
                            
                            <form action="{{ route('system_logs.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear the system logs? This action cannot be undone.');">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash me-1"></i> Clear Logs
                                </button>
                            </form>
                        </div>

                        <div class="bg-dark text-white p-3 rounded overflow-y-auto overflow-x-hidden" style="max-height: 600px; font-family: monospace; font-size: 0.85rem;">
                            @if(count($logs) > 0)
                                @foreach($logs as $log)
                                    <div class="mb-1 border-bottom border-secondary pb-1 text-break" style="white-space: pre-wrap; word-break: break-word;">{{ $log }}</div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-x fs-1"></i>
                                    <p class="mt-2">No logs found.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

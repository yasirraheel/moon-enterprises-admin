@extends('layouts.app')

@section('title', $notification->title)

@section('content')
<div class="container-custom container pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                @if($notification->image)
                <img src="{{ $notification->image_url }}" class="card-img-top" 
                    alt="{{ $notification->title }}" style="max-height: 400px; object-fit: cover;">
                @endif
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 class="card-title mb-0">{{ $notification->title }}</h2>
                        <small class="text-muted">
                            {{ $notification->created_at->format('M d, Y H:i') }}
                        </small>
                    </div>
                    
                    <div class="card-text">
                        {!! nl2br(e($notification->message)) !!}
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> {{ __('misc.back_to_notifications') }}
                        </a>
                        <small class="text-muted">
                            {{ __('misc.published_on') }} {{ $notification->created_at->format('F d, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

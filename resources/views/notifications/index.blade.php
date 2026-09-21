@extends('layouts.app')

@section('title', __('misc.notifications'))

@section('content')
<div class="container-custom container pt-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">{{ __('misc.notifications') }}</h4>
            </div>

            @if($notifications->count() > 0)
                <div class="row">
                    @foreach($notifications as $notification)
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card shadow-sm h-100">
                            @if($notification->image)
                            <img src="{{ $notification->image_url }}" class="card-img-top" 
                                alt="{{ $notification->title }}" style="height: 200px; object-fit: cover;">
                            @endif
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $notification->title }}</h5>
                                <p class="card-text text-muted flex-grow-1">
                                    {{ Str::limit($notification->message, 100) }}
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $notification->created_at->format('M d, Y') }}
                                    </small>
                                    <a href="{{ route('notifications.show', $notification) }}" 
                                        class="btn btn-outline-primary btn-sm">
                                        {{ __('misc.read_more') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($notifications->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
                @endif

            @else
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">{{ __('misc.no_notifications_available') }}</h5>
                    <p class="text-muted">{{ __('misc.check_back_later') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

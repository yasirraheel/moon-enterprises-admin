@extends('admin.layout')

@section('css')
<style>
    /* Dark theme adjustments */
    .card {
        background-color: #2c3b41;
        color: #fff;
    }
    .card-header {
        background-color: #222d32;
        color: #fff;
        border-bottom: 1px solid #444;
    }
    .form-control {
        background-color: #222d32;
        border: 1px solid #444;
        color: #fff;
    }
    .form-control:focus {
        background-color: #2c3b41;
        color: #fff;
        border-color: #3c8dbc;
    }
    .input-group-text {
        background-color: #222d32;
        border: 1px solid #444;
        color: #fff;
    }
</style>
@endsection

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">Results API</span>
  </h5>

<div class="content">
	<div class="row">
		<div class="col-lg-12">

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check2 me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            @endif

            <!-- API Key Section -->
            <div class="card shadow-custom border-0 mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">API Key Configuration</h6>
                    <form action="{{ route('admin.results.generate_key') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="bi-key me-1"></i> {{ $settings->results_api_key ? 'Regenerate API Key' : 'Generate API Key' }}
                        </button>
                    </form>
                </div>
                <div class="card-body p-lg-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase fw-bold">Your API Key</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $settings->results_api_key ?? 'Not generated yet' }}" readonly id="apiKeyInput">
                            <button class="btn btn-outline-light" type="button" onclick="copyToClipboard('apiKeyInput')">
                                <i class="bi-clipboard"></i> Copy
                            </button>
                        </div>
                        <div class="mt-2 text-warning small">
                            <i class="bi-exclamation-triangle me-1"></i>
                            Keep this key secure. It provides read access to all results data.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Postman Guide Section -->
            <div class="card shadow-custom border-0 mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0">Postman / cURL Quick Guide</h6>
                </div>
                <div class="card-body p-lg-4">
                    <p class="text-muted small">Copy and paste these commands directly into your terminal or import into Postman to test the API.</p>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-white small">cURL: Get All Results</label>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded border border-secondary mb-0"><code id="curlAll">curl --location '{{ url('api/results') }}' \
--header 'X-Results-API-Key: {{ $settings->results_api_key ?? 'YOUR_API_KEY' }}'</code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard('curlAll')">
                                <i class="bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-white small">cURL: Get Latest Result</label>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded border border-secondary mb-0"><code id="curlLatest">curl --location '{{ url('api/results/latest') }}' \
--header 'X-Results-API-Key: {{ $settings->results_api_key ?? 'YOUR_API_KEY' }}'</code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard('curlLatest')">
                                <i class="bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-white small">cURL: Get Results by Date</label>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded border border-secondary mb-0"><code id="curlDate">curl --location '{{ url('api/results/by-date') }}' \
--header 'X-Results-API-Key: {{ $settings->results_api_key ?? 'YOUR_API_KEY' }}' \
--header 'Content-Type: application/json' \
--data '{
    "date": "{{ date('Y-m-d') }}"
}'</code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard('curlDate')">
                                <i class="bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-white small">cURL: Get App Settings (App Name, Logo, etc.)</label>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded border border-secondary mb-0"><code id="curlAppSettings">curl --location '{{ url('api/results/app-settings') }}' \
--header 'X-Results-API-Key: {{ $settings->results_api_key ?? 'YOUR_API_KEY' }}'</code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard('curlAppSettings')">
                                <i class="bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-white small">cURL: Get News Ticker</label>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded border border-secondary mb-0"><code id="curlNews">curl --location '{{ url('api/results/news-ticker') }}' \
--header 'X-Results-API-Key: {{ $settings->results_api_key ?? 'YOUR_API_KEY' }}'</code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard('curlNews')">
                                <i class="bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-white small">cURL: Get IP TV Links</label>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded border border-secondary mb-0"><code id="curlIpTv">curl --location '{{ url('api/results/iptv-links') }}' \
--header 'X-Results-API-Key: {{ $settings->results_api_key ?? 'YOUR_API_KEY' }}'</code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard('curlIpTv')">
                                <i class="bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- API Documentation -->
            <div class="card shadow-custom border-0">
                <div class="card-header py-3">
                    <h6 class="mb-0">API Documentation & Response Examples</h6>
                </div>
                <div class="card-body p-lg-4">

                    <div class="mb-5">
                        <h6 class="text-primary mb-3">1. Get All Results</h6>
                        <p class="text-muted small mb-2">Endpoint: <code class="text-warning">GET {{ url('api/results') }}</code></p>
                        <p class="text-muted small">Returns a paginated list of all published results.</p>

                        <div class="bg-dark p-3 rounded border border-secondary">
                            <pre class="text-light mb-0 small"><code>{
    "success": true,
    "data": [
        {
            "id": 1,
            "result_date": "2023-10-27",
            "result_time": "14:30:00",
            "first": "123456",
            "second": "789012",
            // ...
        }
    ],
    "links": { ... },
    "meta": { ... }
}</code></pre>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="text-primary mb-3">2. Get Latest Result</h6>
                        <p class="text-muted small mb-2">Endpoint: <code class="text-warning">GET {{ url('api/results/latest') }}</code></p>
                        <p class="text-muted small">Returns the single most recent published result.</p>

                        <div class="bg-dark p-3 rounded border border-secondary">
                            <pre class="text-light mb-0 small"><code>{
    "success": true,
    "data": {
        "id": 1,
        "result_date": "2023-10-27",
        "result_time": "14:30:00",
        "first": "123456",
        "second": "789012",
        // ...
    }
}</code></pre>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="text-primary mb-3">3. Get Results by Date</h6>
                        <p class="text-muted small mb-2">Endpoint: <code class="text-warning">POST {{ url('api/results/by-date') }}</code></p>
                        <p class="text-muted small">Returns results for a specific date. Requires <code>date</code> parameter (YYYY-MM-DD).</p>

                        <div class="bg-dark p-3 rounded border border-secondary">
                            <pre class="text-light mb-0 small"><code>{
    "success": true,
    "count": 2,
    "data": [
        { ... },
        { ... }
    ]
}</code></pre>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="text-primary mb-3">4. Get App Settings</h6>
                        <p class="text-muted small mb-2">Endpoint: <code class="text-warning">GET {{ url('api/results/app-settings') }}</code></p>
                        <p class="text-muted small">Returns general app settings including app name, logo URL, about us text, contact number, lottie animation URL, and countdown video URL.</p>
                        
                        <div class="bg-dark p-3 rounded border border-secondary">
                            <pre class="text-light mb-0 small"><code>{
    "success": true,
    "data": {
        "app_name": "My Results App",
        "app_logo": "http://domain.com/public/img/app_logo-123456.png",
        "about_us": "This is a description...",
        "contact_no": "+923001234567",
        "lottie_url": "http://domain.com/public/lottie/animation-123456.json",
        "countdown_video": "http://domain.com/public/video/countdown-123456.mp4"
    }
}</code></pre>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="text-primary mb-3">5. Get News Ticker</h6>
                        <p class="text-muted small mb-2">Endpoint: <code class="text-warning">GET {{ url('api/results/news-ticker') }}</code></p>
                        <p class="text-muted small">Returns a list of active news items ordered by sort order.</p>

                        <div class="bg-dark p-3 rounded border border-secondary">
                            <pre class="text-light mb-0 small"><code>{
    "success": true,
    "data": [
        {
            "id": 1,
            "message": "Welcome to our new app!",
            "status": "active",
            "sort_order": 0,
            "created_at": "..."
        },
        // ...
    ]
}</code></pre>
                        </div>
                    </div>

                    <div class="mb-0">
                        <h6 class="text-primary mb-3">6. Get IP TV Links</h6>
                        <p class="text-muted small mb-2">Endpoint: <code class="text-warning">GET {{ url('api/results/iptv-links') }}</code></p>
                        <p class="text-muted small">Returns a list of active IP TV channel links.</p>

                        <div class="bg-dark p-3 rounded border border-secondary">
                            <pre class="text-light mb-0 small"><code>{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Sports Channel",
            "url": "http://stream-url.com/live",
            "icon": "http://icon-url.com/icon.png",
            "status": "active",
            "sort_order": 0
        }
    ]
}</code></pre>
                        </div>
                    </div>

                </div>
            </div>

		</div>
	</div>
</div>

<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); /* For mobile devices */
        document.execCommand("copy");
        // Optional: Change button text temporarily
        var btn = event.currentTarget;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi-check"></i> Copied';
        setTimeout(function() {
            btn.innerHTML = originalHtml;
        }, 2000);
    }
</script>
@endsection

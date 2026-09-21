<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download APK - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .download-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .download-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .download-body {
            padding: 2rem;
        }
        .version-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }
        .download-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease;
            width: 100%;
            margin: 1rem 0;
        }
        .download-btn:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .features {
            margin: 1.5rem 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin: 0.5rem 0;
            color: #666;
        }
        .feature-item i {
            color: #667eea;
            margin-right: 0.5rem;
            width: 20px;
        }
        .release-notes {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .release-notes h6 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .force-update {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
        }
        .force-update i {
            color: #f39c12;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="download-card">
        <div class="download-header">
            <i class="fas fa-mobile-alt fa-3x mb-3"></i>
            <h2>{{ config('app.name') }}</h2>
            <p class="mb-0">Download the latest version</p>
        </div>
        
        <div class="download-body">
            @if($activeVersion)
                <div class="text-center">
                    <div class="version-badge">
                        Version {{ $activeVersion->version_name }}
                    </div>
                    
                    @if($activeVersion->is_force_update)
                        <div class="force-update">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h6>Important Update Available</h6>
                            <p class="mb-0">This update is required to continue using the app.</p>
                        </div>
                    @endif
                    
                    <a href="{{ $activeVersion->download_link }}" class="download-btn" target="_blank">
                        <i class="fas fa-download me-2"></i>
                        Download APK
                    </a>
                    
                    <div class="features">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure & Safe</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-rocket"></i>
                            <span>Latest Features</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bug"></i>
                            <span>Bug Fixes</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-heart"></i>
                            <span>Improved Performance</span>
                        </div>
                    </div>
                    
                    @if($activeVersion->release_notes)
                        <div class="release-notes">
                            <h6><i class="fas fa-sticky-note me-2"></i>What's New</h6>
                            <p class="mb-0">{{ $activeVersion->release_notes }}</p>
                        </div>
                    @endif
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-download me-1"></i>
                            {{ $activeVersion->download_count }} downloads
                        </small>
                    </div>
                </div>
            @else
                <div class="text-center">
                    <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                    <h4>No APK Available</h4>
                    <p class="text-muted">There are currently no APK versions available for download.</p>
                    <p class="text-muted">Please check back later or contact support.</p>
                </div>
            @endif
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Make sure to enable "Install from Unknown Sources" in your device settings
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
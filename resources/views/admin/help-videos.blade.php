@extends('admin.layout')

@section('title', 'Help Videos Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Help Videos Management</h3>
                </div>
                <div class="card-body">
                    @if (session('success_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check2 me-1"></i> {{ session('success_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if (session('error_message'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-x-circle me-1"></i> {{ session('error_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @include('errors.errors-forms')

                    <!-- Add/Edit Help Video Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0" id="formTitle">Add Help Video</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.help.videos.store') }}" method="POST" enctype="multipart/form-data" id="helpVideoForm">
                                        @csrf
                                        <input type="hidden" id="video_id" name="video_id" value="">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="title">Video Title *</label>
                                                    <input type="text" class="form-control" id="title" name="title" 
                                                           placeholder="e.g., How to use the app" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="video_type">Video Type *</label>
                                                    <select class="form-select" id="video_type" name="video_type" required onchange="toggleVideoInput()">
                                                        <option value="youtube">YouTube Video</option>
                                                        <option value="local">Local Upload</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- YouTube URL Input -->
                                        <div class="row" id="youtube_section">
                                            <div class="col-12">
                                                <div class="form-group mb-3">
                                                    <label for="youtube_url">YouTube URL</label>
                                                    <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                                           placeholder="https://www.youtube.com/watch?v=...">
                                                    <small class="text-muted">Paste the full YouTube video URL</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Local Video Upload -->
                                        <div class="row" id="local_section" style="display: none;">
                                            <div class="col-12">
                                                <div class="form-group mb-3">
                                                    <label for="local_video" class="form-label">Upload Video File</label>
                                                    
                                                    <!-- Custom File Upload Area -->
                                                    <div class="custom-file-upload-wrapper">
                                                        <input type="file" class="d-none" id="local_video" name="local_video" 
                                                               accept="video/mp4,video/mpeg,video/quicktime" onchange="handleFileSelect(this)">
                                                        
                                                        <div class="custom-file-upload-area" id="uploadArea" onclick="document.getElementById('local_video').click()">
                                                            <div class="upload-icon">
                                                                <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                                                            </div>
                                                            <div class="upload-text mt-3">
                                                                <h5 class="mb-2">Click to upload video</h5>
                                                                <p class="text-muted mb-0">or drag and drop</p>
                                                            </div>
                                                            <div class="upload-info mt-2">
                                                                <small class="text-muted">MP4, MPEG, MOV (Max 100MB)</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Selected File Preview -->
                                                        <div class="selected-file-preview" id="filePreview" style="display: none;">
                                                            <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-video fa-2x text-primary me-3"></i>
                                                                    <div>
                                                                        <h6 class="mb-0" id="fileName">video.mp4</h6>
                                                                        <small class="text-muted" id="fileSize">0 MB</small>
                                                                    </div>
                                                                </div>
                                                                <button type="button" class="btn btn-sm btn-danger" onclick="clearFileSelection()">
                                                                    <i class="fas fa-times"></i> Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="current_video_info" style="display: none;" class="mt-3">
                                                        <div class="alert alert-info d-flex align-items-center">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            <span>Current video: <strong id="current_video_name"></strong></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                                    <label class="form-check-label" for="is_active">
                                                        Active (Visible to users)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> <span id="submitBtnText">Save Video</span>
                                                </button>
                                                <button type="button" class="btn btn-secondary" id="cancelEditBtn" onclick="cancelEdit()" style="display: none;">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Videos List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Video Link/File</th>
                                            <th>Status</th>
                                            <th>Views</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($videos as $video)
                                        <tr>
                                            <td><strong>{{ $video->title }}</strong></td>
                                            <td>
                                                @if($video->video_type === 'youtube')
                                                    <span class="badge bg-danger"><i class="fab fa-youtube"></i> YouTube</span>
                                                @else
                                                    <span class="badge bg-primary"><i class="fas fa-file-video"></i> Local</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($video->video_type === 'youtube')
                                                    <a href="{{ $video->youtube_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-external-link-alt"></i> View on YouTube
                                                    </a>
                                                @else
                                                    <small class="text-muted">{{ $video->local_video_path }}</small>
                                                    @if($video->local_video_path)
                                                        <a href="{{ url('public/help-videos/' . $video->local_video_path) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                            <i class="fas fa-play"></i> Play
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($video->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $video->view_count }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick='editVideo(@json($video))'>
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form action="{{ route('admin.help.videos.toggle', $video->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $video->is_active ? 'btn-warning' : 'btn-success' }}">
                                                        {{ $video->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.help.videos.delete', $video->id) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this help video?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No help videos found</td>
                                        </tr>
                                        @endforelse
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

<script>
function toggleVideoInput() {
    const videoType = document.getElementById('video_type').value;
    const youtubeSection = document.getElementById('youtube_section');
    const localSection = document.getElementById('local_section');
    const youtubeUrl = document.getElementById('youtube_url');
    const localVideo = document.getElementById('local_video');
    
    if (videoType === 'youtube') {
        youtubeSection.style.display = 'block';
        localSection.style.display = 'none';
        youtubeUrl.required = true;
        localVideo.required = false;
    } else {
        youtubeSection.style.display = 'none';
        localSection.style.display = 'block';
        youtubeUrl.required = false;
        // Only require local video if we're not editing
        localVideo.required = !document.getElementById('video_id').value;
    }
}

function editVideo(video) {
    // Populate form
    document.getElementById('video_id').value = video.id;
    document.getElementById('title').value = video.title;
    document.getElementById('video_type').value = video.video_type;
    document.getElementById('is_active').checked = video.is_active;
    
    if (video.video_type === 'youtube') {
        document.getElementById('youtube_url').value = video.youtube_url || '';
    } else {
        if (video.local_video_path) {
            document.getElementById('current_video_info').style.display = 'block';
            document.getElementById('current_video_name').textContent = video.local_video_path;
            document.getElementById('local_video').required = false;
        }
    }
    
    toggleVideoInput();
    
    // Update UI
    document.getElementById('formTitle').textContent = 'Edit Help Video';
    document.getElementById('submitBtnText').textContent = 'Update Video';
    document.getElementById('cancelEditBtn').style.display = 'inline-block';
    
    // Scroll to form
    document.getElementById('helpVideoForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelEdit() {
    // Reset form
    document.getElementById('helpVideoForm').reset();
    document.getElementById('video_id').value = '';
    document.getElementById('current_video_info').style.display = 'none';
    document.getElementById('local_video').required = false;
    
    // Update UI
    document.getElementById('formTitle').textContent = 'Add Help Video';
    document.getElementById('submitBtnText').textContent = 'Save Video';
    document.getElementById('cancelEditBtn').style.display = 'none';
    
    toggleVideoInput();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleVideoInput();
});

// Handle file selection
function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        // Show preview
        document.getElementById('uploadArea').style.display = 'none';
        document.getElementById('filePreview').style.display = 'block';
        
        // Update file info
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
    }
}

// Clear file selection
function clearFileSelection() {
    document.getElementById('local_video').value = '';
    document.getElementById('uploadArea').style.display = 'block';
    document.getElementById('filePreview').style.display = 'none';
}

// Drag and drop functionality
const uploadArea = document.getElementById('uploadArea');

if (uploadArea) {
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById('local_video');
            input.files = files;
            handleFileSelect(input);
        }
    });
}
</script>

<style>
.custom-file-upload-wrapper {
    margin-top: 0.5rem;
}

.custom-file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.custom-file-upload-area:hover {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

.custom-file-upload-area.drag-over {
    border-color: #0d6efd;
    background-color: #e7f1ff;
    transform: scale(1.02);
}

.custom-file-upload-area .upload-icon {
    margin-bottom: 10px;
}

.custom-file-upload-area .upload-text h5 {
    color: #495057;
    font-weight: 600;
}

.custom-file-upload-area .upload-text p {
    color: #6c757d;
}

.selected-file-preview {
    margin-top: 15px;
}

.selected-file-preview .fa-video {
    color: #0d6efd;
}
</style>

@endsection

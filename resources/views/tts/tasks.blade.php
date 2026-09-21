@extends('layouts.app')

@section('css')
<link href="{{ asset('public/css/tts-custom.css') }}?v={{$settings->version}}" rel="stylesheet">
@endsection

@section('content')
<!-- Hero Section -->
<div class="container-fluid home-cover">
    <div class="mb-4 position-relative custom-pt-6">
        <div class="container px-3 px-md-5">
            <div class="text-center text-md-start">
                <h1 class="display-4 display-md-3 fw-bold text-white mb-3">
                    <i class="bi bi-list-task me-3"></i>Your TTS Tasks
                </h1>
                <p class="col-md-8 fs-5 fw-bold text-white mb-4">
                    Manage and monitor your text-to-speech generation tasks
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="task-history-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <h3 class="mb-0"><i class="bi bi-list-task me-2"></i>Task History</h3>
                    <a href="{{ url('/') }}" class="btn btn-custom">
                        <i class="bi bi-plus-circle me-2"></i>Create New Task
                    </a>
                </div>

                <!-- Task List -->
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white" style="background-color: {{ $settings->color_default }};">
                        <h4 class="mb-0"><i class="bi bi-list-task me-2"></i>Task Management</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Desktop Table View -->
                        <div class="d-none d-lg-block">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tasksTable">
                                    <thead>
                                        <tr>
                                            <th>Task ID</th>
                                            <th>Input Text</th>
                                            <th>Voice ID</th>
                                            <th>Model</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Tasks will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-lg-none" id="mobileTasksContainer">
                            <!-- Mobile task cards will be loaded here -->
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Task pagination" class="mt-4">
                            <ul class="pagination justify-content-center" id="pagination">
                                <!-- Pagination will be loaded here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-12">
                <h2 class="mb-5">Task Management Features</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-eye display-4 mb-3" style="color: {{ $settings->color_default }};"></i>
                        <h5 class="card-title">View Details</h5>
                        <p class="card-text">Click on any task to view detailed information including voice settings and generated audio.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-download display-4 mb-3" style="color: {{ $settings->color_default }};"></i>
                        <h5 class="card-title">Download Audio</h5>
                        <p class="card-text">Download completed speech files directly from the task list or detailed view.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-trash display-4 mb-3" style="color: {{ $settings->color_default }};"></i>
                        <h5 class="card-title">Manage Tasks</h5>
                        <p class="card-text">Delete completed or failed tasks to keep your workspace organized and clean.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-list-task me-2"></i>Task Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="taskDetails">
                <!-- Task details will be loaded here -->
            </div>
            <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
                <button type="button" class="btn btn-outline-custom w-100 w-sm-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Close
                </button>
                <button type="button" class="btn btn-danger w-100 w-sm-auto" id="deleteTaskBtn" style="display: none;">
                    <i class="bi bi-trash me-2"></i>Delete Task
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentTaskId = null;

    // Load tasks on page load
    loadTasks(1);

    // Load tasks function
    function loadTasks(page) {
        $.ajax({
            url: '{{ url("api/tts/tasks") }}',
            method: 'GET',
            data: { page: page, limit: 10 },
            headers: {
                'Authorization': 'Bearer {{ Helper::getSevenLabsApiKey() }}',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayTasks(response.data.tasks || []);
                    displayPagination(response.data);
                } else {
                    $('#tasksTable tbody').html('<tr><td colspan="7" class="text-center">No tasks found</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('Error loading tasks:', xhr);
                $('#tasksTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Error loading tasks</td></tr>');
            }
        });
    }

    // Display tasks in table
    function displayTasks(tasks) {
        // Desktop table view
        const tbody = $('#tasksTable tbody');
        tbody.empty();

        // Mobile card view
        const mobileContainer = $('#mobileTasksContainer');
        mobileContainer.empty();

        if (tasks.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center">No tasks found</td></tr>');
            mobileContainer.html('<div class="text-center text-muted py-4"><i class="bi bi-inbox display-4"></i><p class="mt-2">No tasks found</p></div>');
            return;
        }

        tasks.forEach(function(task) {
            const statusBadge = getStatusBadge(task.status);
            const createdDate = new Date(task.created_at).toLocaleString();
            const shortInput = task.input.length > 50 ? task.input.substring(0, 50) + '...' : task.input;

            // Desktop table row
            const row = `
                <tr>
                    <td><code>${task.id.substring(0, 8)}...</code></td>
                    <td title="${task.input}">${shortInput}</td>
                    <td><code>${task.voice_id}</code></td>
                    <td>${task.model_id}</td>
                    <td>${statusBadge}</td>
                    <td>${createdDate}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-custom" onclick="viewTask('${task.id}')" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            ${task.status === 'completed' && task.result ?
                                `<button class="btn btn-sm btn-outline-info" onclick="playAudio('${task.result}')" title="Play Audio">
                                    <i class="bi bi-play-circle"></i>
                                </button>
                                <a href="${task.result}" class="btn btn-sm btn-outline-success" download title="Download Audio">
                                    <i class="bi bi-download"></i>
                                </a>` : ''
                            }
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTask('${task.id}')" title="Delete Task">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);

            // Mobile card
            const card = `
                <div class="card mb-3 border-0 shadow-sm mobile-task-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">
                                <code class="text-muted">${task.id.substring(0, 12)}...</code>
                            </h6>
                            ${statusBadge}
                        </div>
                        <p class="card-text text-muted small mb-2">
                            <i class="bi bi-calendar me-1"></i>${createdDate}
                        </p>
                        <p class="card-text mb-3" title="${task.input}">
                            ${shortInput}
                        </p>
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted d-block">Voice</small>
                                <code class="small">${task.voice_id.substring(0, 8)}...</code>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Model</small>
                                <span class="small">${task.model_id}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Actions</small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-custom btn-sm" onclick="viewTask('${task.id}')" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    ${task.status === 'completed' && task.result ?
                                        `<button class="btn btn-outline-info btn-sm" onclick="playAudio('${task.result}')" title="Play Audio">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                        <a href="${task.result}" class="btn btn-outline-success btn-sm" download title="Download Audio">
                                            <i class="bi bi-download"></i>
                                        </a>` : ''
                                    }
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteTask('${task.id}')" title="Delete Task">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            mobileContainer.append(card);
        });
    }

    // Get status badge HTML
    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Pending</span>',
            'processing': '<span class="badge bg-info">Processing</span>',
            'completed': '<span class="badge bg-success">Completed</span>',
            'failed': '<span class="badge bg-danger">Failed</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
    }

    // Display pagination
    function displayPagination(data) {
        const pagination = $('#pagination');
        pagination.empty();

        if (!data.total || data.total <= data.limit) {
            return;
        }

        const totalPages = Math.ceil(data.total / data.limit);
        const currentPage = parseInt(data.page);

        // Previous button
        if (currentPage > 1) {
            pagination.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadTasks(${currentPage - 1})">Previous</a></li>`);
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            pagination.append(`<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadTasks(${i})">${i}</a></li>`);
        }

        // Next button
        if (currentPage < totalPages) {
            pagination.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadTasks(${currentPage + 1})">Next</a></li>`);
        }
    }

    // View task details
    window.viewTask = function(taskId) {
        currentTaskId = taskId;

        $.ajax({
            url: '{{ url("api/tts/task") }}/' + taskId,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer {{ Helper::getSevenLabsApiKey() }}',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.task) {
                    displayTaskDetails(response.task);
                    $('#taskModal').modal('show');
                } else {
                    alert('Error loading task details: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Error loading task details: ' + (xhr.responseJSON?.message || 'Network error'));
            }
        });
    };

    // Display task details in modal
    function displayTaskDetails(task) {
        const details = `
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-info-circle me-2"></i>Task Information
                            </h6>
                            <div class="mb-2">
                                <small class="text-muted d-block">Task ID</small>
                                <code class="small">${task.id}</code>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Status</small>
                                ${getStatusBadge(task.status)}
                            </div>
                            <div class="mb-0">
                                <small class="text-muted d-block">Created</small>
                                <span class="small">${new Date(task.created_at).toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-gear me-2"></i>Voice Settings
                            </h6>
                            <div class="mb-2">
                                <small class="text-muted d-block">Voice ID</small>
                                <code class="small">${task.voice_id}</code>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Model</small>
                                <span class="small">${task.model_id}</span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Style</small>
                                <span class="small">${task.style}</span>
                            </div>
                            <div class="mb-0">
                                <small class="text-muted d-block">Speed</small>
                                <span class="small">${task.speed}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card border-0">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-chat-text me-2"></i>Input Text
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">${task.input}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            ${task.result ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-volume-up me-2"></i>Generated Audio
                                </h6>
                                <audio controls class="w-100 mb-3">
                                    <source src="${task.result}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="${task.result}" class="btn btn-success" download>
                                        <i class="bi bi-download me-2"></i>Download Audio
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ` : ''}
            
            ${task.subtitle ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-file-text me-2"></i>Subtitle
                                </h6>
                                <a href="${task.subtitle}" class="btn btn-outline-primary" download>
                                    <i class="bi bi-file-text me-2"></i>Download Subtitle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            ` : ''}
        `;

        $('#taskDetails').html(details);
        $('#deleteTaskBtn').show();
    }

    // Delete task
    window.deleteTask = function(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) {
            return;
        }

        $.ajax({
            url: '{{ url("api/tts/task") }}/' + taskId,
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer {{ Helper::getSevenLabsApiKey() }}',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert('Task deleted successfully');
                    loadTasks(currentPage);
                    $('#taskModal').modal('hide');
                } else {
                    alert('Error deleting task: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Error deleting task: ' + (xhr.responseJSON?.message || 'Network error'));
            }
        });
    };

    // Delete task from modal
    $('#deleteTaskBtn').on('click', function() {
        if (currentTaskId) {
            deleteTask(currentTaskId);
        }
    });

    // Custom Audio Player
    window.playAudio = function(audioUrl) {
        // Stop any existing audio
        const existingPlayer = document.getElementById('customAudioPlayer');
        if (existingPlayer) {
            existingPlayer.remove();
        }

        // Create custom audio player
        const audioPlayer = document.createElement('div');
        audioPlayer.id = 'customAudioPlayer';
        audioPlayer.style.cssText = `
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-default, #007bff);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 12px 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
            z-index: 1050;
            color: white;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;

        audioPlayer.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <!-- Track Info -->
                <div class="d-flex align-items-center" style="min-width: 200px;">
                    <div class="me-3">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-music-note-beamed" style="font-size: 18px;"></i>
                        </div>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px; margin-bottom: 2px;">Generated Speech</div>
                        <div style="font-size: 11px; opacity: 0.8;">SevenLabs AI Voice</div>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="d-flex align-items-center">
                    <button class="btn me-2" id="playPauseBtn" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-play-fill" id="playIcon" style="font-size: 14px;"></i>
                    </button>
                    <button class="btn me-2" id="volumeBtn" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-volume-up" id="volumeIcon" style="font-size: 12px;"></i>
                    </button>
                    <div class="me-3" style="width: 60px;">
                        <input type="range" class="form-range" id="volumeSlider" min="0" max="100" value="70" style="height: 3px;">
                    </div>
                    <button class="btn me-2" id="downloadBtn" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-download" style="font-size: 12px;"></i>
                    </button>
                    <button class="btn" onclick="this.closest('#customAudioPlayer').remove()" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-x" style="font-size: 12px;"></i>
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div class="flex-grow-1 mx-3" style="min-width: 200px;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span id="currentTime" style="font-size: 11px; opacity: 0.8;">0:00</span>
                        <span id="duration" style="font-size: 11px; opacity: 0.8;">0:00</span>
                    </div>
                    <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; cursor: pointer;" id="progressBar">
                        <div class="progress-bar" id="progress" style="background: white; border-radius: 2px; transition: width 0.1s;"></div>
                    </div>
                </div>
            </div>
            
            <audio id="hiddenAudio" preload="metadata">
                <source src="${audioUrl}" type="audio/mpeg">
            </audio>
        `;

        document.body.appendChild(audioPlayer);

        // Get elements
        const audio = audioPlayer.querySelector('#hiddenAudio');
        const playPauseBtn = audioPlayer.querySelector('#playPauseBtn');
        const playIcon = audioPlayer.querySelector('#playIcon');
        const progressBar = audioPlayer.querySelector('#progressBar');
        const progress = audioPlayer.querySelector('#progress');
        const currentTimeEl = audioPlayer.querySelector('#currentTime');
        const durationEl = audioPlayer.querySelector('#duration');
        const volumeBtn = audioPlayer.querySelector('#volumeBtn');
        const volumeIcon = audioPlayer.querySelector('#volumeIcon');
        const volumeSlider = audioPlayer.querySelector('#volumeSlider');
        const downloadBtn = audioPlayer.querySelector('#downloadBtn');

        // Format time
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        // Update progress
        function updateProgress() {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = progressPercent + '%';
            currentTimeEl.textContent = formatTime(audio.currentTime);
        }

        // Update duration
        function updateDuration() {
            durationEl.textContent = formatTime(audio.duration);
        }

        // Play/Pause functionality
        playPauseBtn.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                playIcon.className = 'bi bi-pause-fill';
            } else {
                audio.pause();
                playIcon.className = 'bi bi-play-fill';
            }
        });

        // Progress bar click
        progressBar.addEventListener('click', (e) => {
            const rect = progressBar.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            const clickPercent = clickX / width;
            audio.currentTime = clickPercent * audio.duration;
        });

        // Volume control
        volumeSlider.addEventListener('input', (e) => {
            audio.volume = e.target.value / 100;
            if (audio.volume === 0) {
                volumeIcon.className = 'bi bi-volume-mute';
            } else if (audio.volume < 0.5) {
                volumeIcon.className = 'bi bi-volume-down';
            } else {
                volumeIcon.className = 'bi bi-volume-up';
            }
        });

        volumeBtn.addEventListener('click', () => {
            if (audio.volume > 0) {
                audio.volume = 0;
                volumeSlider.value = 0;
                volumeIcon.className = 'bi bi-volume-mute';
            } else {
                audio.volume = 0.7;
                volumeSlider.value = 70;
                volumeIcon.className = 'bi bi-volume-up';
            }
        });

        // Download functionality
        downloadBtn.addEventListener('click', () => {
            const a = document.createElement('a');
            a.href = audioUrl;
            a.download = 'sevenlabs-speech.mp3';
            a.click();
        });

        // Audio event listeners
        audio.addEventListener('loadedmetadata', updateDuration);
        audio.addEventListener('timeupdate', updateProgress);
        audio.addEventListener('ended', () => {
            playIcon.className = 'bi bi-play-fill';
            progress.style.width = '0%';
            currentTimeEl.textContent = '0:00';
        });

        // Auto-play
        audio.play().catch(e => {
            console.log('Auto-play prevented:', e);
            // Don't show alert, just let user click play
        });

        // Set initial volume
        audio.volume = 0.7;
    };
});
</script>
@endsection

@extends('admin.layout')

@section('title', 'APK Version Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">APK Version Management</h3>
                </div>
                <div class="card-body">
                    @if (session('success_message'))
                        <div class="alert alert-success">
                            {{ session('success_message') }}
                        </div>
                    @endif

                    @if (session('error_message'))
                        <div class="alert alert-danger">
                            {{ session('error_message') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the following:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Add/Update APK Link Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Add/Update APK Version</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Workflow Info -->
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>How to add a new APK version:</h6>
                                        <ol class="mb-0">
                                            <li>Choose source: <strong>External Link</strong> or <strong>Upload APK</strong></li>
                                            <li>If using External Link, paste the final download link directly</li>
                                            <li>If using Upload APK, select and upload the APK file from your system</li>
                                            <li>Fill version details and click <strong>"Save APK Version"</strong></li>
                                        </ol>
                                    </div>
                                    
                                    <form action="{{ route('apk.update.link') }}" method="POST" id="apkForm" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" id="edit_version_id" name="version_id" value="">
                                        <input type="hidden" id="selected_apk_file" name="selected_apk_file" value="">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="version_name">Version Name</label>
                                                    <input type="text" class="form-control" id="version_name" name="version_name" 
                                                           placeholder="e.g., 1.0.0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="version_code">Version Code</label>
                                                    <input type="number" class="form-control" id="version_code" name="version_code" 
                                                           placeholder="e.g., 1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="download_source">Source</label>
                                                    <select class="form-control" id="download_source" name="download_source" onchange="toggleDownloadSource()">
                                                        <option value="link">External Link</option>
                                                        <option value="file">Upload APK</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3" id="downloadLinkGroup">
                                                <div class="form-group">
                                                    <label for="download_link">Download Link</label>
                                                    <div class="input-group">
                                                        <input type="url" class="form-control" id="download_link" name="download_link" 
                                                               placeholder="https://example.com/app.apk">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard()">
                                                                <i class="fas fa-copy"></i> Copy
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3" id="apkFileGroup" style="display: none;">
                                                <div class="form-group">
                                                    <label>Upload APK</label>
                                                    <div class="d-flex gap-2 mb-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" id="uploadApkBtn" onclick="triggerAjaxApkUpload()">
                                                            <i class="fas fa-upload me-1"></i> Upload APK
                                                        </button>
                                                        <small class="text-muted align-self-center">Max 100MB (.apk)</small>
                                                    </div>
                                                    <div class="progress mb-2" id="apkUploadProgressWrap" style="height: 10px; display: none;">
                                                        <div id="apkUploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <small id="apkUploadStatus" class="form-text text-muted"></small>
                                                    <input type="file" id="ajax_apk_file_input" accept=".apk,application/vnd.android.package-archive" style="display: none;">
                                                </div>
                                            </div>
                                            <div class="col-md-6" id="apkSelectGroup" style="display: none;">
                                                <div class="form-group">
                                                    <label for="uploaded_apk_file">Select Uploaded APK</label>
                                                    <select class="form-control" id="uploaded_apk_file" onchange="handleUploadedApkSelection()">
                                                        <option value="">Select uploaded file...</option>
                                                        @foreach($uploadedApkFiles as $apkFile)
                                                            <option value="{{ $apkFile['path'] }}">{{ $apkFile['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small id="selectedApkHint" class="form-text text-muted">Upload once, then reuse from this list anytime.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="release_notes">Release Notes</label>
                                                    <textarea class="form-control" id="release_notes" name="release_notes" 
                                                              rows="3" placeholder="What's new in this version..."></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1">
                                                        <label class="form-check-label" for="is_active">
                                                            Set as Active Version
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="is_force_update" name="is_force_update" value="1">
                                                        <label class="form-check-label" for="is_force_update">
                                                            Force Update
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                                    <i class="fas fa-save"></i> <span id="submitBtnText">Save APK Version</span>
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

                    <!-- APK Versions List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Version</th>
                                            <th>Source</th>
                                            <th>Download Link</th>
                                            <th>Release Notes</th>
                                            <th>Status</th>
                                            <th>Downloads</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($apkVersions as $version)
                                        <tr>
                                            <td>
                                                <strong>{{ $version->version_name }}</strong><br>
                                                <small class="text-muted">Code: {{ $version->version_code }}</small>
                                            </td>
                                            <td>
                                                @if($version->apk_file_path)
                                                    <span class="badge badge-primary">Uploaded</span>
                                                @else
                                                    <span class="badge badge-secondary">External Link</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           value="{{ $version->public_download_link }}" readonly>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="copyToClipboard('{{ $version->public_download_link }}')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($version->release_notes)
                                                    <small>{{ Str::limit($version->release_notes, 100) }}</small>
                                                @else
                                                    <span class="text-muted">No notes</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($version->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                                @if($version->is_force_update)
                                                    <br><span class="badge badge-warning">Force Update</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $version->download_count }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick='editVersion(@json($version))'>
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form action="{{ route('apk.toggle.active', $version->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $version->is_active ? 'btn-warning' : 'btn-success' }}">
                                                        {{ $version->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('apk.delete', $version->id) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this APK version?')">
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
                                            <td colspan="7" class="text-center text-muted">No APK versions found</td>
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
const APK_UPLOAD_CHUNK_URL = @json(route('apk.upload.chunk', [], false));

function buildAppUrl(path) {
    if (!path) return window.location.href;
    if (/^https?:\/\//i.test(path)) return path;
    const normalized = path.startsWith('/') ? path : '/' + path;
    return window.location.origin + normalized;
}

function copyToClipboard(text = null) {
    if (text) {
        // Copy specific text
        navigator.clipboard.writeText(text).then(function() {
            showToast('Link copied to clipboard!', 'success');
        });
    } else {
        // Copy from input field
        const input = document.getElementById('download_link');
        if (input.value) {
            navigator.clipboard.writeText(input.value).then(function() {
                showToast('Link copied to clipboard!', 'success');
            });
        } else {
            showToast('Please enter a download link first', 'warning');
        }
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

function editVersion(version) {
    // Populate form with version data
    document.getElementById('edit_version_id').value = version.id;
    document.getElementById('version_name').value = version.version_name;
    document.getElementById('version_code').value = version.version_code;
    document.getElementById('download_link').value = version.download_link;
    document.getElementById('release_notes').value = version.release_notes || '';
    document.getElementById('is_active').checked = version.is_active;
    document.getElementById('is_force_update').checked = version.is_force_update;
    document.getElementById('download_source').value = version.apk_file_path ? 'file' : 'link';
    document.getElementById('selected_apk_file').value = version.apk_file_path || '';
    document.getElementById('uploaded_apk_file').value = version.apk_file_path || '';
    document.getElementById('apkUploadStatus').textContent = version.apk_file_path ? ('Using: ' + (version.apk_file_path.split('/').pop())) : '';
    toggleDownloadSource();
    
    // Update button text
    document.getElementById('submitBtnText').textContent = 'Update APK Version';
    document.getElementById('cancelEditBtn').style.display = 'inline-block';
    
    // Scroll to form
    document.getElementById('apkForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Show toast
    showToast('Editing version ' + version.version_name, 'info');
}

function cancelEdit() {
    // Reset form
    document.getElementById('apkForm').reset();
    document.getElementById('edit_version_id').value = '';
    document.getElementById('submitBtnText').textContent = 'Save APK Version';
    document.getElementById('cancelEditBtn').style.display = 'none';
    document.getElementById('download_source').value = 'link';
    document.getElementById('selected_apk_file').value = '';
    document.getElementById('uploaded_apk_file').value = '';
    document.getElementById('apkUploadStatus').textContent = '';
    document.getElementById('apkUploadProgressWrap').style.display = 'none';
    document.getElementById('apkUploadProgressBar').style.width = '0%';
    toggleDownloadSource();
    
    showToast('Edit cancelled', 'info');
}

function toggleDownloadSource() {
    const source = document.getElementById('download_source').value;
    const linkGroup = document.getElementById('downloadLinkGroup');
    const fileGroup = document.getElementById('apkFileGroup');
    const apkSelectGroup = document.getElementById('apkSelectGroup');
    const linkInput = document.getElementById('download_link');

    if (source === 'file') {
        linkGroup.style.display = 'none';
        fileGroup.style.display = 'block';
        apkSelectGroup.style.display = 'block';
        linkInput.required = false;
    } else {
        linkGroup.style.display = 'block';
        fileGroup.style.display = 'none';
        apkSelectGroup.style.display = 'none';
        linkInput.required = true;
    }
}

function triggerAjaxApkUpload() {
    document.getElementById('ajax_apk_file_input').click();
}

function handleUploadedApkSelection() {
    const selected = document.getElementById('uploaded_apk_file').value;
    document.getElementById('selected_apk_file').value = selected;
    if (selected) {
        const fileName = selected.split('/').pop();
        document.getElementById('apkUploadStatus').textContent = 'Selected: ' + fileName;
    } else {
        document.getElementById('apkUploadStatus').textContent = '';
    }
}

function uploadApkViaAjax(file) {
    const progressWrap = document.getElementById('apkUploadProgressWrap');
    const progressBar = document.getElementById('apkUploadProgressBar');
    const statusText = document.getElementById('apkUploadStatus');
    const uploadBtn = document.getElementById('uploadApkBtn');

    if (!file) {
        showToast('Please select an APK file first.', 'warning');
        return;
    }

    // 100MB in bytes (client-side pre-check)
    const maxBytes = 100 * 1024 * 1024;
    if (file.size > maxBytes) {
        const message = 'Selected file is larger than 100MB.';
        statusText.textContent = message;
        showToast(message, 'danger');
        return;
    }

    progressWrap.style.display = 'block';
    progressBar.style.width = '1%';
    statusText.textContent = 'Uploading ' + file.name + '...';
    uploadBtn.disabled = true;

    const chunkSize = 2 * 1024 * 1024; // 2MB per chunk
    const totalChunks = Math.ceil(file.size / chunkSize);
    const uploadId = 'apk_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);

    let uploadedBytes = 0;
    let finalResponse = null;

    const run = async () => {
        try {
            for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                const start = chunkIndex * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunkBlob = file.slice(start, end);

                const response = await uploadChunkWithRetry({
                    uploadId: uploadId,
                    chunkIndex: chunkIndex,
                    totalChunks: totalChunks,
                    fileName: file.name,
                    chunkBlob: chunkBlob
                });

                uploadedBytes += chunkBlob.size;
                const percent = Math.max(1, Math.min(100, Math.round((uploadedBytes / file.size) * 100)));
                progressBar.style.width = percent + '%';
                statusText.textContent = 'Uploading... ' + percent + '% (' + (chunkIndex + 1) + '/' + totalChunks + ')';

                if (response && response.completed) {
                    finalResponse = response;
                }
            }

            if (!finalResponse || !finalResponse.success || !finalResponse.data) {
                throw new Error('Upload finished but final server response was not received.');
            }

            const filePath = finalResponse.data.file_path;
            const fileName = finalResponse.data.file_name;
            const select = document.getElementById('uploaded_apk_file');
            const existingOption = Array.from(select.options).find(opt => opt.value === filePath);
            if (!existingOption) {
                const option = document.createElement('option');
                option.value = filePath;
                option.text = fileName;
                select.appendChild(option);
            }

            select.value = filePath;
            document.getElementById('selected_apk_file').value = filePath;
            progressBar.style.width = '100%';
            statusText.textContent = 'Upload complete: ' + fileName;
            showToast(finalResponse.message || 'APK uploaded successfully', 'success');
        } catch (error) {
            const message = error && error.message ? error.message : 'Upload failed. Please try again.';
            statusText.textContent = message;
            showToast(message, 'danger');
        } finally {
            uploadBtn.disabled = false;
        }
    };

    run();
}

async function uploadChunkWithRetry(payload) {
    const maxRetries = 3;
    let lastError = null;

    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            return await uploadSingleChunk(payload);
        } catch (error) {
            lastError = error;
            if (attempt < maxRetries) {
                await new Promise(resolve => setTimeout(resolve, attempt * 1000));
            }
        }
    }

    throw lastError || new Error('Chunk upload failed.');
}

function uploadSingleChunk(payload) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('upload_id', payload.uploadId);
        formData.append('chunk_index', payload.chunkIndex);
        formData.append('total_chunks', payload.totalChunks);
        formData.append('file_name', payload.fileName);
        formData.append('apk_chunk', payload.chunkBlob, payload.fileName + '.part' + payload.chunkIndex);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', buildAppUrl(APK_UPLOAD_CHUNK_URL), true);
        xhr.timeout = 2 * 60 * 1000; // 2 min per chunk
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function () {
            let response = null;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                response = null;
            }

            if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
                resolve(response);
                return;
            }

            reject(new Error(extractChunkErrorMessage(xhr.status, response)));
        };

        xhr.onerror = function () {
            reject(new Error('Connection interrupted during chunk upload. Retrying...'));
        };

        xhr.ontimeout = function () {
            reject(new Error('Chunk upload timed out. Retrying...'));
        };

        xhr.send(formData);
    });
}

function extractChunkErrorMessage(status, response) {
    if (response && response.errors) {
        if (response.errors.apk_chunk && response.errors.apk_chunk[0]) return response.errors.apk_chunk[0];
        if (response.errors.file_name && response.errors.file_name[0]) return response.errors.file_name[0];
        const firstKey = Object.keys(response.errors)[0];
        if (firstKey && response.errors[firstKey] && response.errors[firstKey][0]) return response.errors[firstKey][0];
    }

    if (response && response.message) return response.message;
    if (status === 413) return 'Server upload limit reached. Please increase server upload size limits.';
    if (status === 419) return 'Session expired. Refresh page and try again.';
    if (status === 401 || status === 403) return 'Authentication error. Please login again.';
    if (status >= 500) return 'Server error during upload. Please check server logs.';
    if (status > 0) return 'Upload failed with status ' + status + '.';

    return 'Network/proxy error during upload.';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleDownloadSource();
    const ajaxInput = document.getElementById('ajax_apk_file_input');
    ajaxInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            uploadApkViaAjax(this.files[0]);
        }
        this.value = '';
    });

    document.getElementById('apkForm').addEventListener('submit', function (event) {
        const source = document.getElementById('download_source').value;
        const selectedFile = document.getElementById('selected_apk_file').value;
        const downloadLink = document.getElementById('download_link').value;

        if (source === 'file' && !selectedFile) {
            event.preventDefault();
            showToast('Please upload and select an APK file first.', 'warning');
            return;
        }

        if (source === 'link' && !downloadLink) {
            event.preventDefault();
            showToast('Please enter download link.', 'warning');
        }
    });
});
</script>
@endsection

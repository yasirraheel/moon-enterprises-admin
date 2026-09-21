@extends('layouts.app')

@section('title', 'AI Text to Speech Generator - Convert Text to Natural Voice')
@section('description_custom', 'Generate high-quality AI voice synthesis from text. Convert your text to natural-sounding speech with advanced AI technology. Perfect for content creators and developers.')
@section('keywords_custom', 'AI text to speech, voice synthesis, text to speech generator, AI voice, speech generation, voice cloning, natural voice, TTS')

@section('og_title', 'AI Text to Speech Generator - SevenLabs TTS')
@section('og_description', 'Convert text to natural-sounding speech with advanced AI technology. Generate high-quality voice synthesis for content creators, developers, and businesses.')
@section('og_image', $settings->og_image ? url('public/img', $settings->og_image) : url('public/img', $settings->image_header))

@section('css')
<style>
:root {
    --color-default: {{ $settings->color_default ?? '#007bff' }};
    --color-default-rgb: {{ str_replace('#', '', $settings->color_default ?? '#007bff') }};
}
</style>
<link href="{{ asset('public/css/tts-custom.css') }}?v={{$settings->version}}" rel="stylesheet">
@endsection

@section('content')
<!-- Hero Section -->
<div class="container-fluid home-cover">
      <div class="mb-4 position-relative custom-pt-6">
        <div class="container px-3 px-md-5">
          @if ($settings->announcement != '' && $settings->announcement_show == 'all'
              || $settings->announcement != '' && $settings->announcement_show == 'users' && auth()->check())
            <div class="alert alert-{{$settings->type_announcement}} announcements display-none alert-dismissible fade show" role="alert">
              <h4 class="alert-heading"><i class="bi-megaphone me-2"></i> {{ __('admin.announcements') }}</h4>
                    <p class="update-text">{!! $settings->announcement !!}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" id="closeAnnouncements">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if (session('success_message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check2 me-1"></i> {{ session('success_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            @endif

            @if (session('error_message'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
		  @endif

            <div class="text-center text-md-start">
                <h1 class="display-4 display-md-3 fw-bold text-white mb-3">
                    <i class="bi bi-mic me-3"></i>SevenLabs Voice AI
                </h1>
                <p class="col-md-8 fs-5 fw-bold text-white mb-4">
                    Transform text into natural-sounding speech with advanced AI voice synthesis
                </p>
            </div>
        </div>
      </div>
</div>

<!-- Main TTS Interface -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">

                <!-- TTS Form -->
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white" style="background-color: {{ $settings->color_default }};">
                        <h4 class="mb-0"><i class="bi bi-mic me-2"></i>Text-to-Speech Generator</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="ttsForm">
                            @csrf

                            <!-- Text Input -->
                            <div class="form-floating mb-4">
                                <textarea
                                    class="form-control"
                                    id="input"
                                    name="input"
                                    style="height: 120px;"
                                    placeholder="Enter the text you want to convert to speech..."
                                    required
                                ></textarea>
                                <label for="input">
                                    <i class="bi bi-chat-text me-2"></i>Text to Convert
                                </label>
                                <div class="form-text">Enter the text you want to convert to speech</div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="char-counter">
                                        <span id="charCount">0</span> characters
                                    </div>
                                    @auth
                                    <div class="credit-info">
                                        <span id="creditInfo" class="fw-bold text-success">Credits: <span id="userCredits">Loading...</span></span>
                                    </div>
                                    @endauth
                                </div>
                                <div class="credit-warning mt-1" id="creditWarning" style="display: none;">
                                    <small class="text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Insufficient credits! You need <span id="neededCredits">0</span> credits but only have <span id="availableCredits">0</span>.
                                    </small>
                                </div>
                            </div>

                            <!-- Voice Selection -->
                            <div class="row mb-4">
                                <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                    <!-- Voice Selection Mode Toggle -->
                                    <div class="mb-3">
                                        <div class="btn-group w-100 d-flex flex-column flex-sm-row" role="group" aria-label="Voice selection mode">
                                            <input type="radio" class="btn-check" name="voice_mode" id="voice_mode_browse" value="browse" checked>
                                            <label class="btn btn-outline-custom flex-fill mb-2 mb-sm-0 me-sm-2" for="voice_mode_browse">
                                                <i class="bi bi-search me-2"></i>Browse Voices
                                            </label>

                                            <input type="radio" class="btn-check" name="voice_mode" id="voice_mode_manual" value="manual">
                                            <label class="btn btn-outline-custom flex-fill" for="voice_mode_manual">
                                                <i class="bi bi-pencil me-2"></i>Manual Entry
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Browse Mode -->
                                    <div id="browse_mode">
                                        <div class="form-floating">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="voice_display"
                                                placeholder="Click to select a voice..."
                                                value=""
                                                readonly
                                                required
                                            >
                                            <label for="voice_display">
                                                <i class="bi bi-person-voice me-2"></i>Select Voice
                                            </label>
                                            <input
                                                type="hidden"
                                                id="voice_id"
                                                name="voice_id"
                                                value=""
                                            >
                                        </div>
                                        <div class="d-grid mt-2">
                                            <button class="btn btn-outline-secondary" type="button" id="voiceSelectBtn">
                                                <i class="bi bi-search me-2"></i>Browse Voices
                                            </button>
                                        </div>
                                        <div class="form-text">Choose from available voices with preview</div>
                                    </div>

                                    <!-- Manual Mode -->
                                    <div id="manual_mode" style="display: none;">
                                        <div class="form-floating">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="voice_id_manual"
                                                name="voice_id_manual"
                                                placeholder="Enter voice ID manually..."
                                                value=""
                                            >
                                            <label for="voice_id_manual">
                                                <i class="bi bi-key me-2"></i>Voice ID
                                            </label>
                                        </div>
                                        <div class="form-text">Enter the voice ID directly (e.g., 21m00Tcm4TlvDq8ikWAM)</div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="model_id" name="model_id" required>
                                            <option value="eleven_multilingual_v2">Eleven Multilingual v2</option>
                                            <option value="eleven_turbo_v2_5" selected>Eleven Turbo v2.5</option>
                                            <option value="eleven_flash_v2_5">Eleven Flash v2.5</option>
                                            <option value="eleven_v3">Eleven v3</option>
                                        </select>
                                        <label for="model_id">
                                            <i class="bi bi-cpu me-2"></i>Model
                                        </label>
                                    </div>
                                    <div class="form-text">Select the AI model for voice generation</div>
                                </div>
                            </div>

                            <!-- Advanced Settings -->
                            <div class="accordion mb-4" id="advancedSettings">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced">
                                            <i class="bi bi-gear me-2"></i>Advanced Settings
                                        </button>
                                    </h2>
                                    <div id="collapseAdvanced" class="accordion-collapse collapse" data-bs-parent="#advancedSettings">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <!-- Style -->
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="style" class="form-label fw-bold">Style</label>
                                                    <input
                                                        type="range"
                                                        class="form-range"
                                                        id="style"
                                                        name="style"
                                                        min="0"
                                                        max="1"
                                                        step="0.1"
                                                        value="0"
                                                    >
                                                    <div class="d-flex justify-content-between">
                                                        <small>0.0</small>
                                                        <small id="styleValue">0.0</small>
                                                        <small>1.0</small>
                                                    </div>
                                                    <div class="form-text">Voice style variation (0.0 - 1.0)</div>
                                                </div>

                                                <!-- Speed -->
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="speed" class="form-label fw-bold">Speed</label>
                                                    <input
                                                        type="range"
                                                        class="form-range"
                                                        id="speed"
                                                        name="speed"
                                                        min="0.7"
                                                        max="1.2"
                                                        step="0.1"
                                                        value="1"
                                                    >
                                                    <div class="d-flex justify-content-between">
                                                        <small>0.7x</small>
                                                        <small id="speedValue">1.0x</small>
                                                        <small>1.2x</small>
                                                    </div>
                                                    <div class="form-text">Speech speed (0.7 - 1.2)</div>
                                                </div>

                                                <!-- Similarity -->
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="similarity" class="form-label fw-bold">Similarity</label>
                                                    <input
                                                        type="range"
                                                        class="form-range"
                                                        id="similarity"
                                                        name="similarity"
                                                        min="0"
                                                        max="1"
                                                        step="0.05"
                                                        value="0.75"
                                                    >
                                                    <div class="d-flex justify-content-between">
                                                        <small>0.0</small>
                                                        <small id="similarityValue">0.75</small>
                                                        <small>1.0</small>
                                                    </div>
                                                    <div class="form-text">Voice similarity (0.0 - 1.0)</div>
  </div>

                                                <!-- Stability -->
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <label for="stability" class="form-label fw-bold">Stability</label>
                                                    <input
                                                        type="range"
                                                        class="form-range"
                                                        id="stability"
                                                        name="stability"
                                                        min="0"
                                                        max="1"
                                                        step="0.05"
                                                        value="0.5"
                                                    >
                                                    <div class="d-flex justify-content-between">
                                                        <small>0.0</small>
                                                        <small id="stabilityValue">0.5</small>
                                                        <small>1.0</small>
                                                    </div>
                                                    <div class="form-text">Voice stability (0.0 - 1.0)</div>
  </div>

                                                <!-- Speaker Boost -->
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="use_speaker_boost" name="use_speaker_boost">
                                                        <label class="form-check-label fw-bold" for="use_speaker_boost">
                                                            Speaker Boost
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Enhance speaker clarity</div>
                                                </div>

                                                <!-- Callback URL -->
                                                <div class="col-12 col-lg-6 mb-3">
                                                    <div class="form-floating">
                                                        <input
                                                            type="url"
                                                            class="form-control"
                                                            id="call_back_url"
                                                            name="call_back_url"
                                                            placeholder="https://your-domain.com/callback"
                                                        >
                                                        <label for="call_back_url">Callback URL (Optional)</label>
                                                    </div>
                                                    <div class="form-text">URL to receive completion notification</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
  </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-custom btn-lg" id="generateBtn">
                                    <i class="bi bi-play-circle me-2"></i>Generate Speech
                                </button>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <a href="{{ url('tts/tasks') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-list-task me-2"></i>View Tasks
    </a>
  </div>
                            </div>
                        </form>
                    </div>
      </div>

                <!-- Results Section -->
                <div class="card shadow-lg border-0 mt-4" id="resultsCard" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bi bi-check-circle me-2"></i>Generated Audio</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center">
                            <audio controls class="w-100 mb-3" id="audioPlayer">
                                Your browser does not support the audio element.
                            </audio>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button class="btn btn-outline-primary" id="downloadBtn">
                                    <i class="bi bi-download me-2"></i>Download Audio
                                </button>
                                <button class="btn btn-outline-secondary" id="resetBtn">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Generate New
                                </button>
                            </div>
                        </div>
                    </div>
  </div>

                <!-- Loading Spinner -->
                <div class="text-center mt-4" id="loadingSpinner" style="display: none;">
                    <div class="spinner-border" role="status" style="color: {{ $settings->color_default }};">
                        <span class="visually-hidden">Generating...</span>
    </div>
                    <p class="mt-2">Generating your speech, please wait...</p>
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
                <h2 class="mb-5">Why Choose SevenLabs Voice AI?</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-globe display-4 mb-3" style="color: {{ $settings->color_default }};"></i>
                        <h5 class="card-title">Multilingual Support</h5>
                        <p class="card-text">Generate speech in multiple languages with natural pronunciation and accent.</p>
                    </div>
              </div>
            </div>
            <div class="col-12 col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-lightning display-4 mb-3" style="color: {{ $settings->color_default }};"></i>
                        <h5 class="card-title">Fast Generation</h5>
                        <p class="card-text">Get high-quality speech output in seconds with our optimized AI models.</p>
          </div>
              </div>
            </div>
            <div class="col-12 col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-sliders display-4 mb-3" style="color: {{ $settings->color_default }};"></i>
                        <h5 class="card-title">Customizable</h5>
                        <p class="card-text">Fine-tune voice parameters including speed, style, and stability.</p>
          </div>
              </div>
            </div>
          </div>
        </div>
      </div>

<!-- Voice Selection Modal -->
<div class="modal fade" id="voiceSelectModal" tabindex="-1" aria-labelledby="voiceSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voiceSelectModalLabel">
                    <i class="bi bi-person-voice me-2"></i>Select Voice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Bar -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="voiceSearch" placeholder="Search voices by name, accent, gender, or use case...">
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-secondary btn-sm filter-btn active" data-filter="all">All</button>
                        <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="male">Male</button>
                        <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="female">Female</button>
                        <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="american">American</button>
                        <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="british">British</button>
                        <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="conversational">Conversational</button>
                        <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="news">News</button>
                    </div>
                </div>

                <!-- Voice Grid -->
                <div class="row" id="voiceGrid">
                    <!-- Voices will be loaded here -->
                </div>

                <!-- Loading Spinner -->
                <div class="text-center" id="voiceLoading" style="display: none;">
                    <div class="spinner-border" role="status" style="color: {{ $settings->color_default }};">
                        <span class="visually-hidden">Loading voices...</span>
                    </div>
                    <p class="mt-2">Loading voices...</p>
                </div>

                <!-- No Results -->
                <div class="text-center" id="noVoicesFound" style="display: none;">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h5 class="mt-3">No voices found</h5>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
	<script type="text/javascript">
$(document).ready(function() {
    let voices = [];
    let filteredVoices = [];
    let currentFilter = 'all';

    // Load saved model selection from localStorage
    function loadSavedModel() {
        const savedModel = localStorage.getItem('sevenlabs_selected_model');
        if (savedModel) {
            $('#model_id').val(savedModel);
        }
    }

    // Save model selection to localStorage
    function saveModelSelection() {
        const selectedModel = $('#model_id').val();
        localStorage.setItem('sevenlabs_selected_model', selectedModel);
    }

    // Load saved model on page load
    loadSavedModel();
    
    // Load saved voice on page load
    loadSavedVoice();
    
    // Load user credits on page load (only for authenticated users)
    @auth
    loadUserCredits();
    @endauth

    // Save model selection when changed
    $('#model_id').on('change', function() {
        saveModelSelection();
    });

    // Range slider value updates
    $('#style').on('input', function() {
        $('#styleValue').text($(this).val());
    });

    $('#speed').on('input', function() {
        $('#speedValue').text($(this).val() + 'x');
    });

    $('#similarity').on('input', function() {
        $('#similarityValue').text($(this).val());
    });

    $('#stability').on('input', function() {
        $('#stabilityValue').text($(this).val());
    });

    // Voice mode switching
    $('input[name="voice_mode"]').change(function() {
        const mode = $(this).val();
        if (mode === 'browse') {
            $('#browse_mode').show();
            $('#manual_mode').hide();
            $('#voice_id').prop('required', true);
            $('#voice_id_manual').prop('required', false);
        } else {
            $('#browse_mode').hide();
            $('#manual_mode').show();
            $('#voice_id').prop('required', false);
            $('#voice_id_manual').prop('required', true);
        }
    });

    // Voice Selection Modal
    $('#voiceSelectBtn').on('click', function() {
        // Check if user is logged in
        @guest
        showFlashMessage('error', 'Please login first to browse voices. <a href="{{ url("login") }}" class="text-white text-decoration-underline">Click here to login</a>');
        return;
        @endguest
        
        $('#voiceSelectModal').modal('show');
        if (voices.length === 0) {
            loadVoices();
        }
        // Load saved voice selection
        loadSavedVoice();
    });

    // Load voices from API
    function loadVoices() {
        $('#voiceLoading').show();
        $('#voiceGrid').empty();
        $('#noVoicesFound').hide();

        $.ajax({
            url: '{{ url("api/tts/voices/local") }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data && response.data.voices) {
                    voices = response.data.voices;
                    filteredVoices = [...voices];
                    displayVoices();
                } else {
                    $('#voiceGrid').html('<div class="col-12 text-center text-warning"><i class="bi bi-exclamation-triangle"></i> No voices available</div>');
                }
                $('#voiceLoading').hide();
            },
            error: function(xhr) {
                $('#voiceLoading').hide();
                let errorMessage = 'Failed to load voices';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#voiceGrid').html(`<div class="col-12 text-center text-danger"><i class="bi bi-exclamation-triangle"></i> ${errorMessage}</div>`);
            }
        });
    }

    // Display voices in grid
    function displayVoices() {
        const grid = $('#voiceGrid');
        grid.empty();

        if (filteredVoices.length === 0) {
            $('#noVoicesFound').show();
            return;
        }

        filteredVoices.forEach(function(voice) {
            const voiceCard = createVoiceCard(voice);
            grid.append(voiceCard);
        });
    }

    // Create voice card HTML
    function createVoiceCard(voice) {
        const labels = voice.labels || {};
        const description = voice.description || 'No description available';
        const accent = labels.accent || 'Unknown';
        const gender = labels.gender || 'Unknown';
        const age = labels.age || 'Unknown';
        const useCase = labels.use_case || 'General';
        const descriptive = labels.descriptive || '';

        return `
            <div class="col-12 col-md-6 col-lg-4 mb-3 voice-card" data-gender="${gender}" data-accent="${accent}" data-use-case="${useCase}">
                <div class="card h-100 voice-item" data-voice-id="${voice.voice_id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${voice.name}</h6>
                            <span class="badge" style="background-color: {{ $settings->color_default }};">${voice.voice_id.substring(0, 8)}...</span>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-person"></i> ${gender} •
                                <i class="bi bi-globe"></i> ${accent} •
                                <i class="bi bi-clock"></i> ${age}
                            </small>
                        </div>

                        <p class="card-text small text-muted mb-3">${description}</p>

                        <div class="mb-3">
                            <span class="badge bg-light text-dark me-1">${useCase}</span>
                            ${descriptive ? `<span class="badge bg-light text-dark">${descriptive}</span>` : ''}
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-custom btn-sm flex-fill preview-btn" data-voice-id="${voice.voice_id}" data-preview-url="${voice.preview_url}">
                                <i class="bi bi-play-circle me-1"></i>Preview
                            </button>
                            <button class="btn btn-custom btn-sm select-voice-btn" data-voice-id="${voice.voice_id}" data-voice-name="${voice.name}">
                                <i class="bi bi-check"></i>Select
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Voice search functionality
    $('#voiceSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterVoices(searchTerm, currentFilter);
    });

    // Filter buttons
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        const searchTerm = $('#voiceSearch').val().toLowerCase();
        filterVoices(searchTerm, currentFilter);
    });

    // Filter voices based on search and filter
    function filterVoices(searchTerm, filter) {
        filteredVoices = voices.filter(function(voice) {
            const labels = voice.labels || {};
            const name = voice.name.toLowerCase();
            const description = (voice.description || '').toLowerCase();
            const accent = (labels.accent || '').toLowerCase();
            const gender = (labels.gender || '').toLowerCase();
            const useCase = (labels.use_case || '').toLowerCase();
            const descriptive = (labels.descriptive || '').toLowerCase();

            const matchesSearch = searchTerm === '' ||
                name.includes(searchTerm) ||
                description.includes(searchTerm) ||
                accent.includes(searchTerm) ||
                gender.includes(searchTerm) ||
                useCase.includes(searchTerm) ||
                descriptive.includes(searchTerm);

            const matchesFilter = filter === 'all' ||
                gender.includes(filter) ||
                accent.includes(filter) ||
                useCase.includes(filter);

            return matchesSearch && matchesFilter;
        });

        displayVoices();
    }

    // Preview voice functionality
    $(document).on('click', '.preview-btn', function() {
        const previewUrl = $(this).data('preview-url');
        const voiceId = $(this).data('voice-id');

        // Stop any currently playing audio
        $('audio').each(function() {
            this.pause();
            this.currentTime = 0;
        });

        // Create or update audio element
        let audioElement = $('#previewAudio');
        if (audioElement.length === 0) {
            audioElement = $('<audio id="previewAudio" controls class="w-100 mt-2" style="display: none;"></audio>');
            $('body').append(audioElement);
        }

        audioElement.attr('src', previewUrl);
        audioElement.show();
        audioElement[0].play();

        // Update button state
        $('.preview-btn').removeClass('btn-success').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-success');
    });

    // Select voice functionality
    $(document).on('click', '.select-voice-btn', function() {
        const voiceId = $(this).data('voice-id');
        const voiceName = $(this).data('voice-name');

        // Update the hidden input with voice ID (for backend)
        $('#voice_id').val(voiceId);

        // Update the display input with voice name (for user)
        $('#voice_display').val(voiceName);

        // Save selected voice to localStorage
        localStorage.setItem('selectedVoice', JSON.stringify({
            voiceId: voiceId,
            voiceName: voiceName,
            timestamp: Date.now()
        }));

        // Highlight the selected voice
        highlightSelectedVoice(voiceId);

        // Close modal
        $('#voiceSelectModal').modal('hide');

        // Show success message
        const toast = $(`
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>Selected voice: ${voiceName}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        // Remove toast after it's hidden
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    });

    // TTS Form submission
    $('#ttsForm').on('submit', function(e) {
        e.preventDefault();

        // Show loading spinner
        $('#loadingSpinner').show();
        $('#resultsCard').hide();
        $('#generateBtn').prop('disabled', true);

        // Get voice ID based on mode
        let voiceId = '';
        const voiceMode = $('input[name="voice_mode"]:checked').val();

        if (voiceMode === 'browse') {
            voiceId = $('#voice_id').val();
        } else {
            voiceId = $('#voice_id_manual').val();
        }

        // Validate voice selection
        if (!voiceId) {
            showFlashMessage('error', 'Please select a voice or enter a voice ID before generating speech.');
            if (voiceMode === 'browse') {
                $('#voiceSelectBtn').focus();
            } else {
                $('#voice_id_manual').focus();
            }
            $('#loadingSpinner').hide();
            $('#generateBtn').prop('disabled', false);
            return;
        }

        // Collect form data
        const formData = {
            input: $('#input').val(),
            voice_id: voiceId,
            model_id: $('#model_id').val(),
            style: parseFloat($('#style').val()),
            speed: parseFloat($('#speed').val()),
            use_speaker_boost: $('#use_speaker_boost').is(':checked'),
            similarity: parseFloat($('#similarity').val()),
            stability: parseFloat($('#stability').val()),
            call_back_url: $('#call_back_url').val() || null
        };

        // Check if API key is configured
        @if(!Helper::hasSevenLabsApiKey())
        showFlashMessage('error', 'SevenLabs API key is not configured. Please contact administrator.');
        $('#loadingSpinner').hide();
        $('#generateBtn').prop('disabled', false);
        return;
        @endif

        // Make API call to GenAI Pro
        $.ajax({
            url: '{{ url("api/tts/generate") }}',
            method: 'POST',
            headers: {
                'Authorization': 'Bearer {{ Helper::getSevenLabsApiKey() }}',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success && response.task_id) {
                    // Save model selection on successful submission
                    saveModelSelection();
                    // Task created successfully, start polling
                    pollTaskStatus(response.task_id);
                } else {
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false);
                    showFlashMessage('error', response.message || 'Failed to create task');
                }
            },
            error: function(xhr) {
                $('#loadingSpinner').hide();
                $('#generateBtn').prop('disabled', false);

                let errorMessage = 'An error occurred while generating speech.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    errorMessage = 'API key is invalid or missing. Please check your SevenLabs API configuration.';
                } else if (xhr.status === 429) {
                    errorMessage = 'Rate limit exceeded. Please try again later.';
                }

                showFlashMessage('error', errorMessage);
            }
        });
    });

    // Character counter and credit checker for text input
    $('#input').on('input', function() {
        const text = $(this).val();
        const charCount = text.length;
        const maxChars = 5000; // Adjust based on API limits
        
        // Update character count display
        $('#charCount').text(charCount);
        
        // Check if text exceeds maximum length
        if (charCount > maxChars) {
            $(this).addClass('is-invalid');
            if (!$('#charCountError').length) {
                $(this).after('<div id="charCountError" class="invalid-feedback">Text exceeds maximum length of ' + maxChars + ' characters.</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $('#charCountError').remove();
        }
        
        // Check credits and show warning if insufficient (only for authenticated users)
        @auth
        checkCreditsForText(charCount);
        @endauth
    });

    // Auto-resize textarea
    $('#input').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Poll task status function
    function pollTaskStatus(taskId) {
        const maxAttempts = 180; // 3 minutes max wait for longer TTS
        let attempts = 0;
        let progressMessage = 'Generating your speech, please wait...';

        const pollInterval = setInterval(function() {
            attempts++;

            // Update progress message based on time elapsed
            if (attempts > 30) {
                progressMessage = 'Processing longer audio, this may take a few minutes...';
            } else if (attempts > 60) {
                progressMessage = 'Almost done, please be patient...';
            }

            // Update the loading message
            $('#loadingSpinner').html(`
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">${progressMessage}</p>
                <small class="text-muted">Time elapsed: ${Math.floor(attempts / 60)}:${(attempts % 60).toString().padStart(2, '0')}</small>
            `);

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
                        const task = response.task;

                        if (task.status === 'completed' && task.result) {
                            // Task completed successfully
                            clearInterval(pollInterval);
                            $('#loadingSpinner').hide();
                            $('#generateBtn').prop('disabled', false);

                            // Show results
                            $('#audioPlayer').attr('src', task.result);
                            $('#resultsCard').show();

                            // Set download link
                            $('#downloadBtn').off('click').on('click', function() {
                                const link = document.createElement('a');
                                link.href = task.result;
                                link.download = 'generated_speech_' + Date.now() + '.mp3';
                                link.click();
                            });

                            // Reset form
                            $('#resetBtn').off('click').on('click', function() {
                                $('#ttsForm')[0].reset();
                                $('#voice_display').val(''); // Clear display field
                                $('#voice_id').val(''); // Clear hidden ID field
                                $('#voice_id_manual').val(''); // Clear manual ID field
                                $('#resultsCard').hide();
                                $('#styleValue').text('0.0');
                                $('#speedValue').text('1.0x');
                                $('#similarityValue').text('0.75');
                                $('#stabilityValue').text('0.5');

                                // Reset to browse mode
                                $('#voice_mode_browse').prop('checked', true);
                                $('#browse_mode').show();
                                $('#manual_mode').hide();
                                $('#voice_id').prop('required', true);
                                $('#voice_id_manual').prop('required', false);

                                // Reload saved model (don't reset it)
                                loadSavedModel();
                            });

                        } else if (task.status === 'failed') {
                            // Task failed
                            clearInterval(pollInterval);
                            $('#loadingSpinner').hide();
                            $('#generateBtn').prop('disabled', false);
                            showFlashMessage('error', 'Task failed: ' + (task.error || 'Unknown error'));

                        } else if (attempts >= maxAttempts) {
                            // Timeout
                            clearInterval(pollInterval);
                            $('#loadingSpinner').hide();
                            $('#generateBtn').prop('disabled', false);
                            showFlashMessage('error', 'Task is taking longer than expected. This can happen with longer audio files. Please try again in a few minutes or contact support if the issue persists.');
                        }
                        // If status is 'pending' or 'processing', continue polling
                    } else {
                        // Error in response
                        clearInterval(pollInterval);
                        $('#loadingSpinner').hide();
                        $('#generateBtn').prop('disabled', false);
                        showFlashMessage('error', 'Error checking task status: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    clearInterval(pollInterval);
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false);
                    showFlashMessage('error', 'Error checking task status: ' + (xhr.responseJSON?.message || 'Network error'));
                }
            });
        }, 1000); // Poll every 1 second
    }

    // Check for cached results on page load
    function checkCachedResults() {
        // This would check for any cached results from callbacks
        // Implementation depends on your caching strategy
    }

    // Load saved voice selection from localStorage
    function loadSavedVoice() {
        try {
            const savedVoice = localStorage.getItem('selectedVoice');
            if (savedVoice) {
                const voiceData = JSON.parse(savedVoice);
                // Check if saved voice is not too old (e.g., 30 days)
                const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);
                if (voiceData.timestamp > thirtyDaysAgo) {
                    // Set the form fields
                    $('#voice_id').val(voiceData.voiceId);
                    $('#voice_display').val(voiceData.voiceName);
                    
                    // Highlight the selected voice in the modal
                    highlightSelectedVoice(voiceData.voiceId);
                }
            }
        } catch (e) {
            console.log('No saved voice found or error loading saved voice');
        }
    }

    // Highlight the selected voice in the modal
    function highlightSelectedVoice(voiceId) {
        // Remove any existing highlights
        $('.voice-card').removeClass('selected-voice');
        $('.select-voice-btn').removeClass('btn-success').addClass('btn-custom');
        
        // Find and highlight the selected voice
        const selectedCard = $(`.voice-card[data-voice-id="${voiceId}"]`);
        if (selectedCard.length > 0) {
            selectedCard.addClass('selected-voice');
            selectedCard.find('.select-voice-btn').removeClass('btn-custom').addClass('btn-success');
        }
    }

    // Load user credits from API (only for authenticated users)
    @auth
    function loadUserCredits() {
        $.ajax({
            url: '{{ url("api/user/credits") }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#userCredits').text(response.user_credits);
                    window.userCredits = response.user_credits;
                }
            },
            error: function() {
                $('#userCredits').text('Error loading');
                window.userCredits = 0;
            }
        });
    }
    @endauth

    // Check if user has enough credits for the text (only for authenticated users)
    @auth
    function checkCreditsForText(charCount) {
        if (!window.userCredits) return;
        
        const neededCredits = Math.max(1, charCount); // 1 credit per character, minimum 1
        const availableCredits = window.userCredits;
        
        if (neededCredits > availableCredits) {
            // Show warning
            $('#creditWarning').show();
            $('#neededCredits').text(neededCredits);
            $('#availableCredits').text(availableCredits);
            
            // Add red styling to character counter
            $('#charCount').addClass('text-danger fw-bold');
            $('#creditInfo').addClass('text-danger');
        } else {
            // Hide warning
            $('#creditWarning').hide();
            
            // Remove red styling
            $('#charCount').removeClass('text-danger fw-bold');
            $('#creditInfo').removeClass('text-danger');
        }
    }
    @endauth

    // Function to show flash messages
    function showFlashMessage(type, message) {
        // Remove any existing flash messages
        $('.flash-message').remove();

        // Create flash message element
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        const iconClass = type === 'error' ? 'bi-exclamation-triangle' : 'bi-check2';

        const flashMessage = $(`
            <div class="alert ${alertClass} alert-dismissible fade show flash-message" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px; transform: translateX(100%); opacity: 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <i class="bi ${iconClass} me-2"></i> ${message}
                    </div>
                    <div class="ms-2">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="progress-timer" style="position: absolute; bottom: 0; left: 0; height: 3px; background: rgba(255,255,255,0.3); width: 100%; border-radius: 0 0 0.375rem 0.375rem;">
                    <div class="progress-bar" style="height: 100%; background: ${type === 'error' ? '#dc3545' : '#198754'}; width: 100%; border-radius: 0 0 0.375rem 0.375rem; transition: width 5s linear;"></div>
                </div>
            </div>
        `);

        // Add to body
        $('body').append(flashMessage);

        // Animate in
        setTimeout(function() {
            flashMessage.css({
                'transform': 'translateX(0)',
                'opacity': '1'
            });
        }, 10);

        // Start progress bar animation
        setTimeout(function() {
            flashMessage.find('.progress-bar').css('width', '0%');
        }, 100);

        // Auto remove after 5 seconds with smooth animation
        setTimeout(function() {
            flashMessage.css({
                'transform': 'translateX(100%)',
                'opacity': '0'
            });
            setTimeout(function() {
                flashMessage.remove();
            }, 400);
        }, 5000);

        // Handle manual close with animation
        flashMessage.find('.btn-close').on('click', function() {
            flashMessage.css({
                'transform': 'translateX(100%)',
                'opacity': '0'
            });
            setTimeout(function() {
                flashMessage.remove();
            }, 400);
        });
    }
});

// Session messages
		@if (session('success_verify'))
		swal({
			title: "{{ __('misc.welcome') }}",
			text: "{{ __('users.account_validated') }}",
			type: "success",
			confirmButtonText: "{{ __('users.ok') }}"
			});
		@endif

		@if (session('error_verify'))
		swal({
			title: "{{ __('misc.error_oops') }}",
			text: "{{ __('users.code_not_valid') }}",
			type: "error",
			confirmButtonText: "{{ __('users.ok') }}"
			});
		@endif
	</script>
@endsection

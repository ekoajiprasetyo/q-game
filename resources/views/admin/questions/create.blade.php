@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .options-container {
        background: var(--cream);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        margin-top: 0.5rem;
    }

    .option-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 0.875rem;
        background: var(--white);
        border-radius: var(--radius-md);
        border: 2px solid var(--light-gray);
        transition: var(--transition);
    }

    .option-item:hover {
        border-color: var(--primary-light);
    }

    .option-item.correct {
        border-color: var(--accent-green);
        background: rgba(125, 206, 160, 0.1);
    }

    .option-key {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--primary), var(--accent-yellow));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .option-item.correct .option-key {
        background: linear-gradient(135deg, var(--accent-green), #5AB890);
    }

    .option-input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.95rem;
        padding: 0.5rem;
        outline: none;
        font-family: inherit;
    }

    .option-correct-btn {
        padding: 0.5rem 0.875rem;
        border-radius: var(--radius-full);
        border: 2px solid var(--light-gray);
        background: var(--white);
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 600;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.375rem;
        color: var(--gray);
    }

    .option-correct-btn:hover {
        border-color: var(--accent-green);
        color: var(--accent-green);
    }

    .option-correct-btn.active {
        background: var(--accent-green);
        border-color: var(--accent-green);
        color: white;
    }

    .option-remove-btn {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        border: none;
        background: var(--cream);
        color: var(--gray);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .option-remove-btn:hover {
        background: var(--danger);
        color: white;
    }

    .add-option-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.875rem;
        border: 2px dashed var(--primary-light);
        border-radius: var(--radius-md);
        background: transparent;
        color: var(--primary);
        cursor: pointer;
        font-weight: 600;
        transition: var(--transition);
        font-family: inherit;
    }

    .add-option-btn:hover {
        background: rgba(255, 155, 80, 0.1);
    }

    .true-false-options {
        display: flex;
        gap: 1rem;
    }

    .true-false-option {
        flex: 1;
        padding: 1.25rem;
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-lg);
        cursor: pointer;
        text-align: center;
        font-weight: 700;
        font-size: 1.1rem;
        transition: var(--transition);
        background: var(--white);
    }

    .true-false-option:hover {
        border-color: var(--primary-light);
    }

    .true-false-option.selected {
        border-color: var(--accent-green);
        background: rgba(125, 206, 160, 0.15);
        color: #4A9D6E;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .type-selector {
        display: flex;
        gap: 0.75rem;
    }

    .type-option {
        flex: 1;
        padding: 1rem;
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-lg);
        cursor: pointer;
        text-align: center;
        transition: var(--transition);
        background: var(--white);
    }

    .type-option:hover {
        border-color: var(--primary-light);
    }

    .type-option.selected {
        border-color: var(--primary);
        background: rgba(255, 155, 80, 0.1);
    }

    .type-option-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .type-option-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--dark);
    }

    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    .hidden-radio {
        opacity: 0;
        position: absolute;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    /* Toggle Switch Styles */
    .toggle-switch {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        user-select: none;
    }

    .toggle-track {
        width: 48px;
        height: 26px;
        background: var(--light-gray);
        border-radius: 13px;
        position: relative;
        transition: background 0.3s ease;
    }

    .toggle-track::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .toggle-input {
        display: none;
    }

    .toggle-input:checked + .toggle-track {
        background: linear-gradient(135deg, var(--primary), var(--accent-yellow));
    }

    .toggle-input:checked + .toggle-track::after {
        transform: translateX(22px);
    }

    .toggle-label {
        font-size: 0.9rem;
        color: var(--dark);
        font-weight: 500;
    }

    /* Image Upload Styles */
    .image-upload-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: var(--cream);
        border-radius: var(--radius-lg);
        border: 2px dashed var(--light-gray);
    }

    .image-upload-section.has-image {
        border-style: solid;
        border-color: var(--primary-light);
    }

    .image-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: var(--white);
        border: 2px dashed var(--primary-light);
        border-radius: var(--radius-md);
        color: var(--primary);
        cursor: pointer;
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition);
    }

    .image-upload-btn:hover {
        background: rgba(255, 155, 80, 0.1);
        border-color: var(--primary);
    }

    .image-preview-wrapper {
        position: relative;
        display: inline-block;
        margin-top: 0.5rem;
    }

    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: var(--radius-md);
        border: 2px solid var(--light-gray);
    }

    .image-actions {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 0.5rem;
    }

    .image-action-btn {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: var(--transition);
    }

    .image-action-btn.edit {
        background: var(--primary);
        color: white;
    }

    .image-action-btn.delete {
        background: var(--danger);
        color: white;
    }

    .image-action-btn:hover {
        transform: scale(1.1);
    }

    /* Image Resize Modal */
    .image-resize-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
    }

    .image-resize-modal.active {
        opacity: 1;
        visibility: visible;
    }

    .resize-modal-content {
        background: white;
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .resize-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--light-gray);
    }

    .resize-modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
    }

    .resize-preview-container {
        text-align: center;
        margin-bottom: 1rem;
        background: var(--cream);
        padding: 1rem;
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .resize-preview-container img {
        max-width: 100%;
        max-height: 300px;
        transition: transform 0.2s ease;
    }

    .resize-controls {
        margin-bottom: 1rem;
    }

    .resize-slider-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .resize-slider {
        flex: 1;
        -webkit-appearance: none;
        height: 8px;
        border-radius: 4px;
        background: var(--light-gray);
        outline: none;
    }

    .resize-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
    }

    .resize-value {
        font-weight: 700;
        color: var(--primary);
        min-width: 50px;
        text-align: center;
    }

    .resize-modal-footer {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid var(--light-gray);
    }
</style>
@endpush

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.questions.index') }}" class="btn btn-ghost btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--gray); font-weight: 500;">
            <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
            Kembali ke Daftar Soal
        </a>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <h1 class="page-title">
                    <div class="page-title-icon">
                        <i data-feather="plus"></i>
                    </div>
                    Tambah Pertanyaan
                </h1>
                <p class="page-subtitle">Buat pertanyaan baru untuk bank soal</p>
            </div>
            <div class="header-actions">
                <!-- Actions can go here if needed -->
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm" enctype="multipart/form-data">
                @csrf

                <!-- Material Selection -->
                <div class="form-group">
                    <label class="form-label">Pilih Materi *</label>
                    <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.75rem;">
                        Pertanyaan akan ditautkan ke materi yang dipilih
                    </p>
                    <div class="d-flex gap-3">
                        <select id="topic_select" class="form-control" style="flex: 1;">
                            <option value="">-- Pilih Topik --</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ (isset($selectedTopicId) && $selectedTopicId == $topic->id) ? 'selected' : '' }}>
                                    {{ $topic->icon ?? '📚' }} {{ $topic->name }}
                                </option>
                            @endforeach
                        </select>
                        <select id="material_id" name="material_id" class="form-control" style="flex: 1;" required>
                            <option value="">-- Pilih Materi --</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}" data-topic="{{ $material->topic_id }}" {{ $selectedMaterialId == $material->id ? 'selected' : '' }}>
                                    {{ $material->icon ?? '📄' }} {{ $material->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Question Type -->
                <div class="form-group">
                    <label class="form-label">Tipe Pertanyaan *</label>
                    <div class="type-selector" style="flex-wrap: wrap;">
                        <label class="type-option {{ old('question_type', 'multiple_choice') === 'multiple_choice' ? 'selected' : '' }}" data-type="multiple_choice">
                            <input type="radio" name="question_type" value="multiple_choice" {{ old('question_type', 'multiple_choice') === 'multiple_choice' ? 'checked' : '' }} class="hidden-radio">
                            <div class="type-option-icon">🔤</div>
                            <div class="type-option-label">Pilihan Ganda</div>
                        </label>
                        <label class="type-option {{ old('question_type') === 'multiple_answer' ? 'selected' : '' }}" data-type="multiple_answer">
                            <input type="radio" name="question_type" value="multiple_answer" {{ old('question_type') === 'multiple_answer' ? 'checked' : '' }} class="hidden-radio">
                            <div class="type-option-icon">☑️</div>
                            <div class="type-option-label">PG Kompleks</div>
                        </label>
                        <label class="type-option {{ old('question_type') === 'true_false' ? 'selected' : '' }}" data-type="true_false">
                            <input type="radio" name="question_type" value="true_false" {{ old('question_type') === 'true_false' ? 'checked' : '' }} class="hidden-radio">
                            <div class="type-option-icon">✓✗</div>
                            <div class="type-option-label">Benar / Salah</div>
                        </label>
                        <label class="type-option {{ old('question_type') === 'short_answer' ? 'selected' : '' }}" data-type="short_answer">
                            <input type="radio" name="question_type" value="short_answer" {{ old('question_type') === 'short_answer' ? 'checked' : '' }} class="hidden-radio">
                            <div class="type-option-icon">⌨️</div>
                            <div class="type-option-label">Isian Singkat</div>
                        </label>
                    </div>
                </div>

                <!-- Image Upload Section (Above Question) -->
                <div class="form-group">
                    <label class="form-label">📷 Gambar Pertanyaan (Opsional)</label>
                    <div class="image-upload-section" id="imageUploadSection">
                        <div id="imageUploadPlaceholder">
                            <label class="image-upload-btn" for="question_image">
                                <i data-feather="image" style="width: 18px; height: 18px;"></i>
                                Pilih Gambar
                            </label>
                            <input type="file" id="question_image" name="question_image" accept="image/*" style="display: none;" onchange="openResizeModal(this)">
                            <p class="text-muted" style="font-size: 0.8rem; margin-top: 0.5rem; margin-bottom: 0;">Format: JPG, PNG, GIF. Max: 5MB</p>
                        </div>
                        <div id="imagePreviewWrapper" class="image-preview-wrapper" style="display: none;">
                            <img id="imagePreview" class="image-preview" src="" alt="Preview">
                            <div class="image-actions">
                                <button type="button" class="image-action-btn edit" onclick="openResizeModalEdit()" title="Resize Gambar">
                                    <i data-feather="maximize-2" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button type="button" class="image-action-btn delete" onclick="removeImage()" title="Hapus Gambar">
                                    <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="imageScale" name="image_scale" value="100">
                    </div>
                </div>

                <!-- Question Text -->
                <div class="form-group">
                    <label class="form-label" for="question_text">Pertanyaan *</label>
                    <textarea
                        id="question_text"
                        name="question_text"
                        class="form-control"
                        placeholder="Tuliskan pertanyaan di sini..."
                        rows="4"
                        required
                    >{{ old('question_text') }}</textarea>
                </div>

                <!-- Multiple Choice Options -->
                <div class="form-group" id="multipleChoiceSection">
                    <label class="form-label">Pilihan Jawaban *</label>
                    <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                        Klik "✓ Benar" untuk menandai jawaban yang benar
                    </p>
                    <div class="options-container">
                        <div id="optionsContainer"></div>
                        <button type="button" class="add-option-btn" id="addOptionBtn">
                            <i data-feather="plus"></i>
                            Tambah Opsi
                        </button>
                    </div>
                </div>

                <!-- Multiple Answer Options (PG Kompleks) -->
                <div class="form-group" id="multipleAnswerSection" style="display: none;">
                    <label class="form-label">Pilihan Jawaban *</label>
                    <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                        Centang semua jawaban yang benar (bisa lebih dari satu)
                    </p>
                    <div class="options-container">
                        <div id="multiAnswerOptionsContainer"></div>
                        <button type="button" class="add-option-btn" id="addMultiAnswerOptionBtn">
                            <i data-feather="plus"></i>
                            Tambah Opsi
                        </button>
                    </div>
                </div>

                <!-- True/False Options -->
                <div class="form-group" id="trueFalseSection" style="display: none;">
                    <label class="form-label">Jawaban Benar *</label>
                    <div class="true-false-options">
                        <div class="true-false-option" data-value="T">✓ Benar</div>
                        <div class="true-false-option" data-value="F">✗ Salah</div>
                    </div>
                </div>

                <!-- Short Answer Options -->
                <div class="form-group" id="shortAnswerSection" style="display: none;">
                    <div class="d-flex justify-between align-center mb-2">
                        <label class="form-label" style="margin-bottom: 0;">Jawaban Benar *</label>
                        <button type="button" class="btn btn-ghost btn-sm" id="addShortAnswerBtn" style="color: var(--primary);">
                            <i data-feather="plus" style="width: 14px; height: 14px;"></i> Tambah Jawaban
                        </button>
                    </div>

                    <div id="shortAnswersContainer" class="d-grid gap-2"></div>

                    <div class="mt-3">
                        <label class="toggle-switch">
                            <input type="checkbox" id="case_sensitive" name="case_sensitive" class="toggle-input" value="1" {{ old('case_sensitive') ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                            <span class="toggle-label">Case Sensitive (Huruf Besar/Kecil Berpengaruh)</span>
                        </label>

                    </div>
                </div>

                <input type="hidden" name="correct_answer" id="correctAnswer" value="{{ old('correct_answer', 'A') }}">

                <!-- Settings -->
                <div class="form-group">
                    <label class="form-label">⚙️ Pengaturan Soal</label>
                    <div class="settings-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div>
                            <label class="form-label" for="difficulty" style="font-weight: 500; font-size: 0.85rem;">Tingkat Kesulitan</label>
                            <select id="difficulty" name="difficulty" class="form-control" required>
                                <option value="easy" {{ old('difficulty', 'easy') === 'easy' ? 'selected' : '' }}>🟢 Mudah</option>
                                <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }}>🟡 Sedang</option>
                                <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>🔴 Sulit</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="points" style="font-weight: 500; font-size: 0.85rem;">Poin Nilai</label>
                            <input type="number" id="points" name="points" class="form-control" value="{{ old('points', 10) }}" min="1" max="100" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-end gap-2" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Simpan Pertanyaan
                    </button>
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Resize Modal -->
    <div class="image-resize-modal" id="imageResizeModal">
        <div class="resize-modal-content">
            <div class="resize-modal-header">
                <h3 class="resize-modal-title">
                    <i data-feather="image" style="width: 20px; height: 20px; display: inline;"></i>
                    Preview & Resize Gambar
                </h3>
                <button type="button" class="btn btn-ghost btn-icon" onclick="closeResizeModal()">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="resize-preview-container">
                <img id="resizePreviewImage" src="" alt="Preview">
            </div>
            <div class="resize-controls">
                <label class="form-label" style="font-size: 0.9rem;">Ukuran Gambar</label>
                <div class="resize-slider-container">
                    <span style="font-size: 0.8rem; color: var(--gray);">50%</span>
                    <input type="range" class="resize-slider" id="resizeSlider" min="50" max="100" value="100" oninput="updateResizePreview(this.value)">
                    <span style="font-size: 0.8rem; color: var(--gray);">100%</span>
                    <span class="resize-value" id="resizeValue">100%</span>
                </div>
            </div>
            <div class="resize-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeResizeModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmImage()">
                    <i data-feather="check"></i> Gunakan Gambar
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    feather.replace();

    // Topic-Material cascade
    const topicSelect = document.getElementById('topic_select');
    const materialSelect = document.getElementById('material_id');
    const materialOptions = Array.from(materialSelect.options);

    topicSelect.addEventListener('change', function() {
        const selectedTopic = this.value;
        materialSelect.innerHTML = '<option value="">-- Pilih Materi --</option>';

        materialOptions.forEach(opt => {
            if (opt.value === '') return;
            const topicId = opt.dataset.topic;
            if (!selectedTopic || topicId === selectedTopic) {
                materialSelect.appendChild(opt.cloneNode(true));
            }
        });
    });

    // Options management
    const optionKeys = ['A', 'B', 'C', 'D', 'E', 'F'];
    let options = [
        { key: 'A', text: '' },
        { key: 'B', text: '' },
        { key: 'C', text: '' },
        { key: 'D', text: '' }
    ];
    let correctAnswer = 'A';

    const optionsContainer = document.getElementById('optionsContainer');
    const addOptionBtn = document.getElementById('addOptionBtn');
    const correctAnswerInput = document.getElementById('correctAnswer');
    const multipleChoiceSection = document.getElementById('multipleChoiceSection');
    const multipleAnswerSection = document.getElementById('multipleAnswerSection');
    const trueFalseSection = document.getElementById('trueFalseSection');
    const shortAnswerSection = document.getElementById('shortAnswerSection');
    const typeOptions = document.querySelectorAll('.type-option');
    const trueFalseOptions = document.querySelectorAll('.true-false-option');

    // Multiple Answer (PGK) Management
    const multiAnswerOptionsContainer = document.getElementById('multiAnswerOptionsContainer');
    const addMultiAnswerOptionBtn = document.getElementById('addMultiAnswerOptionBtn');
    let multiAnswerOptions = [
        { key: 'A', text: '', isCorrect: false },
        { key: 'B', text: '', isCorrect: false },
        { key: 'C', text: '', isCorrect: false },
        { key: 'D', text: '', isCorrect: false }
    ];

    function renderMultiAnswerOptions() {
        multiAnswerOptionsContainer.innerHTML = multiAnswerOptions.map((opt, index) => `
            <div class="option-item ${opt.isCorrect ? 'correct' : ''}" data-key="${opt.key}">
                <div class="option-key">${opt.key}</div>
                <input type="text" class="option-input multi-answer-text" data-key="${opt.key}" name="multi_options[${index}][text]" placeholder="Tulis opsi ${opt.key}..." value="${opt.text}" ${document.querySelector('input[name=\"question_type\"]:checked')?.value === 'multiple_answer' ? 'required' : ''}>
                <input type="hidden" name="multi_options[${index}][key]" value="${opt.key}">
                <label class="option-correct-btn ${opt.isCorrect ? 'active' : ''}" style="cursor: pointer;">
                    <input type="checkbox" name="correct_answers[]" value="${opt.key}" ${opt.isCorrect ? 'checked' : ''} style="display: none;" onchange="toggleMultiAnswer('${opt.key}', this.checked)">
                    <i data-feather="check"></i> Benar
                </label>
                ${multiAnswerOptions.length > 2 ? `<button type="button" class="option-remove-btn" onclick="removeMultiAnswerOption('${opt.key}')"><i data-feather="x"></i></button>` : ''}
            </div>
        `).join('');
        feather.replace();
        addMultiAnswerOptionBtn.style.display = multiAnswerOptions.length >= 6 ? 'none' : 'flex';

        // Add input listeners to update text in real-time
        document.querySelectorAll('.multi-answer-text').forEach(input => {
            input.addEventListener('input', (e) => {
                const opt = multiAnswerOptions.find(o => o.key === e.target.dataset.key);
                if (opt) opt.text = e.target.value;
            });
        });
    }

    window.toggleMultiAnswer = function(key, isChecked) {
        // Save current text values before re-rendering
        document.querySelectorAll('.multi-answer-text').forEach(input => {
            const opt = multiAnswerOptions.find(o => o.key === input.dataset.key);
            if (opt) opt.text = input.value;
        });

        const opt = multiAnswerOptions.find(o => o.key === key);
        if (opt) opt.isCorrect = isChecked;
        renderMultiAnswerOptions();
    };

    window.removeMultiAnswerOption = function(key) {
        if (multiAnswerOptions.length <= 2) return;
        // Save current text values before re-rendering
        document.querySelectorAll('.multi-answer-text').forEach(input => {
            const opt = multiAnswerOptions.find(o => o.key === input.dataset.key);
            if (opt) opt.text = input.value;
        });
        multiAnswerOptions = multiAnswerOptions.filter(o => o.key !== key).map((o, i) => ({ ...o, key: optionKeys[i] }));
        renderMultiAnswerOptions();
    };

    addMultiAnswerOptionBtn.addEventListener('click', () => {
        if (multiAnswerOptions.length >= 6) return;
        // Save current text values before re-rendering
        document.querySelectorAll('.multi-answer-text').forEach(input => {
            const opt = multiAnswerOptions.find(o => o.key === input.dataset.key);
            if (opt) opt.text = input.value;
        });
        multiAnswerOptions.push({ key: optionKeys[multiAnswerOptions.length], text: '', isCorrect: false });
        renderMultiAnswerOptions();
    });

    function renderOptions() {
        optionsContainer.innerHTML = options.map((opt, index) => `
            <div class="option-item ${opt.key === correctAnswer ? 'correct' : ''}" data-key="${opt.key}">
                <div class="option-key">${opt.key}</div>
                <input type="text" class="option-input pg-option-text" data-key="${opt.key}" name="options[${index}][text]" placeholder="Tulis opsi ${opt.key}..." value="${opt.text}" ${document.querySelector('input[name="question_type"]:checked')?.value === 'multiple_choice' ? 'required' : ''}>
                <input type="hidden" name="options[${index}][key]" value="${opt.key}">
                <button type="button" class="option-correct-btn ${opt.key === correctAnswer ? 'active' : ''}" data-key="${opt.key}">
                    <i data-feather="check"></i> Benar
                </button>
                ${options.length > 2 ? `<button type="button" class="option-remove-btn" data-key="${opt.key}"><i data-feather="x"></i></button>` : ''}
            </div>
        `).join('');

        feather.replace();

        document.querySelectorAll('.option-correct-btn').forEach(btn => {
            btn.addEventListener('click', () => setCorrectAnswer(btn.dataset.key));
        });

        document.querySelectorAll('.option-remove-btn').forEach(btn => {
            btn.addEventListener('click', () => removeOption(btn.dataset.key));
        });

        addOptionBtn.style.display = options.length >= 6 ? 'none' : 'flex';

        // Add input listeners to update text in real-time
        document.querySelectorAll('.pg-option-text').forEach(input => {
            input.addEventListener('input', (e) => {
                const opt = options.find(o => o.key === e.target.dataset.key);
                if (opt) opt.text = e.target.value;
            });
        });
    }

    function saveOptionTexts() {
        document.querySelectorAll('.pg-option-text').forEach(input => {
            const opt = options.find(o => o.key === input.dataset.key);
            if (opt) opt.text = input.value;
        });
    }

    function setCorrectAnswer(key) {
        saveOptionTexts();
        correctAnswer = key;
        correctAnswerInput.value = key;
        renderOptions();
    }

    function addOption() {
        if (options.length >= 6) return;
        saveOptionTexts();
        options.push({ key: optionKeys[options.length], text: '' });
        renderOptions();
    }

    function removeOption(key) {
        if (options.length <= 2) return;
        saveOptionTexts();
        options = options.filter(opt => opt.key !== key).map((opt, index) => ({ key: optionKeys[index], text: opt.text }));
        if (!options.find(opt => opt.key === correctAnswer)) {
            correctAnswer = options[0].key;
            correctAnswerInput.value = correctAnswer;
        }
        renderOptions();
    }

    // Short Answer Logic
    let shortAnswers = [''];
    const shortAnswersContainer = document.getElementById('shortAnswersContainer');
    const addShortAnswerBtn = document.getElementById('addShortAnswerBtn');

    function renderShortAnswers() {
        shortAnswersContainer.innerHTML = shortAnswers.map((ans, index) => `
            <div class="d-flex gap-2">
                <input type="text" name="short_answers[]" class="form-control short-answer-input" placeholder="Jawaban benar ${index + 1}..." value="${ans}" required>
                ${shortAnswers.length > 1 ? `
                <button type="button" class="btn btn-ghost btn-icon" style="color: var(--danger);" onclick="removeShortAnswer(${index})">
                    <i data-feather="trash-2"></i>
                </button>` : ''}
            </div>
        `).join('');
        feather.replace();

        // Update first answer as correct_answer fallback
        const firstInput = document.querySelector('.short-answer-input');
        if (firstInput) {
            firstInput.addEventListener('input', function() {
                correctAnswerInput.value = this.value;
                shortAnswers[0] = this.value;
            });
        }

        // Add listeners to update array on input
        document.querySelectorAll('.short-answer-input').forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                shortAnswers[idx] = e.target.value;
            });
        });
    }

    addShortAnswerBtn.addEventListener('click', () => {
        shortAnswers.push('');
        renderShortAnswers();
    });

    window.removeShortAnswer = function(index) {
        if (shortAnswers.length <= 1) return;
        shortAnswers.splice(index, 1);
        renderShortAnswers();
        correctAnswerInput.value = shortAnswers[0] || '';
    };

    function handleQuestionTypeChange(type) {
        typeOptions.forEach(opt => opt.classList.toggle('selected', opt.dataset.type === type));

        // Hide all sections first
        multipleChoiceSection.style.display = 'none';
        multipleAnswerSection.style.display = 'none';
        trueFalseSection.style.display = 'none';
        shortAnswerSection.style.display = 'none';

        if (type === 'multiple_choice') {
            multipleChoiceSection.style.display = 'block';
            correctAnswerInput.value = correctAnswer;
        } else if (type === 'multiple_answer') {
            multipleAnswerSection.style.display = 'block';
            renderMultiAnswerOptions();
            correctAnswerInput.value = '';
        } else if (type === 'true_false') {
            trueFalseSection.style.display = 'block';
            correctAnswerInput.value = 'T';
            trueFalseOptions.forEach(opt => opt.classList.toggle('selected', opt.dataset.value === 'T'));
        } else if (type === 'short_answer') {
            shortAnswerSection.style.display = 'block';
            if (shortAnswers.length === 0) shortAnswers = [''];
            renderShortAnswers();
            correctAnswerInput.value = shortAnswers[0] || '';
        }

        renderOptions();

        const shortInputs = document.querySelectorAll('.short-answer-input');
        shortInputs.forEach(input => {
            if (type === 'short_answer') {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    // Initialize Short Answers
    renderShortAnswers();

    addOptionBtn.addEventListener('click', addOption);
    // Handle Question Type Selection securely
    document.querySelectorAll('input[name="question_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            handleQuestionTypeChange(this.value);
        });
    });

    // Ensure labels trigger the radio change correctly even with custom styling
    typeOptions.forEach(opt => {
        opt.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT') {
                const radio = opt.querySelector('input');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    // Manually trigger change event since programmatic check doesn't fire it
                    radio.dispatchEvent(new Event('change'));
                }
            }
        });
    });
    trueFalseOptions.forEach(opt => {
        opt.addEventListener('click', () => {
            trueFalseOptions.forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            correctAnswerInput.value = opt.dataset.value;
        });
    });

    renderOptions();
    const checkedType = document.querySelector('input[name="question_type"]:checked');
    if (checkedType) handleQuestionTypeChange(checkedType.value);

    // Image Upload & Resize Functions
    let currentImageData = null;
    let currentScale = 100;

    window.openResizeModal = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                currentImageData = e.target.result;
                document.getElementById('resizePreviewImage').src = currentImageData;
                document.getElementById('resizeSlider').value = 100;
                document.getElementById('resizeValue').textContent = '100%';
                currentScale = 100;
                updateResizePreview(100);
                document.getElementById('imageResizeModal').classList.add('active');
                feather.replace();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    window.openResizeModalEdit = function() {
        const preview = document.getElementById('imagePreview');
        if (preview.src) {
            currentImageData = preview.src;
            document.getElementById('resizePreviewImage').src = currentImageData;
            document.getElementById('resizeSlider').value = currentScale;
            document.getElementById('resizeValue').textContent = currentScale + '%';
            updateResizePreview(currentScale);
            document.getElementById('imageResizeModal').classList.add('active');
            feather.replace();
        }
    };

    window.closeResizeModal = function() {
        document.getElementById('imageResizeModal').classList.remove('active');
        // Reset file input if cancelled without confirming
        if (!document.getElementById('imagePreviewWrapper').style.display ||
            document.getElementById('imagePreviewWrapper').style.display === 'none') {
            document.getElementById('question_image').value = '';
        }
    };

    window.updateResizePreview = function(value) {
        currentScale = parseInt(value);
        document.getElementById('resizeValue').textContent = value + '%';
        const previewImg = document.getElementById('resizePreviewImage');
        previewImg.style.transform = `scale(${value / 100})`;
    };

    window.confirmImage = function() {
        const preview = document.getElementById('imagePreview');
        const wrapper = document.getElementById('imagePreviewWrapper');
        const placeholder = document.getElementById('imageUploadPlaceholder');
        const section = document.getElementById('imageUploadSection');

        preview.src = currentImageData;
        preview.style.transform = `scale(${currentScale / 100})`;
        preview.style.transformOrigin = 'top left';
        document.getElementById('imageScale').value = currentScale;

        wrapper.style.display = 'inline-block';
        placeholder.style.display = 'none';
        section.classList.add('has-image');

        closeResizeModal();
        feather.replace();
    };

    window.removeImage = function() {
        const input = document.getElementById('question_image');
        const wrapper = document.getElementById('imagePreviewWrapper');
        const placeholder = document.getElementById('imageUploadPlaceholder');
        const section = document.getElementById('imageUploadSection');
        const preview = document.getElementById('imagePreview');

        input.value = '';
        preview.src = '';
        preview.style.transform = '';
        wrapper.style.display = 'none';
        placeholder.style.display = 'block';
        section.classList.remove('has-image');
        currentImageData = null;
        currentScale = 100;
    };

    // Initialize Summernote
    $(document).ready(function() {
        $('#question_text').summernote({
            placeholder: 'Tuliskan pertanyaan di sini...',
            tabsize: 2,
            height: 200,
            dialogsInBody: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear', 'superscript', 'subscript']],
                ['color', ['forecolor', 'backcolor']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['view', ['codeview']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    for(let i=0; i < files.length; i++) {
                        uploadImage(files[i]);
                    }
                }
            }
        });

        function uploadImage(file) {
            var data = new FormData();
            data.append("file", file);

            $.ajax({
                url: "{{ route('admin.upload-image') }}",
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#question_text').summernote('insertImage', response.url, function ($image) {
                        $image.css('max-width', '100%');
                        $image.addClass('img-fluid rounded');
                    });
                },
                error: function(data) {
                    console.error(data);
                    alert('Gagal upload gambar. Pastikan file adalah gambar valid (JPG, PNG, GIF) max 2MB.');
                }
            });
        }
    });
</script>
@endpush

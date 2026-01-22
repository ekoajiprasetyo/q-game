@extends('layouts.admin')

@section('title', 'Bank Soal')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="page-title-icon">
                    <i data-feather="help-circle"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; line-height: 1.2; color: var(--dark);">Bank Soal</h1>
                    <p class="page-subtitle" style="margin: 4px 0 0 0; line-height: 1.2;">Kelola semua pertanyaan untuk game tarik tambang</p>
                </div>
            </div>
            <a href="{{ route('admin.questions.create', ['topic_id' => request('topic_id'), 'material_id' => request('material_id')]) }}" class="btn btn-primary">
                <i data-feather="plus"></i>
                Tambah Soal
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body" style="padding: 1rem 1.5rem;">
            <form action="{{ route('admin.questions.index') }}" method="GET" class="d-flex gap-3 flex-wrap align-center">
                <div style="position:relative;">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari pertanyaan..."
                        value="{{ request('search') }}"
                        style="max-width: 220px; padding-left: 35px;"
                    >
                    <i data-feather="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; color:#94A3B8;"></i>
                </div>
                <select name="topic_id" class="form-control" style="max-width: 160px;" id="topicFilter">
                    <option value="">Semua Topik</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}" {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                            {{ $topic->icon ?? '📚' }} {{ $topic->name }}
                        </option>
                    @endforeach
                </select>
                <select name="material_id" class="form-control" style="max-width: 180px;" id="materialFilter">
                    <option value="">Semua Materi</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" data-topic="{{ $material->topic_id }}" {{ request('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->icon ?? '📄' }} {{ $material->name }}
                        </option>
                    @endforeach
                </select>
                <select name="difficulty" class="form-control" style="max-width: 120px;">
                    <option value="">Semua Level</option>
                    <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>🟢 Mudah</option>
                    <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>🟡 Sedang</option>
                    <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>🔴 Sulit</option>
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i data-feather="filter"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'topic_id', 'material_id', 'difficulty', 'question_type']))
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-ghost">
                        <i data-feather="x"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Questions List -->
    @if($questions->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding: 4rem 2rem;">
                <div class="empty-state-icon">📝</div>
                <div class="empty-state-title">Belum ada pertanyaan</div>
                <div class="empty-state-text">Mulai dengan membuat pertanyaan baru untuk bank soal</div>
                <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                    <i data-feather="plus"></i>
                    Buat Pertanyaan Pertama
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" style="cursor: pointer;">
                                </th>
                                <th>Pertanyaan</th>
                                <th style="width: 250px;">Materi</th>
                                <th style="width: 150px;">Tipe</th>
                                <th style="width: 80px;">Level</th>
                                <th style="width: 60px;">Poin</th>
                                <th style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="question-checkbox" value="{{ $question->id }}" style="cursor: pointer;">
                                    </td>
                                    <td>
                                        <div class="q-content-preview">
                                            {!! $question->question_text !!}
                                        </div>
                                        <div class="d-flex gap-2 align-center">
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                ✓ {{ $question->correct_answer }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($question->material)
                                            <div class="d-flex align-center gap-2">
                                                <span style="font-size: 1rem;">{{ $question->material->icon ?? '📄' }}</span>
                                                <div>
                                                    <div style="font-size: 0.85rem; font-weight: 500;">{{ Str::limit($question->material->name, 15) }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $question->material->topic?->name ?? '-' }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($question->question_type === 'multiple_choice')
                                            <span class="badge badge-purple">PG</span>
                                        @elseif($question->question_type === 'multiple_answer')
                                            <span class="badge badge-teal">PGK</span>
                                        @elseif($question->question_type === 'true_false')
                                            <span class="badge badge-blue">B/S</span>
                                        @elseif($question->question_type === 'short_answer')
                                            <span class="badge badge-pink">Isian Singkat</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($question->difficulty === 'easy')
                                            <span class="badge badge-green">Mudah</span>
                                        @elseif($question->difficulty === 'medium')
                                            <span class="badge badge-yellow">Sedang</span>
                                        @else
                                            <span class="badge badge-red">Sulit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $question->points }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-ghost btn-icon" title="Preview" 
                                                onclick="openPreviewModal(@js($question))">
                                                <i data-feather="eye"></i>
                                            </button>
                                            <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-ghost btn-icon" title="Edit">
                                                <i data-feather="edit-2"></i>
                                            </a>
                                            <button type="button" class="btn btn-ghost btn-icon" title="Hapus" style="color: var(--danger);" onclick="confirmDelete('{{ route('admin.questions.destroy', $question) }}')">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Stats -->
                <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--cream); display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
                    <div style="font-size: 0.9rem; color: var(--gray);">
                        Total Soal: <strong style="color: var(--dark);">{{ $questions->total() }}</strong>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--gray);">
                        Total Poin: <strong style="color: var(--primary);">{{ $totalPoints }}</strong>
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div id="bulkActions" style="display: none; padding: 1rem 1.5rem; background: var(--cream); border-top: 2px solid var(--cream-dark);">
                    <div class="d-flex align-center gap-3">
                        <span id="selectedCount" style="font-weight: 600; color: var(--dark);">0 item dipilih</span>
                        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="padding: 0.4rem 1rem; font-size: 0.8rem;">
                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
            
            @if($questions->hasPages())
                <div class="pagination-container">
                    {{ $questions->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    @endif

    <!-- Preview Modal -->
    <div class="modal-overlay" id="previewModal">
        <div class="modal" style="max-width: 600px; display: flex; flex-direction: column; max-height: 85vh;">
            <div class="modal-header" style="flex-shrink: 0; border-bottom: 1px solid var(--cream-dark);">
                <h3 class="modal-title">
                    <i data-feather="eye"></i>
                    Preview Pertanyaan
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('previewModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                <div style="margin-bottom: 1.5rem;">
                    <span class="badge badge-purple" id="previewType"></span>
                    <span class="badge badge-yellow" id="previewDifficulty"></span>
                    <span class="badge badge-green" id="previewPoints"></span>
                </div>

                <div class="question-preview-card" style="background: var(--cream); border: 2px solid var(--cream-dark); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                    <div class="q-modal-text" id="previewText"></div>
                    
                    <!-- Options Container -->
                    <div id="previewOptions" class="d-grid gap-2"></div>
                </div>

                <div style="border-top: 1px solid var(--gray-light); padding-top: 1.5rem;">
                    <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--gray); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Jawaban Benar</h4>
                    <div style="font-size: 1rem; font-weight: 700; color: var(--primary);" id="previewCorrectAnswer"></div>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid var(--cream-dark);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('previewModal')">Tutup</button>
            </div>
        </div>
    </div>
    
    <style>
        /* Table Header Solid Orange (Match Button Tone) */
        .q-content-preview {
            font-size: 0.95rem;
            line-height: 1.05; /* Jarak baris sangat rapat */
            max-height: 120px;
            overflow: hidden;
            position: relative;
            margin-bottom: 0.25rem;
            color: #334155;
            font-weight: normal;
        }
        
        .q-content-preview p {
            margin: 0 !important; /* Hapus jarak antar paragraf */
            padding: 0 !important;
        }

        .q-content-preview ul, .q-content-preview ol {
            margin: 0 0 0.25rem 1.2rem !important;
            padding: 0 !important;
        }
        
        .q-content-preview img {
            max-height: 40px;
            width: auto;
            border-radius: 4px;
            vertical-align: middle;
            display: block; /* Agar gambar punya baris sendiri jika besar, atau inline jika kecil? Block lebih aman untuk layout tabel */
            margin: 2px 0;
            border: 1px solid #e2e8f0;
        }

        /* Modal Preview Text Style */
        .q-modal-text {
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark); /* fallback */
            color: #1e293b; 
            margin-bottom: 1.5rem;
            line-height: 1.05;
        }
        .q-modal-text p { margin: 0 !important; }
        .q-modal-text p:empty, .q-modal-text br { display: block; content: ' '; min-height: 1em; } 
        .q-modal-text img { max-width: 100%; height: auto; border-radius: 8px; margin: 0.5rem 0; }

        .table thead th {
            background-color: #E58B45 !important; /* Menggunakan Primary Dark agar solid & kontras */
            color: #FFFFFF !important;
            border-bottom: none !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        /* Pagination Container Reset */
        .pagination-container {
            margin-top: 2rem;
            padding-bottom: 2rem;
            display: flex;
            justify-content: center;
        }

        /* Bootstrap Pagination Overrides */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .page-item {
            margin: 0;
            padding: 0;
        }

        .page-item .page-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            margin-left: 0;
            line-height: 1;
            color: #64748B;
            background-color: #fff;
            border: none;
            border-radius: 12px !important;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        
        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            border-radius: 12px !important;
        }
        
        .page-item:not(.active):not(.disabled) .page-link:hover {
            color: #E58B45;
            background-color: #FFF7ED;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(229, 139, 69, 0.15);
            z-index: 2;
        }

        .page-item.active .page-link {
            z-index: 3;
            color: #fff !important;
            background-color: #E58B45 !important; /* Match Header */
            box-shadow: 0 4px 10px rgba(229, 139, 69, 0.4);
            transform: scale(1.05);
        }

        .page-item.disabled .page-link {
            color: #CBD5E1;
            background-color: transparent;
            box-shadow: none;
            cursor: default;
        }
        
        .page-link svg {
            width: 16px;
            height: 16px;
        }
    </style>
@endsection

@push('scripts')
<script>
    feather.replace();

    // Topic-Material filter cascade
    const topicFilter = document.getElementById('topicFilter');
    const materialFilter = document.getElementById('materialFilter');
    const materialOptions = Array.from(materialFilter.options);

    topicFilter.addEventListener('change', function() {
        const selectedTopic = this.value;
        
        // Reset material filter
        materialFilter.innerHTML = '<option value="">Semua Materi</option>';
        
        materialOptions.forEach(opt => {
            if (opt.value === '') return;
            const topicId = opt.dataset.topic;
            if (!selectedTopic || topicId === selectedTopic) {
                materialFilter.appendChild(opt.cloneNode(true));
            }
        });
    });

    // Select all functionality
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.question-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    function updateBulkActions() {
        const checked = document.querySelectorAll('.question-checkbox:checked');
        if (checked.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = checked.length + ' item dipilih';
        } else {
            bulkActions.style.display = 'none';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.question-checkbox:checked');
            const items = Array.from(checked);
            
            if (items.length === 0) return;

            document.getElementById('bulkDeleteCount').textContent = items.length;
            openModal('confirmBulkDeleteModal');

            // Handle actual deletion
            document.getElementById('confirmBulkDeleteBtn').onclick = async function() {
                const ids = items.map(cb => cb.value);
                closeModal('confirmBulkDeleteModal');
                
                try {
                    const response = await fetch('{{ route("admin.questions.bulk-destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids })
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Gagal menghapus pertanyaan');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan');
                    console.error(error);
                }
            };
        });
    }
    function openPreviewModal(question) {
        document.getElementById('previewText').innerHTML = question.question_text;
        document.getElementById('previewPoints').textContent = question.points + ' Poin';
        
        // Remove any existing image first to prevent duplicates
        const existingImages = document.querySelectorAll('#previewModal .preview-question-image');
        existingImages.forEach(img => img.remove());
        
        // Handle question image - add above question text
        if (question.image_url) {
            const imgDiv = document.createElement('div');
            imgDiv.className = 'preview-question-image';
            imgDiv.style.marginBottom = '1rem';
            imgDiv.innerHTML = `<img src="${question.image_url}" alt="Question Image" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid var(--light-gray);">`;
            document.getElementById('previewText').insertAdjacentElement('beforebegin', imgDiv);
        }
        
        // Type Badge
        const typeBadge = document.getElementById('previewType');
        if (question.question_type === 'multiple_choice') {
            typeBadge.textContent = 'Pilihan Ganda';
            typeBadge.className = 'badge badge-purple';
        } else if (question.question_type === 'multiple_answer') {
            typeBadge.textContent = 'PG Kompleks';
            typeBadge.className = 'badge badge-teal';
        } else if (question.question_type === 'true_false') {
            typeBadge.textContent = 'Benar / Salah';
            typeBadge.className = 'badge badge-blue';
        } else {
            typeBadge.textContent = 'Isian Singkat';
            typeBadge.className = 'badge badge-pink';
        }

        // Difficulty Badge
        const diffBadge = document.getElementById('previewDifficulty');
        if (question.difficulty === 'easy') {
            diffBadge.textContent = 'Mudah';
            diffBadge.className = 'badge badge-green';
        } else if (question.difficulty === 'medium') {
            diffBadge.textContent = 'Sedang';
            diffBadge.className = 'badge badge-yellow';
        } else {
            diffBadge.textContent = 'Sulit';
            diffBadge.className = 'badge badge-red';
        }

        // Options or Answer Display
        const optionsContainer = document.getElementById('previewOptions');
        optionsContainer.innerHTML = '';

        if (question.question_type === 'multiple_choice' && question.options) {
            question.options.forEach(opt => {
                const isCorrect = opt.key === question.correct_answer;
                const div = document.createElement('div');
                div.className = 'd-flex align-center gap-2';
                div.style.padding = '0.75rem 1rem';
                div.style.background = isCorrect ? '#dcfce7' : 'white';
                div.style.border = isCorrect ? '1px solid #22c55e' : '1px solid var(--gray-light)';
                div.style.borderRadius = '8px';
                
                div.innerHTML = `
                    <div style="font-weight: 700; width: 24px;">${opt.key}.</div>
                    <div style="${isCorrect ? 'color: #15803d; font-weight: 600;' : ''}">${opt.text}</div>
                    ${isCorrect ? '<i data-feather="check-circle" style="width: 16px; height: 16px; margin-left: auto; color: #15803d;"></i>' : ''}
                `;
                optionsContainer.appendChild(div);
            });
        } else if (question.question_type === 'true_false') {
            ['T', 'F'].forEach(key => {
                const isCorrect = key === question.correct_answer;
                const text = key === 'T' ? 'Benar' : 'Salah';
                const div = document.createElement('div');
                div.className = 'd-flex align-center gap-2';
                div.style.padding = '0.75rem 1rem';
                div.style.background = isCorrect ? '#dcfce7' : 'white';
                div.style.border = isCorrect ? '1px solid #22c55e' : '1px solid var(--gray-light)';
                div.style.borderRadius = '8px';
                div.innerHTML = `
                    <div style="${isCorrect ? 'color: #15803d; font-weight: 600;' : ''}">${text}</div>
                    ${isCorrect ? '<i data-feather="check-circle" style="width: 16px; height: 16px; margin-left: auto; color: #15803d;"></i>' : ''}
                `;
                optionsContainer.appendChild(div);
            });
        } else if (question.question_type === 'multiple_answer' && question.options && question.options.choices) {
            const correctAnswers = question.options.correct_answers || [];
            question.options.choices.forEach(opt => {
                const isCorrect = correctAnswers.includes(opt.key);
                const div = document.createElement('div');
                div.className = 'd-flex align-center gap-2';
                div.style.padding = '0.75rem 1rem';
                div.style.background = isCorrect ? '#dcfce7' : 'white';
                div.style.border = isCorrect ? '1px solid #22c55e' : '1px solid var(--gray-light)';
                div.style.borderRadius = '8px';
                
                div.innerHTML = `
                    <div style="font-weight: 700; width: 24px;">${opt.key}.</div>
                    <div style="${isCorrect ? 'color: #15803d; font-weight: 600;' : ''}">${opt.text}</div>
                    ${isCorrect ? '<i data-feather="check-circle" style="width: 16px; height: 16px; margin-left: auto; color: #15803d;"></i>' : ''}
                `;
                optionsContainer.appendChild(div);
            });
        } else if (question.question_type === 'short_answer') {
            // Short Answer
            const div = document.createElement('div');
            div.style.padding = '1rem';
            div.style.background = 'white';
            div.style.border = '1px solid var(--gray-light)';
            div.style.borderRadius = '8px';
            div.style.color = 'var(--gray)';
            div.style.fontStyle = 'italic';
            div.textContent = '(Siswa mengetik jawaban melalui keyboard virtual)';
            optionsContainer.appendChild(div);
        }

        // Correct Answer Text
        const answerText = document.getElementById('previewCorrectAnswer');
        if (question.question_type === 'true_false') {
            answerText.textContent = question.correct_answer === 'T' ? 'Benar' : 'Salah';
        } else if (question.question_type === 'short_answer' && question.options && question.options.answers) {
            // Show all valid answers for short answer
            const answers = question.options.answers;
            const caseSensitive = question.options.case_sensitive;
            
            let html = '<div class="d-flex flex-wrap gap-2" style="margin-bottom: 0.5rem;">';
            answers.forEach(ans => {
                html += `<span style="padding: 0.375rem 0.75rem; background: #dcfce7; color: #15803d; border-radius: 6px; font-weight: 600;">${ans}</span>`;
            });
            html += '</div>';
            
            if (caseSensitive) {
                html += '<div style="font-size: 0.8rem; color: var(--gray); margin-top: 0.5rem;"><i data-feather="alert-circle" style="width: 14px; height: 14px; display: inline;"></i> Case Sensitive aktif</div>';
            }
            
            answerText.innerHTML = html;
        } else if (question.question_type === 'multiple_answer' && question.options && question.options.correct_answers) {
            // Show all correct answers for multiple answer
            const correctAnswers = question.options.correct_answers;
            
            let html = '<div class="d-flex flex-wrap gap-2">';
            correctAnswers.forEach(key => {
                html += `<span style="padding: 0.375rem 0.75rem; background: #dcfce7; color: #15803d; border-radius: 6px; font-weight: 600;">${key}</span>`;
            });
            html += '</div>';
            
            answerText.innerHTML = html;
        } else {
            answerText.textContent = question.correct_answer;
        }

        feather.replace();
        
        // Render KaTeX math formulas in preview
        if (typeof renderKaTeX === 'function') {
            renderKaTeX(document.getElementById('previewModal'));
        }
        
        openModal('previewModal');
    }
</script>
@endpush

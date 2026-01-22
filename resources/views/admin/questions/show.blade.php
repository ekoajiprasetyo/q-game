@extends('layouts.admin')

@section('title', 'Preview Pertanyaan')

@push('styles')
<style>
    .preview-card {
        background: linear-gradient(135deg, var(--dark), #1a1d2e);
        border-radius: var(--radius-xl);
        padding: 2.5rem;
        color: white;
        max-width: 700px;
        margin: 0 auto;
        box-shadow: var(--shadow-strong);
    }

    .preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .preview-material {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .preview-badges {
        display: flex;
        gap: 0.5rem;
    }

    .preview-question {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.5;
        margin-bottom: 2rem;
    }

    .preview-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .preview-option {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg);
        border: 2px solid transparent;
        transition: var(--transition);
    }

    .preview-option:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    .preview-option.correct {
        border-color: var(--accent-green);
        background: rgba(125, 206, 160, 0.2);
    }

    .preview-option-key {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--primary), var(--accent-yellow));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .preview-option.correct .preview-option-key {
        background: linear-gradient(135deg, var(--accent-green), #5AB890);
    }

    .preview-option-text {
        font-size: 1rem;
        font-weight: 500;
    }

    .preview-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }

    .preview-stat {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .preview-stat svg {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 600px) {
        .preview-options {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <h1 class="page-title">
                    <div class="page-title-icon">👁️</div>
                    Preview Pertanyaan
                </h1>
                <p class="page-subtitle">Lihat tampilan pertanyaan seperti di game</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-secondary">
                    <i data-feather="edit-2"></i>
                    Edit
                </a>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
                    <i data-feather="arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Preview Card -->
    <div class="preview-card">
        <div class="preview-header">
            <div class="preview-material">
                @if($question->material)
                    <span>{{ $question->material->icon ?? '📄' }}</span>
                    <span>{{ $question->material->name }}</span>
                    <span style="opacity: 0.5;">•</span>
                    <span style="opacity: 0.7;">{{ $question->material->topic?->name ?? '-' }}</span>
                @else
                    <span>📚</span>
                    <span>Tanpa materi</span>
                @endif
            </div>
            <div class="preview-badges">
                @if($question->question_type === 'multiple_choice')
                    <span class="badge badge-purple">Pilihan Ganda</span>
                @else
                    <span class="badge badge-yellow">B/S</span>
                @endif

                @if($question->difficulty === 'easy')
                    <span class="badge badge-green">Mudah</span>
                @elseif($question->difficulty === 'medium')
                    <span class="badge badge-yellow">Sedang</span>
                @else
                    <span class="badge badge-red">Sulit</span>
                @endif
            </div>
        </div>

        <div class="preview-question">
            {{ $question->question_text }}
        </div>

        <div class="preview-options">
            @foreach($question->options as $option)
                <div class="preview-option {{ $option['key'] === $question->correct_answer ? 'correct' : '' }}">
                    <div class="preview-option-key">{{ $option['key'] }}</div>
                    <div class="preview-option-text">{{ $option['text'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="preview-footer">
            <div class="preview-stat">
                <i data-feather="award"></i>
                <span>{{ $question->points }} poin</span>
            </div>
            <div class="preview-stat">
                <i data-feather="clock"></i>
                <span>{{ $question->time_limit }} detik</span>
            </div>
            <div class="preview-stat">
                <i data-feather="check-circle"></i>
                <span>Jawaban: {{ $question->correct_answer }}</span>
            </div>
        </div>
    </div>

    <!-- Details Card -->
    <div class="card mt-4" style="max-width: 700px; margin-left: auto; margin-right: auto;">
        <div class="card-header">
            <h3 class="card-title">
                <div class="card-title-icon" style="background: linear-gradient(135deg, var(--team-blue), #4DA6FF);">📋</div>
                Detail Pertanyaan
            </h3>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.75rem 0; width: 150px; color: var(--gray);">ID</td>
                    <td style="padding: 0.75rem 0; font-weight: 600;">#{{ $question->id }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; color: var(--gray);">Materi</td>
                    <td style="padding: 0.75rem 0;">{{ $question->material?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; color: var(--gray);">Topik</td>
                    <td style="padding: 0.75rem 0;">{{ $question->material?->topic?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; color: var(--gray);">Tipe</td>
                    <td style="padding: 0.75rem 0;">{{ $question->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Benar/Salah' }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; color: var(--gray);">Dibuat</td>
                    <td style="padding: 0.75rem 0;">{{ $question->created_at->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; color: var(--gray);">Diperbarui</td>
                    <td style="padding: 0.75rem 0;">{{ $question->updated_at->format('d M Y, H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    feather.replace();
</script>
@endpush

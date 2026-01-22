@extends('layouts.admin')

@section('title', 'Detail Game')

@push('styles')
<style>
    .game-header {
        background: linear-gradient(135deg, var(--dark), #1a1d2e);
        border-radius: var(--radius-xl);
        padding: 2.5rem;
        color: white;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-strong);
    }

    .game-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .game-title {
        font-size: 1.5rem;
        font-weight: 800;
    }

    .game-meta {
        display: flex;
        gap: 1.5rem;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        flex-wrap: wrap;
    }

    .game-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .game-meta-item svg {
        width: 16px;
        height: 16px;
    }

    .scoreboard {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 2rem;
        align-items: center;
        text-align: center;
    }

    .team-score {
        padding: 2rem;
        border-radius: var(--radius-xl);
        position: relative;
    }

    .team-score.blue {
        background: linear-gradient(135deg, rgba(110, 198, 255, 0.2), rgba(110, 198, 255, 0.1));
        border: 2px solid var(--team-blue);
    }

    .team-score.red {
        background: linear-gradient(135deg, rgba(255, 138, 138, 0.2), rgba(255, 138, 138, 0.1));
        border: 2px solid var(--team-red);
    }

    .team-score.winner::after {
        content: '🏆';
        position: absolute;
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 2rem;
    }

    .team-name {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .team-points {
        font-size: 4rem;
        font-weight: 900;
        line-height: 1;
    }

    .team-score.blue .team-points { color: var(--team-blue); }
    .team-score.red .team-points { color: var(--team-red); }

    .vs-divider {
        font-size: 1.5rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.3);
    }

    .round-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        border-bottom: 2px solid var(--cream);
        transition: var(--transition);
    }

    .round-item:last-child {
        border-bottom: none;
    }

    .round-item:hover {
        background: var(--cream);
    }

    .round-number {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary), var(--accent-yellow));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .round-question {
        flex: 1;
    }

    .round-question-text {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .round-question-answer {
        font-size: 0.8rem;
        color: var(--gray);
    }

    .round-answers {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .round-answer {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.9rem;
        font-weight: 600;
    }

    .round-answer.blue {
        background: rgba(110, 198, 255, 0.15);
        color: var(--team-blue);
    }

    .round-answer.blue.correct {
        background: var(--team-blue);
        color: white;
    }

    .round-answer.red {
        background: rgba(255, 138, 138, 0.15);
        color: var(--team-red);
    }

    .round-answer.red.correct {
        background: var(--team-red);
        color: white;
    }

    .round-winner {
        font-size: 1.5rem;
    }

    @media (max-width: 768px) {
        .scoreboard {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .vs-divider {
            display: none;
        }

        .team-points {
            font-size: 3rem;
        }
    }
</style>
@endpush

@section('content')
    <!-- Game Header with Scoreboard -->
    <div class="game-header">
        <div class="game-header-top">
            <div>
                <h1 class="game-title">{{ $session->title ?? 'Game #'.$session->id }}</h1>
                <div class="game-meta">
                    <div class="game-meta-item">
                        <i data-feather="folder"></i>
                        <span>{{ $session->topic?->name ?? 'Tanpa topik' }}</span>
                    </div>
                    <div class="game-meta-item">
                        <i data-feather="calendar"></i>
                        <span>{{ $session->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="game-meta-item">
                        <i data-feather="help-circle"></i>
                        <span>{{ $session->total_questions }} soal</span>
                    </div>
                    @if($session->game_mode === 'race')
                        <span class="badge badge-orange">Race Mode</span>
                    @else
                        <span class="badge badge-purple">Turn Based</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
                <i data-feather="arrow-left"></i>
                Kembali
            </a>
        </div>

        <div class="scoreboard">
            <div class="team-score blue {{ $session->winner_team === 'blue' ? 'winner' : '' }}">
                <div class="team-name">{{ $session->team_blue_name }}</div>
                <div class="team-points">{{ $session->team_blue_score }}</div>
            </div>
            <div class="vs-divider">VS</div>
            <div class="team-score red {{ $session->winner_team === 'red' ? 'winner' : '' }}">
                <div class="team-name">{{ $session->team_red_name }}</div>
                <div class="team-points">{{ $session->team_red_score }}</div>
            </div>
        </div>
    </div>

    <!-- Rounds Detail -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <div class="card-title-icon" style="background: linear-gradient(135deg, var(--accent-purple), #9B5DE5);">📋</div>
                Detail Ronde
            </h3>
            <span class="badge badge-purple">{{ $session->rounds->count() }} ronde</span>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($session->rounds->isEmpty())
                <div class="empty-state" style="padding: 3rem 2rem;">
                    <div class="empty-state-icon">📝</div>
                    <div class="empty-state-title">Tidak ada data ronde</div>
                    <div class="empty-state-text">Game ini belum memiliki data ronde yang tercatat</div>
                </div>
            @else
                @foreach($session->rounds->sortBy('round_number') as $round)
                    <div class="round-item">
                        <div class="round-number">{{ $round->round_number }}</div>
                        <div class="round-question">
                            <div class="round-question-text">
                                {{ Str::limit($round->question?->question_text ?? 'Pertanyaan tidak tersedia', 70) }}
                            </div>
                            <div class="round-question-answer">
                                Jawaban benar: <strong>{{ $round->question?->correct_answer ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="round-answers">
                            <div class="round-answer blue {{ $round->team_blue_correct ? 'correct' : '' }}">
                                {{ $round->team_blue_answer ?? '-' }}
                                @if($round->team_blue_time_ms)
                                    <span style="opacity: 0.7; font-size: 0.8rem;">({{ number_format($round->team_blue_time_ms / 1000, 1) }}s)</span>
                                @endif
                            </div>
                            <div class="round-answer red {{ $round->team_red_correct ? 'correct' : '' }}">
                                {{ $round->team_red_answer ?? '-' }}
                                @if($round->team_red_time_ms)
                                    <span style="opacity: 0.7; font-size: 0.8rem;">({{ number_format($round->team_red_time_ms / 1000, 1) }}s)</span>
                                @endif
                            </div>
                        </div>
                        <div class="round-winner">
                            @if($round->winner_team === 'blue')
                                🔵
                            @elseif($round->winner_team === 'red')
                                🔴
                            @elseif($round->winner_team === 'draw')
                                🤝
                            @else
                                ⭕
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    feather.replace();
</script>
@endpush

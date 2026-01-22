@extends('layouts.admin')

@section('title', $tournament->title)

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div style="flex: 1;">
            <div class="page-title">
                <div class="page-title-icon">
                    <i data-feather="award"></i>
                </div>
                <div>
                    <span>{{ $tournament->title }}</span>
                    <div class="page-subtitle">
                        @if($tournament->status == 'setup') <span class="badge badge-yellow">Persiapan</span>
                        @elseif($tournament->status == 'active') <span class="badge badge-green">Berlangsung</span>
                        @else <span class="badge badge-blue">Selesai</span> @endif
                        &nbsp; • &nbsp; {{ $tournament->description ?? 'Kelola pertandingan turnamen' }}
                    </div>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.tournaments.index') }}" class="btn btn-secondary">
                <i data-feather="arrow-left"></i> Kembali
            </a>
            @if(!$tournament->matches->whereNotNull('game_session_id')->count())
            <button type="button" class="btn btn-primary" onclick="openModal('reshuffleModal')">
                <i data-feather="shuffle"></i> Acak Bagan
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Tournament PIN Display -->
<div class="pin-card">
    <div class="pin-card-body">
        <div class="pin-label">
            <i data-feather="key" style="width: 16px; height: 16px; vertical-align: middle;"></i>
            PIN Akses Turnamen
        </div>
        <div class="pin-value">{{ $tournament->pin }}</div>
        <div class="pin-hint">Masukkan PIN ini di halaman game setup untuk mengakses bracket publik</div>
        <button id="copyPinBtn" onclick="copyPin('{{ $tournament->pin }}')" class="btn btn-sm btn-copy">
            <span id="copyPinContent"><i data-feather="copy"></i> Salin PIN</span>
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <i data-feather="users"></i>
        <span>{{ $tournament->teams->count() }} Tim</span>
    </div>
    <div class="stat-card">
        <i data-feather="target"></i>
        <span>{{ $tournament->matches->count() }} Match</span>
    </div>
    <div class="stat-card">
        <i data-feather="check-circle"></i>
        <span>{{ $tournament->matches->whereNotNull('winner_team_id')->count() }} Selesai</span>
    </div>
</div>

<!-- Bracket View -->
<div class="bracket-scroll-container">
    <div class="bracket-wrapper" id="bracketWrapper">
        @php
            $totalRounds = $matchesByRound->keys()->max();
        @endphp
        
        @foreach($matchesByRound as $round => $matches)
            @php
                if ($round == $totalRounds) {
                    $roundClass = 'final';
                    $roundLabel = '🏆 FINAL';
                } elseif ($round == $totalRounds - 1 && $totalRounds > 2) {
                    $roundClass = 'semi';
                    $roundLabel = 'SEMI FINAL';
                } elseif ($round == 2) {
                    $roundClass = 'round-2';
                    $roundLabel = 'ROUND 2';
                } else {
                    $roundClass = 'round-1';
                    $roundLabel = 'ROUND ' . $round;
                }
            @endphp
            
            <div class="bracket-round" data-round="{{ $round }}">
                <div class="round-title {{ $roundClass }}">{{ $roundLabel }}</div>
                
                <div class="round-matches" data-match-count="{{ $matches->count() }}">
                    @foreach($matches as $mIndex => $match)
                        @php
                            $isCompleted = $match->winner_team_id != null;
                            $team1Number = '?';
                            if($match->team1) {
                                $nums = preg_replace('/[^0-9]/', '', $match->team1->name);
                                $team1Number = $nums ? $nums : strtoupper(substr($match->team1->name, 0, 1));
                            }
                            
                            $team2Number = '?';
                            if($match->team2) {
                                $nums = preg_replace('/[^0-9]/', '', $match->team2->name);
                                $team2Number = $nums ? $nums : strtoupper(substr($match->team2->name, 0, 1));
                            }
                            $durationSecs = $match->time_per_question ?? 0;
                            $durationMins = floor($durationSecs / 60);
                            $durationSecRemainder = $durationSecs % 60;
                            $durationFormatted = sprintf('%02d:%02d', $durationMins, $durationSecRemainder);
                        @endphp
                        
                        <div class="match-wrapper">
                            <div class="match-card {{ $isCompleted ? 'completed' : '' }} {{ $match->is_ready ? 'ready' : '' }}">
                                
                                <div class="match-header {{ $roundClass }}">
                                    <span>Match #{{ $match->match_number }}</span>
                                    @if($isCompleted)
                                        <i data-feather="check-circle" width="14"></i>
                                    @elseif($match->is_ready)
                                        <i data-feather="zap" width="14"></i>
                                    @endif
                                </div>
                                
                                <div class="match-body">
                                    <!-- Match Info: Material & Duration -->
                                    @if($match->is_ready && $match->material)
                                        <div class="match-info">
                                            <span class="match-info-badge">
                                                <i data-feather="book-open"></i>
                                                {{ Str::limit($match->material->name, 18) }}
                                            </span>
                                            <span class="match-info-badge">
                                                <i data-feather="clock"></i>
                                                {{ $durationFormatted }}
                                            </span>
                                            <span class="match-info-badge">
                                                <i data-feather="help-circle"></i>
                                                {{ $match->total_questions }} soal
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <!-- Team 1 -->
                                    <div class="match-team {{ $match->winner_team_id == $match->team_1_id ? 'winner' : '' }} {{ $match->winner_team_id && $match->winner_team_id != $match->team_1_id ? 'loser' : '' }} {{ !$match->team_1_id ? 'tbd' : '' }}">
                                        <div class="team-name">
                                            <div class="team-number">{{ $team1Number ?: '?' }}</div>
                                            <span>{{ $match->team1->name ?? 'Menunggu...' }}</span>
                                        </div>
                                        @if($match->gameSession)
                                            <span class="team-score">{{ $match->gameSession->team_red_score }}</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Team 2 -->
                                    <div class="match-team {{ $match->winner_team_id == $match->team_2_id ? 'winner' : '' }} {{ $match->winner_team_id && $match->winner_team_id != $match->team_2_id ? 'loser' : '' }} {{ !$match->team_2_id ? 'tbd' : '' }}">
                                        <div class="team-name">
                                            <div class="team-number">{{ $team2Number ?: '?' }}</div>
                                            <span>{{ $match->team2->name ?? 'Menunggu...' }}</span>
                                        </div>
                                        @if($match->gameSession)
                                            <span class="team-score">{{ $match->gameSession->team_blue_score }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Action -->
                                <div class="match-action">
                                    @if($round == 1 && (!$match->team_1_id || !$match->team_2_id))
                                        <span style="font-size: 0.75rem; font-style: italic; color: #94a3b8;">
                                            Lolos Otomatis (Bye)
                                        </span>
                                    @elseif($isCompleted)
                                        <span class="badge badge-green">
                                            <i data-feather="check" width="12"></i>
                                            Selesai
                                        </span>
                                    @else
                                        <!-- Logic: Konfigurasi always available until finished -->
                                        @if($match->team_1_id && $match->team_2_id && $match->is_ready)
                                             <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openConfigModal({{ $match->id }}, {{ $match->topic_id ?? 'null' }}, {{ $match->material_id ?? 'null' }}, {{ $match->total_questions ?? 'null' }}, {{ $match->time_per_question ?? 'null' }})" title="Ubah Konfigurasi">
                                                    <i data-feather="settings"></i>
                                                </button>
                                                <span class="badge badge-blue">
                                                    <i data-feather="zap" width="12"></i>
                                                    Siap
                                                </span>
                                             </div>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary" onclick="openConfigModal({{ $match->id }}, {{ $match->topic_id ?? 'null' }}, {{ $match->material_id ?? 'null' }}, {{ $match->total_questions ?? 'null' }}, {{ (!$match->is_ready && $match->time_per_question == 30) ? 'null' : ($match->time_per_question ?? 'null') }})">
                                                <i data-feather="settings"></i> Konfigurasi
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        
        <!-- Winner Box -->
        @if($tournament->status == 'completed')
        <div class="bracket-round">
            <div class="round-title champion">🏅 CHAMPION</div>
            <div class="round-matches">
                <div class="match-wrapper">
                    <div class="match-card winner-card">
                        <div class="trophy">🏆</div>
                        <div class="winner-name">{{ $tournament->matches->sortByDesc('round')->first()->winner->name ?? 'TBD' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Configuration Modal -->
<div class="modal-overlay" id="configModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-feather="settings"></i> Konfigurasi Match</h3>
            <button class="modal-close" onclick="closeConfigModal()">&times;</button>
        </div>
        <form id="configForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Topik Pertanyaan</label>
                    <select name="topic_id" id="configTopicSelect" class="form-control" required onchange="updateMaterialOptions()">
                        <option value="">Pilih Topik...</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" data-materials="{{ json_encode($topic->materials) }}">{{ $topic->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Materi Pembelajaran</label>
                    <select name="material_id" id="configMaterialSelect" class="form-control" required disabled>
                        <option value="">Pilih topik terlebih dahulu...</option>
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Jumlah Soal</label>
                        <input type="number" name="total_questions" id="configTotalQuestions" class="form-control" value="10" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Durasi Permainan (mm:ss)</label>
                        <input type="text" name="duration_display" id="configDurationDisplay" class="form-control" value="" pattern="[0-5][0-9]:[0-5][0-9]" placeholder="mm:ss" required autocomplete="off" style="font-family: 'Courier New', monospace; text-align: center; font-size: 1.2rem; font-weight: 700; letter-spacing: 3px;">
                        <input type="hidden" name="time_per_question" id="configTimePerQuestion" value="">
                        <div id="durationError" class="field-error" style="display: none; color: var(--danger); font-size: 0.8rem; margin-top: 0.25rem;">
                            <i data-feather="alert-circle" style="width: 12px; height: 12px; vertical-align: middle;"></i>
                            Durasi harus diisi dengan format mm:ss
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfigModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save"></i> Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reshuffle Confirmation Modal -->
<div class="modal-overlay" id="reshuffleModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header" style="background: var(--primary); color: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
            <h3 class="modal-title" style="color: white;">
                <i data-feather="shuffle"></i>
                Acak Ulang Bagan?
            </h3>
            <button type="button" class="modal-close" style="color: white; opacity: 0.8;" onclick="closeModal('reshuffleModal')">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="modal-body" style="text-align: center; padding: 2rem 1.5rem;">
            <div style="width: 64px; height: 64px; background: rgba(249, 115, 22, 0.1); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--primary);">
                <i data-feather="refresh-cw" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 style="font-weight: 700; margin-bottom: 0.5rem; color: var(--dark);">Apakah Anda yakin?</h4>
            <p class="text-muted" style="font-size: 0.9rem; line-height: 1.5;">Mengacak ulang bagan akan mereset semua konfigurasi match (topik, materi, jumlah soal). Urutan tim akan diacak secara acak.</p>
        </div>
        <div class="modal-footer" style="background: var(--cream); border-top: none; justify-content: center; padding: 1.25rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('reshuffleModal')" style="min-width: 100px;">Batal</button>
            <form action="{{ route('admin.tournaments.reshuffle', $tournament->id) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary" style="min-width: 100px;">
                    <i data-feather="shuffle"></i> Ya, Acak
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* PIN Card */
    .pin-card {
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: var(--radius-lg);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
    }
    .pin-card-body {
        text-align: center;
        padding: 1.5rem;
    }
    .pin-label {
        color: rgba(255,255,255,0.8);
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    .pin-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        letter-spacing: 8px;
        font-family: 'Courier New', monospace;
    }
    .pin-hint {
        color: rgba(255,255,255,0.7);
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
    .btn-copy {
        margin-top: 1rem;
        background: rgba(255,255,255,0.2);
        color: white;
        border: none;
        min-width: 120px;
    }
    .btn-copy:hover {
        background: rgba(255,255,255,0.3);
    }
    
    /* Stats Row */
    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .stat-card {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.75rem 1.25rem;
        background: white;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-soft);
        font-weight: 600;
        font-size: 0.9rem;
    }
    .stat-card i {
        color: var(--primary);
    }
    
    /* Bracket Layout */
    .bracket-scroll-container {
        overflow-x: auto;
        padding: 1rem 0 2rem;
        background: rgba(255,255,255,0.5);
        border-radius: var(--radius-lg);
    }
    
    .bracket-wrapper {
        display: flex;
        gap: 80px;
        min-width: max-content;
        padding: 1rem 2rem;
        align-items: flex-start;
        position: relative;
    }
    
    .bracket-round {
        display: flex;
        flex-direction: column;
        min-width: 290px;
        position: relative;
        z-index: 10; /* Ensure rounds are above connectors */
    }
    
    .round-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: 1px;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 8px 16px;
        border-radius: 50px;
        margin-bottom: 1rem;
        color: white;
    }
    
    .round-title.round-1 { background: #3B82F6; }
    .round-title.round-2 { background: #8B5CF6; }
    .round-title.semi { background: #F59E0B; }
    .round-title.final { background: linear-gradient(135deg, #EF4444, #DC2626); }
    .round-title.champion { background: linear-gradient(135deg, #FFD700, #F59E0B); color: #92400E; }
    
    .round-matches {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 24px;
        min-height: 100%;
    }
    
    .match-wrapper {
        position: relative;
    }
    
    /* Match Card */
    .match-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 2px solid transparent;
        overflow: hidden;
        transition: all 0.3s;
    }
    
    .match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    
    .match-card.completed { border-color: var(--accent-green); }
    .match-card.ready { border-color: #3B82F6; }
    
    .match-header {
        padding: 0.6rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .match-header.round-1 { background: #3B82F6; }
    .match-header.round-2 { background: #8B5CF6; }
    .match-header.semi { background: #F59E0B; }
    .match-header.final { background: linear-gradient(135deg, #EF4444, #DC2626); }
    
    .match-body { padding: 0.75rem; }
    
    .match-info {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 0.5rem;
    }
    
    .match-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
        background: #FFF7ED;
        color: #EA580C;
        border: 1px solid #FFDDC1;
    }
    
    .match-info-badge i {
        width: 10px;
        height: 10px;
    }
    
    .match-team {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0.75rem;
        border-radius: var(--radius-sm);
        margin-bottom: 0.25rem;
        font-weight: 600;
        font-size: 0.9rem;
        background: #F8FAFC;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .match-team:last-child { margin-bottom: 0; }
    
    .match-team.winner {
        /* No Background Green! Just border or subtle style */
        background: white;
        color: var(--dark);
        border: 2px solid var(--accent-green);
    }
    
    .match-team.loser {
        opacity: 0.5;
        filter: grayscale(100%);
    }
    
    .match-team.tbd {
        color: var(--gray);
        font-style: italic;
        background: #F1F5F9;
    }
    
    .team-name {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .team-number {
        width: 26px;
        height: 26px;
        background: linear-gradient(135deg, #3B82F6, #8B5CF6);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: white;
        font-weight: 800;
    }
    
    .match-team.winner .team-number {
        /* background: rgba(255,255,255,0.3); */
    }
    
    .match-team.tbd .team-number {
        background: #CBD5E1;
    }
    
    .team-score {
        font-weight: 800;
        font-size: 1rem;
    }
    
    .match-action {
        padding: 0.6rem 0.75rem;
        text-align: center;
        border-top: 1px solid #F1F5F9;
    }
    
    /* Winner Card */
    .winner-card {
        background: linear-gradient(135deg, #FFD700, #FDB931) !important;
        text-align: center;
        padding: 1.5rem !important;
        border: none !important;
    }
    
    .winner-card .trophy {
        font-size: 3rem;
        animation: bounce 2s infinite;
    }
    
    .winner-card .winner-name {
        font-size: 1.3rem;
        font-weight: 800;
        color: #92400E;
        margin-top: 0.75rem;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
</style>
@endpush

@push('scripts')
<script>
    function copyPin(pin) {
        const btn = document.getElementById('copyPinBtn');
        const content = document.getElementById('copyPinContent');
        const originalContent = content.innerHTML;
        
        navigator.clipboard.writeText(pin).then(() => {
            btn.style.background = '#22C55E';
            content.innerHTML = '<i data-feather="check"></i> Tersalin';
            feather.replace();
            
            setTimeout(() => {
                btn.style.background = 'rgba(255,255,255,0.2)';
                content.innerHTML = originalContent;
                feather.replace();
            }, 2000);
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = pin;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            btn.style.background = '#22C55E';
            content.innerHTML = '<i data-feather="check"></i> Tersalin';
            feather.replace();
            
            setTimeout(() => {
                btn.style.background = 'rgba(255,255,255,0.2)';
                content.innerHTML = originalContent;
                feather.replace();
            }, 2000);
        });
    }

    let currentMatchId = null;

    function openConfigModal(matchId, topicId, materialId, totalQ, timePerQ) {
        currentMatchId = matchId;
        document.getElementById('configForm').action = `/admin/tournaments/matches/${matchId}/configure`;
        
        document.getElementById('configTopicSelect').value = topicId || '';
        document.getElementById('configTotalQuestions').value = totalQ || 10;
        
        const durationDisplayEl = document.getElementById('configDurationDisplay');
        const durationHiddenEl = document.getElementById('configTimePerQuestion');
        const errorEl = document.getElementById('durationError');
        
        // Ensure strictly positive integer check
        if (timePerQ && parseInt(timePerQ) > 0) {
            durationHiddenEl.value = timePerQ;
            durationDisplayEl.value = secondsToMMSS(timePerQ);
        } else {
            durationHiddenEl.value = '';
            durationDisplayEl.value = '';
        }
        
        if(errorEl) errorEl.style.display = 'none';
        
        // Load materials
        const topicSelect = document.getElementById('configTopicSelect');
        // Trigger manually
        updateMaterialOptions();
        
        if (materialId) {
             setTimeout(() => {
                document.getElementById('configMaterialSelect').value = materialId || '';
            }, 100);
        }
        
        openModal('configModal');
        feather.replace();
    }

    function validateConfigForm(e) {
        const durationDisplay = document.getElementById('configDurationDisplay').value;
        const errorEl = document.getElementById('durationError');
        
        if (!durationDisplay || !durationDisplay.match(/^[0-5][0-9]:[0-5][0-9]$/)) {
            e.preventDefault();
            if(errorEl) {
                errorEl.style.display = 'block';
                const input = document.getElementById('configDurationDisplay');
                input.classList.add('is-invalid');
                setTimeout(() => input.classList.remove('is-invalid'), 500);
            }
            return false;
        }
        
        if(errorEl) errorEl.style.display = 'none';
        return true;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('configForm');
        if(form) {
            form.addEventListener('submit', validateConfigForm);
        }
        
        // Position bracket matches
        positionBracketMatches();
        // Wait for positions to settle
        setTimeout(() => {
            drawConnectorLines();
        }, 100);
        
        window.addEventListener('resize', () => {
             drawConnectorLines();
        });
    });

    function closeConfigModal() {
        closeModal('configModal');
        currentMatchId = null;
    }

    function updateMaterialOptions() {
        const topicSelect = document.getElementById('configTopicSelect');
        const materialSelect = document.getElementById('configMaterialSelect');
        const selectedOption = topicSelect.options[topicSelect.selectedIndex];
        
        materialSelect.innerHTML = '<option value="">Pilih Materi...</option>';
        
        if (selectedOption && selectedOption.dataset.materials) {
            const materials = JSON.parse(selectedOption.dataset.materials);
            if (materials.length > 0) {
                materialSelect.disabled = false;
                materials.forEach(mat => {
                    const opt = document.createElement('option');
                    opt.value = mat.id;
                    opt.textContent = mat.name;
                    materialSelect.appendChild(opt);
                });
            } else {
                materialSelect.disabled = true;
                materialSelect.innerHTML = '<option value="">Tidak ada materi</option>';
            }
        } else {
            materialSelect.disabled = true;
        }
    }

    function secondsToMMSS(seconds) {
        if (!seconds || seconds <= 0) return '';
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function mmssToSeconds(mmss) {
        if (!mmss) return 0;
        const parts = mmss.split(':');
        if (parts.length !== 2) return 0; // Return 0 if invalid
        const mins = parseInt(parts[0]) || 0;
        const secs = parseInt(parts[1]) || 0;
        return (mins * 60) + secs;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const durationDisplay = document.getElementById('configDurationDisplay');
        const durationHidden = document.getElementById('configTimePerQuestion');
        
        if (durationDisplay && durationHidden) {
            durationDisplay.addEventListener('input', function() {
                let val = this.value.replace(/[^0-9]/g, '');
                if (val.length >= 2) {
                    val = val.substring(0, 2) + ':' + val.substring(2, 4);
                }
                if (val.length > 5) val = val.substring(0, 5);
                this.value = val;
                
                if (val.match(/^[0-5][0-9]:[0-5][0-9]$/)) {
                    durationHidden.value = mmssToSeconds(val);
                }
            });
        }
    });
    
    // Position matches in later rounds
    function positionBracketMatches() {
        const wrapper = document.getElementById('bracketWrapper');
        if (!wrapper) return;
        
        const rounds = wrapper.querySelectorAll('.bracket-round');
        
        for (let roundIndex = 1; roundIndex < rounds.length; roundIndex++) {
            const prevRound = rounds[roundIndex - 1];
            const currentRound = rounds[roundIndex];
            
            const prevMatches = prevRound.querySelectorAll('.match-wrapper');
            const currentMatches = currentRound.querySelectorAll('.match-wrapper');
            
            currentMatches.forEach((match, matchIndex) => {
                const pairStart = matchIndex * 2;
                const match1 = prevMatches[pairStart];
                const match2 = prevMatches[pairStart + 1];
                
                if (match1 && match2) {
                    const rect1 = match1.getBoundingClientRect();
                    const rect2 = match2.getBoundingClientRect();
                    const center1 = rect1.top + rect1.height / 2;
                    const center2 = rect2.top + rect2.height / 2;
                    const targetCenter = (center1 + center2) / 2;
                    
                    const currentRect = match.getBoundingClientRect();
                    const currentCenter = currentRect.top + currentRect.height / 2;
                    const offset = targetCenter - currentCenter;
                    
                    const currentMargin = parseFloat(getComputedStyle(match).marginTop) || 0;
                    match.style.marginTop = (currentMargin + offset) + 'px';
                } else if (match1) {
                    const rect1 = match1.getBoundingClientRect();
                    const currentRect = match.getBoundingClientRect();
                    const offset = (rect1.top + rect1.height / 2) - (currentRect.top + currentRect.height / 2);
                    const currentMargin = parseFloat(getComputedStyle(match).marginTop) || 0;
                    match.style.marginTop = (currentMargin + offset) + 'px';
                }
            });
        }
    }
    
    // Draw connector lines using SVG
    function drawConnectorLines() {
        const wrapper = document.getElementById('bracketWrapper');
        if (!wrapper) return;
        
        // Cleanup old connectors (divs)
        wrapper.querySelectorAll('.connector').forEach(el => el.remove());
        // Cleanup old SVG if exists
        const existingSvg = wrapper.querySelector('.bracket-lines-svg');
        if (existingSvg) existingSvg.remove();
        
        const wrapperRect = wrapper.getBoundingClientRect();
        
        // Create SVG Container
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute('class', 'bracket-lines-svg');
        Object.assign(svg.style, {
            position: 'absolute',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            zIndex: '0',
            pointerEvents: 'none'
        });
        
        // Config
        const strokeColor = '#94A3B8'; // Slate 400
        const strokeWidth = 4;
        const radius = 12; // Corner radius
        const connectorGap = 40; // Distance before vertical line
        
        const rounds = wrapper.querySelectorAll('.bracket-round');
        
        rounds.forEach((round, roundIndex) => {
            if (roundIndex >= rounds.length - 1) return;
            
            const currentMatches = round.querySelectorAll('.match-wrapper');
            const nextRound = rounds[roundIndex + 1];
            const nextMatches = nextRound.querySelectorAll('.match-wrapper');
            let nextMatchIndex = 0;
            
            for (let i = 0; i < currentMatches.length; i += 2) {
                const match1 = currentMatches[i];
                const match2 = currentMatches[i + 1];
                const targetMatch = nextMatches[nextMatchIndex];
                
                if (targetMatch && match1) {
                    const rect1 = match1.getBoundingClientRect();
                    const targetRect = targetMatch.getBoundingClientRect();
                    
                    // Coordinates relative to wrapper
                    const p1 = {
                        x: rect1.right - wrapperRect.left,
                        y: rect1.top + rect1.height / 2 - wrapperRect.top
                    };
                    
                    const pt = {
                        x: targetRect.left - wrapperRect.left,
                        y: targetRect.top + targetRect.height / 2 - wrapperRect.top
                    };
                    
                    let d = '';
                    
                    if (match2) {
                        // FORK: Two sources -> One target
                        const rect2 = match2.getBoundingClientRect();
                        const p2 = {
                            x: rect2.right - wrapperRect.left,
                            y: rect2.top + rect2.height / 2 - wrapperRect.top
                        };
                        
                        const midX = p1.x + connectorGap;
                        
                        // Top Match Path
                        d += `M ${p1.x} ${p1.y} L ${midX - radius} ${p1.y}`;
                        d += `Q ${midX} ${p1.y} ${midX} ${p1.y + radius}`; // Top Corner
                        
                        // Bottom Match Path
                        d += `M ${p2.x} ${p2.y} L ${midX - radius} ${p2.y}`;
                        d += `Q ${midX} ${p2.y} ${midX} ${p2.y - radius}`; // Bottom Corner
                        
                        // Vertical Line connection
                        // From Top curve end to Bottom curve end
                        d += `M ${midX} ${p1.y + radius} L ${midX} ${p2.y - radius}`;
                        
                        // Path to Target
                        // From center of vertical line to target
                        const midY = (p1.y + p2.y) / 2;
                        d += `M ${midX} ${midY} L ${pt.x} ${pt.y}`;
                        
                    } else {
                        // SINGLE: One source -> One target (e.g. final or bye flow)
                        // Simple S-curve or L-shape?
                        // L-shape with rounded corners
                        
                        const midX = (p1.x + pt.x) / 2; 
                        
                        d += `M ${p1.x} ${p1.y} L ${midX - radius} ${p1.y}`;
                        
                        if (Math.abs(p1.y - pt.y) > radius * 2) {
                            // Needs vertical movement
                            const direction = pt.y > p1.y ? 1 : -1;
                             d += `Q ${midX} ${p1.y} ${midX} ${p1.y + (radius * direction)}`;
                             d += `L ${midX} ${pt.y - (radius * direction)}`;
                             d += `Q ${midX} ${pt.y} ${midX + radius} ${pt.y}`;
                        } else {
                            // Nearly straight
                            d += `L ${midX} ${pt.y}`;
                        }
                        
                        d += `L ${pt.x} ${pt.y}`;
                    }
                    
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute('d', d);
                    path.setAttribute('stroke', strokeColor);
                    path.setAttribute('stroke-width', strokeWidth);
                    path.setAttribute('fill', 'none');
                    path.setAttribute('stroke-linecap', 'round');
                    path.setAttribute('stroke-linejoin', 'round');
                    svg.appendChild(path);
                }
                
                nextMatchIndex++;
            }
        });
        
        wrapper.appendChild(svg);
    }
</script>
@endpush

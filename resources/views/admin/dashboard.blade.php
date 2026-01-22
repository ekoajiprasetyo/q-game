@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="page-title-icon">
                    <i data-feather="home"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; line-height: 1.2; color: var(--dark);">Selamat Datang!</h1>
                    <p class="page-subtitle" style="margin: 4px 0 0 0; line-height: 1.2;">Kelola game tarik tambang edukasi Anda dari sini</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card orange">
            <div class="stat-icon">
                <i data-feather="folder"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalTopics }}</div>
                <div class="stat-label">Topik</div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon">
                <i data-feather="layers"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalMaterials }}</div>
                <div class="stat-label">Materi</div>
            </div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon">
                <i data-feather="help-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalQuestions }}</div>
                <div class="stat-label">Pertanyaan</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">
                <i data-feather="play-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalSessions }}</div>
                <div class="stat-label">Game Dimainkan</div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Materials -->
        <div class="card" style="height: 100%;">
            <div class="card-header">
                <h3 class="card-title">
                    <div class="card-title-icon" style="background: linear-gradient(135deg, var(--accent-purple), #9B5DE5);">
                        <i data-feather="layers" style="width: 16px; height: 16px; color: white;"></i>
                    </div>
                    Materi Terbaru
                </h3>
                <a href="{{ route('admin.topics.index') }}" class="btn btn-ghost btn-sm">
                    Lihat Semua
                    <i data-feather="arrow-right"></i>
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($recentMaterials->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">📄</div>
                        <div class="empty-state-title">Belum ada materi</div>
                        <div class="empty-state-text">Mulai dengan membuat topik dan materi baru</div>
                    </div>
                @else
                    <div style="padding: 0.5rem 0;">
                        @foreach($recentMaterials as $material)
                            <div style="display: flex; align-items: center; padding: 0.875rem 1.5rem; gap: 1rem; transition: var(--transition);" class="hover-row">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, {{ $material->color ?? '#B47EFF' }}30, {{ $material->color ?? '#B47EFF' }}50); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    {{ $material->icon ?? '📄' }}
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">{{ $material->name }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">{{ $material->topic?->name ?? '-' }}</div>
                                </div>
                                
                                <span class="badge badge-blue">{{ $material->questions_count }} soal</span>

                                @if($material->topic_id)
                                    @php
                                        $hasActiveSession = $material->activeSession && $material->activeSession->session_pin;
                                    @endphp
                                    <button type="button" class="btn btn-sm {{ $hasActiveSession ? 'btn-success' : 'btn-primary' }} pin-btn-{{ $material->id }}"
                                            @if($hasActiveSession) data-pin="{{ $material->activeSession->session_pin }}" title="Klik untuk menyalin" @endif
                                            onclick="event.preventDefault(); event.stopPropagation(); toggleGamePin(this, {{ $material->topic_id }}, {{ $material->id }})"
                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        @if($hasActiveSession)
                                            <i data-feather="copy" style="width: 12px; height: 12px; margin-right: 4px;"></i> {{ $material->activeSession->session_pin }}
                                        @else
                                            <i data-feather="play" style="width: 12px; height: 12px; margin-right: 4px;"></i> Main
                                        @endif
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    
    <!-- Recent Tournaments -->
    <div class="card" style="height: 100%;">
        <div class="card-header">
            <h3 class="card-title">
                <div class="card-title-icon" style="background: #3B82F6;">
                    <i data-feather="award" style="width: 16px; height: 16px; color: white;"></i>
                </div>
                Turnamen Terbaru
            </h3>
            <a href="{{ route('admin.tournaments.index') }}" class="btn btn-ghost btn-sm">
                Lihat Semua
                <i data-feather="arrow-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if(isset($recentTournaments) && $recentTournaments->isNotEmpty())
                <div style="padding: 0.5rem 0;">
                    @foreach($recentTournaments as $tournament)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.875rem 1.5rem; gap: 1rem; border-bottom: 1px solid #f1f5f9; transition: all 0.2s;" class="hover-row">
                            <!-- Info -->
                            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                    <div style="width: 40px; height: 40px; background: #3B82F6; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    🏆
                                </div>
                                <div>
                                    <div style="font-weight: 600; font-size: 1rem; color: var(--dark);">{{ $tournament->title }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">
                                        <i data-feather="calendar" style="width: 10px; height: 10px;"></i> {{ $tournament->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Badges -->
                            <div style="display: flex; gap: 10px; align-items: center;">
                                    <!-- Jumlah Tim (Blue) -->
                                    <div class="badge badge-blue" style="font-size: 0.8rem; padding: 6px 12px; border-radius: 20px;">
                                    <i data-feather="users" style="width: 14px; height: 14px; margin-right: 6px;"></i>
                                    {{ $tournament->teams->count() }} Tim
                                    </div>
        
                                    <!-- PIN (Green + Copy) -->
                                    <button onclick="copyTournamentPin(this, '{{ $tournament->pin }}')" class="btn btn-sm btn-success" style="padding: 4px 12px; border-radius: 20px; font-weight: 700; display: flex; align-items: center; border: none; font-size: 0.8rem;">
                                    <span class="pin-content" style="display: flex; align-items: center;">
                                        <i data-feather="copy" style="width: 14px; height: 14px; margin-right: 6px;"></i>
                                        {{ $tournament->pin }}
                                    </span>
                                    </button>
                                    
                                    <a href="{{ route('admin.tournaments.show', $tournament->id) }}" class="btn btn-icon btn-ghost btn-sm" title="Kelola">
                                    <i data-feather="settings"></i>
                                    </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">🏆</div>
                    <div class="empty-state-title">Belum ada turnamen</div>
                    <div class="empty-state-text">Buat turnamen baru untuk memulai kompetisi</div>
                    <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary mt-3">
                        <i data-feather="plus"></i>
                        Buat Turnamen
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Sessions -->
<div class="card mb-4" style="margin-top: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <div class="card-title-icon" style="background: linear-gradient(135deg, var(--accent-green), #5AB890);">
                        <i data-feather="play-circle" style="width: 16px; height: 16px; color: white;"></i>
                    </div>
                    Game Terakhir
                </h3>
                <a href="{{ route('admin.sessions.index') }}" class="btn btn-ghost btn-sm">
                    Lihat Semua
                    <i data-feather="arrow-right"></i>
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($recentSessions->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">🎮</div>
                        <div class="empty-state-title">Belum ada game</div>
                        <div class="empty-state-text">Mainkan game pertama Anda!</div>
                    </div>
                @else
                    <div style="padding: 0.5rem 0;">
                        @foreach($recentSessions as $session)
                            <div style="display: flex; align-items: center; padding: 0.875rem 1.5rem; gap: 1rem; text-decoration: none; color: inherit; transition: var(--transition); cursor: pointer;" class="hover-row" onclick="openDetailModal({{ $session->id }})">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary)30, var(--accent-yellow)50); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    🎯
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">{{ $session->custom_title ?? ($session->title ?? 'Game #'.$session->id) }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem; margin-bottom: 2px;">
                                        ID: #{{ $session->id }}
                                        @if($session->game_mode === 'tournament')
                                            <span style="font-size: 0.65rem; font-weight: 700; color: white; background: #F59E0B; padding: 1px 6px; border-radius: 10px; margin-left: 4px;">TURNAMEN</span>
                                        @endif
                                        @if($session->session_pin) | PIN: {{ $session->session_pin }} @endif
                                    </div>
                                    <div class="text-muted" style="font-size: 0.8rem;">{{ $session->created_at->diffForHumans() }}</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 700;">
                                        <span style="color: var(--team-red);">{{ $session->team_red_score }}</span>
                                        <span class="text-muted">vs</span>
                                        <span style="color: var(--team-blue);">{{ $session->team_blue_score }}</span>
                                    </div>
                                    @if($session->winner_team === 'blue')
                                        <span class="badge badge-blue">🏆 {{ $session->team_blue_name }}</span>
                                    @elseif($session->winner_team === 'red')
                                        <span class="badge badge-red">🏆 {{ $session->team_red_name }}</span>
                                    @else
                                        <span class="badge badge-yellow">🤝 Seri</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- DETAIL GAME MODAL (Same as sessions page) -->
    <div class="modal-overlay" id="detailGameModal">
        <div class="modal" style="max-width: 800px; width: 95%; display: flex; flex-direction: column; max-height: 90vh; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #FF9F43, #EE5A24); color: white; padding: 1.5rem; flex-shrink: 0;">
                <h3 class="modal-title" style="color:white; display:flex; align-items:center; gap:10px;">
                    <i data-feather="activity"></i> Detail Permainan
                </h3>
                <button type="button" class="modal-close" style="color: white; opacity: 0.8;" onclick="closeModal('detailGameModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body" id="detailGameContent" style="padding: 1.5rem; overflow-y: auto; overflow-x: hidden; background: #FFFFFF; flex: 1 1 auto; min-height: 0;">
                <!-- AJAX CONTENT HERE -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function copyTournamentPin(btn, pin) {
        if(!pin) return;
        
        // Prevent default button action
        if(event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        navigator.clipboard.writeText(pin).then(() => {
            const originalContent = btn.innerHTML;
            
            // Change style locally (optional, since it's already badge-success)
            // But we want to ensure visual feedback
            btn.innerHTML = '<span style="display: flex; align-items: center;"><i data-feather="check" style="width: 14px; height: 14px; margin-right: 6px;"></i> Tersalin</span>';
            feather.replace();
            
            // Revert after 2 seconds
            setTimeout(() => {
                btn.innerHTML = originalContent;
                feather.replace();
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            // Fallback
            const textArea = document.createElement("textarea");
            textArea.value = pin;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
             btn.innerHTML = '<span style="display: flex; align-items: center;"><i data-feather="check" style="width: 14px; height: 14px; margin-right: 6px;"></i> Tersalin</span>';
            feather.replace();
             setTimeout(() => {
                btn.innerHTML = originalContent;
                feather.replace();
            }, 2000);
        });
    }

    feather.replace();

    // Modal Slide Navigation
    let mCurrentSlide = 0;
    let mTotalSlides = 0;

    window.changeModalSlide = function(dir) {
        let next = mCurrentSlide + dir;
        if(next < 0 || next >= mTotalSlides) return;
        
        // Hide current
        const curEl = document.querySelector(`.m-slide[data-idx="${mCurrentSlide}"]`);
        if(curEl) curEl.classList.remove('active');
        
        // Show new
        mCurrentSlide = next;
        const nextEl = document.querySelector(`.m-slide[data-idx="${mCurrentSlide}"]`);
        if(nextEl) nextEl.classList.add('active');
        
        updateModalNav();
    }

    function updateModalNav() {
        const prevBtn = document.getElementById('m-btn-prev');
        const nextBtn = document.getElementById('m-btn-next');
        const pageInd = document.getElementById('m-cur-page');
        
        if(pageInd) pageInd.textContent = mCurrentSlide + 1;
        if(prevBtn) prevBtn.disabled = (mCurrentSlide === 0);
        if(nextBtn) nextBtn.disabled = (mCurrentSlide >= mTotalSlides - 1);
    }
    
    function openDetailModal(sessionId) {
        openModal('detailGameModal');
        const container = document.getElementById('detailGameContent');
        container.innerHTML = '<div style="padding: 3rem; text-align: center; color: #64748B;"><div class="spinner-border" style="display:inline-block; width:2rem; height:2rem; border:3px solid #cbd5e1; border-top-color:var(--primary); border-radius:50%; animation:spin 1s linear infinite; margin-bottom:10px;"></div><br>Memuat data permainan...</div><style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';
        
        fetch(`/admin/sessions/${sessionId}/modal`)
            .then(response => {
                if(!response.ok) throw new Error("Gagal memuat");
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
                if(typeof feather !== 'undefined') feather.replace();
                
                // Render KaTeX Math Formulas
                if (typeof renderMathInElement === 'function') {
                    renderMathInElement(container, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false },
                            { left: '\\(', right: '\\)', display: false },
                            { left: '\\[', right: '\\]', display: true }
                        ],
                        throwOnError: false
                    });
                }
                
                // Initialize Slide State
                const dataEl = document.getElementById('m-slides-data');
                if(dataEl) {
                    mTotalSlides = parseInt(dataEl.dataset.total);
                    mCurrentSlide = 0;
                    updateModalNav();
                }
            })
            .catch(err => {
                container.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--danger);">Gagal memuat detail permainan. Silakan coba lagi.</div>';
                console.error(err);
            });
    }
</script>
@endpush

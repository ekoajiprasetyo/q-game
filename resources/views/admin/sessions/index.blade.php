@extends('layouts.admin')

@section('title', 'Riwayat Game')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="page-title-icon">
                    <i data-feather="clock"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; line-height: 1.2; color: var(--dark);">Riwayat Game</h1>
                    <p class="page-subtitle" style="margin: 4px 0 0 0; line-height: 1.2;">Lihat semua pertandingan yang telah dimainkan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body" style="padding: 1rem 1.5rem;">
            <form action="{{ route('admin.sessions.index') }}" method="GET" class="d-flex gap-3 flex-wrap align-center">
                <!-- Search -->
                <div style="position:relative;">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Judul Game..."
                        value="{{ request('search') }}"
                        style="max-width: 250px; padding-left: 35px;"
                    >
                    <i data-feather="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; color:#94A3B8;"></i>
                </div>

                <!-- Topic Filter -->
                <select name="topic_id" class="form-control" style="max-width: 200px;" onchange="filterMaterials()">
                    <option value="">Semua Topik</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}" {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                            {{ $topic->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Material Filter -->
                <select name="material_id" class="form-control" style="max-width: 200px;">
                    <option value="">Semua Materi</option>
                    @foreach($materials as $mat)
                        <option value="{{ $mat->id }}" data-topic-id="{{ $mat->topic_id }}" {{ request('material_id') == $mat->id ? 'selected' : '' }}>
                            {{ $mat->name }}
                        </option>
                    @endforeach
                </select>

                <select name="winner_team" class="form-control" style="max-width: 150px;">
                    <option value="">Semua Hasil</option>
                    <option value="blue" {{ request('winner_team') === 'blue' ? 'selected' : '' }}>🔵 Tim Biru</option>
                    <option value="red" {{ request('winner_team') === 'red' ? 'selected' : '' }}>🔴 Tim Merah</option>
                    <option value="draw" {{ request('winner_team') === 'draw' ? 'selected' : '' }}>🤝 Seri</option>
                </select>

                <button type="submit" class="btn btn-secondary">
                    <i data-feather="filter"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'topic_id', 'material_id', 'winner_team']))
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-ghost">
                        <i data-feather="x"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    @if($surpriseSessions->isNotEmpty())
        <div class="card mb-4">
            <div class="card-body">
                <h3 style="margin:0 0 1rem;font-size:1.1rem">🎁 Riwayat Kotak Kejutan</h3>
                <div style="display:grid;gap:.75rem">
                    @foreach($surpriseSessions as $session)
                        @php($topScore = $session->teams->max('score'))
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem;border:1px solid #e9d5ff;border-radius:12px;background:#faf5ff">
                            <a href="{{ route('surprise.play', $session) }}" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex:1;min-width:0;text-decoration:none;color:inherit">
                                <div>
                                    <strong>{{ $session->title ?? $session->topic?->name ?? 'Kotak Kejutan' }}</strong>
                                    <div class="text-muted" style="font-size:.82rem;margin-top:4px">{{ $session->teams->count() }} tim · {{ $session->board_size }} kartu · {{ $session->ended_at?->format('d M Y, H:i') }}</div>
                                </div>
                                <div style="text-align:right"><strong>{{ $session->teams->where('score', $topScore)->pluck('name')->join(', ') }}</strong><div style="color:#6d28d9;font-weight:700">🏆 {{ $topScore }} poin</div></div>
                            </a>
                            @if(auth()->user()->isAdmin() || $session->created_by == auth()->id())
                                <button type="button" class="btn btn-ghost btn-icon" title="Hapus" style="color: var(--danger);" onclick="confirmDelete('{{ route('admin.surprise-sessions.destroy', $session) }}')">
                                    <i data-feather="trash-2"></i>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    @if($sessions->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding: 4rem 2rem;">
                <div class="empty-state-icon">🎮</div>
                <div class="empty-state-title">Belum ada game</div>
                <div class="empty-state-text">Data pertandingan sesuai filter akan muncul di sini.</div>
            </div>
        </div>
    @else
        <div style="display: grid; gap: 1rem;">
            @foreach($sessions as $session)
                <div class="card">
                    <div class="card-body">
                        <!-- GRID LAYOUT FOR ALIGNMENT -->
                        <div class="session-grid">
                            <!-- Game Info -->
                            <div class="d-flex align-center gap-3">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF9F43, #EE5A24); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(255, 149, 0, 0.3);">
                                    🎮
                                </div>
                                <div style="min-width: 0;"> <!-- Fix text overflow in grid -->
                                    <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $session->custom_title ?? ($session->title ?? 'Game #'.$session->id) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.25rem;">
                                        ID: <span style="font-family: monospace; font-weight: 600; background: #e2e8f0; padding: 2px 5px; border-radius: 4px;">#{{ $session->id }}</span>

                                        @if($session->game_mode === 'tournament')
                                            <span style="margin: 0 4px;">•</span>
                                            <span style="font-size: 0.75rem; font-weight: 700; color: white; background: linear-gradient(135deg, #F59E0B, #D97706); padding: 2px 8px; border-radius: 12px; letter-spacing: 0.5px;">TURNAMEN</span>
                                        @endif

                                        @if($session->session_pin)
                                            <span style="margin: 0 4px;">•</span>
                                            PIN: <span style="font-family: monospace; font-weight: 600; color: var(--primary); background: #FFF4E6; padding: 2px 5px; border-radius: 4px;">{{ $session->session_pin }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2 align-center">
                                        <span class="text-muted" style="font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                            <span>{{ $session->topic->icon ?? '📚' }}</span>
                                            <span>{{ $session->topic->name ?? 'Tanpa Topik' }}</span>
                                        </span>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted" style="font-size: 0.85rem;">
                                            {{ $session->total_questions }} soal
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Score (Swapped: Red vs Blue) - CENTERED -->
                            <div class="d-flex align-center justify-center gap-4">
                                <div style="text-align: center; min-width: 80px;">
                                    <div style="font-size: 0.7rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom:4px; white-space: nowrap;">{{ $session->team_red_name }}</div>
                                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--team-red); line-height:1;">{{ $session->team_red_score }}</div>
                                </div>
                                <div style="font-size: 1rem; font-weight: 700; color: #cbd5e1;">VS</div>
                                <div style="text-align: center; min-width: 80px;">
                                    <div style="font-size: 0.7rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom:4px; white-space: nowrap;">{{ $session->team_blue_name }}</div>
                                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--team-blue); line-height:1;">{{ $session->team_blue_score }}</div>
                                </div>
                            </div>

                            <!-- Winner & Actions - RIGHT ALIGNED -->
                            <div class="d-flex align-center justify-end gap-3">
                                <div style="text-align: right;">
                                    @if($session->winner_team === 'red')
                                        <span class="badge badge-red">🏆 {{ $session->team_red_name }}</span>
                                    @elseif($session->winner_team === 'blue')
                                        <span class="badge badge-blue">🏆 {{ $session->team_blue_name }}</span>
                                    @elseif($session->winner_team === 'draw')
                                        <span class="badge badge-yellow">🤝 Seri</span>
                                    @else
                                        <span class="badge" style="background: var(--light-gray); color: var(--gray);">Belum selesai</span>
                                    @endif
                                    <div class="text-muted" style="font-size: 0.8rem; margin-top: 0.25rem;">
                                        {{ $session->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <!-- Use Modal via AJAX -->
                                    <button type="button" class="btn btn-ghost btn-icon" title="Lihat Detail" onclick="openDetailModal({{ $session->id }})">
                                        <i data-feather="eye"></i>
                                    </button>

                                    <!-- Consistent Delete Modal -->
                                    <button type="button" class="btn btn-ghost btn-icon" title="Hapus" style="color: var(--danger);" onclick="confirmDelete('{{ route('admin.sessions.destroy', $session) }}')">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($sessions->hasPages())
            <div class="pagination mt-4">
                {{ $sessions->appends(request()->query())->links() }}
            </div>
        @endif
    @endif

    <!-- DETAIL GAME MODAL -->
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

    <!-- STYLES -->
    <style>
        .session-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 1.5rem;
            align-items: center;
        }
        @media (max-width: 992px) {
            .session-grid { grid-template-columns: 1fr; }
            .session-grid > div:nth-child(2) { justify-content: flex-start; margin: 10px 0; }
            .session-grid > div:nth-child(3) { justify-content: flex-start; }
            .session-grid > div:nth-child(3) > div:first-child { text-align: left; }
        }

        /* Modern Orange Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 2.5rem;
            padding-bottom: 2rem;
            list-style: none; /* Reset default list style */
        }
        /* Target Laravel Bootstrap Classes */
        .page-item .page-link {
            border: none;
            border-radius: 12px; /* Smooth rounded square */
            min-width: 40px; height: 40px;
            padding: 0 12px; /* Flexible width for Text (Prev/Next) */
            display: flex; align-items: center; justify-content: center;
            color: #64748B;
            font-weight: 700;
            font-size: 0.9rem;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }

        .page-item:not(.active) .page-link:hover {
            background: #FFF7ED; /* Light Orange */
            color: #EA580C; /* Dark Orange */
            transform: translateY(-3px);
            box-shadow: 0 6px 12px -2px rgba(234, 88, 12, 0.15);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #FF9F43, #EE5A24);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(238, 90, 36, 0.4);
            transform: scale(1.05);
            z-index: 1;
        }

        .page-item.disabled .page-link {
            background: transparent;
            color: #CBD5E1;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* Specific fix for SVG arrows if Laravel uses them */
        .page-link svg { width: 16px; height: 16px; }
    </style>
@endsection

@push('scripts')
<script>
    feather.replace();

    function filterMaterials() {
        const topicSelect = document.querySelector('select[name="topic_id"]');
        const materialSelect = document.querySelector('select[name="material_id"]');
        const topicId = topicSelect.value;
        const options = Array.from(materialSelect.querySelectorAll('option'));

        let hasSelection = false;

        options.forEach(opt => {
            if(opt.value === "") return;
            const mt = opt.getAttribute('data-topic-id');
            if(!topicId || mt === topicId) opt.style.display = '';
            else opt.style.display = 'none';
        });

        // Reset material if hidden
        const selected = materialSelect.options[materialSelect.selectedIndex];
        if(selected.style.display === 'none') materialSelect.value = "";
    }

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

    document.addEventListener('DOMContentLoaded', filterMaterials);
</script>
@endpush

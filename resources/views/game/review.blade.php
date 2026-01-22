<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Jawaban - {{ $session->title }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <!-- KaTeX for Math Formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true},{left: '$', right: '$', display: false}], throwOnError: false});"></script>
    
    <style>
        :root {
            --md-sys-color-primary: #FF9B50;
            --md-sys-color-surface: #FFFFFF;
            --text-main: #1E293B;
            --success: #10B981;
            --danger: #EF4444;
            --blue-team: #3B82F6;
            --red-team: #EF4444;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; padding: 0; 
            min-height: 100vh; 
            overflow-x: hidden; 
            overflow-y: auto; /* Force vertical scroll availability */
        }

        /* Animated Background */
        .admin-bg-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: radial-gradient(circle at 10% 20%, #F3F0FF 0%, #FFF8F0 60%, #FFFFFF 100%);
            pointer-events: none; /* Critical: Prevent BG from capturing clicks/scroll */
        }
        .blob { position: absolute; filter: blur(80px); opacity: 0.6; animation: float 10s infinite alternate ease-in-out; }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: #EADDFF; }
        .blob-2 { bottom: -10%; right: -10%; width: 60vw; height: 60vw; background: #FFDBC8; animation-delay: -5s; }
        .blob-3 { top: 40%; left: 40%; width: 30vw; height: 30vw; background: #E8DEF8; animation-delay: -2s; }
        @keyframes float { 0% { transform: translate(0, 0); } 100% { transform: translate(30px, 50px); } }

        .review-container {
            max-width: 800px; margin: 40px auto; padding: 0 20px;
            position: relative; z-index: 10;
            padding-bottom: 150px; /* Ensure space for scrolling to bottom */
        }

        .review-header {
            text-align: center; margin-bottom: 30px;
            background: rgba(255,255,255,0.8); backdrop-filter: blur(10px);
            padding: 20px; border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .review-header h1 { margin: 0; color: var(--text-main); font-weight: 800; font-size: 28px; }
        .review-header p { margin: 5px 0 0; color: #64748B; }

        /* Card Styles */
        .review-card {
            background: white; border-radius: 24px; padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); margin-bottom: 20px;
            display: none; flex-direction: column; gap: 20px;
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .review-card.active { display: flex; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .question-meta {
            display: flex; justify-content: space-between; align-items: center;
        }
        .q-number { background: #1E293B; color: white; padding: 6px 16px; border-radius: 50px; font-weight: 700; font-size: 14px; }
        .q-type { font-weight: 600; color: #94A3B8; font-size: 14px; text-transform: uppercase; }

        .q-text { 
            font-size: 18px; font-weight: 500; line-height: 1.05; color: #0F172A; 
            /* text-align inherited from editor inline styles */
        }
        /* Rich Text Content Styling - Zero spacing (same as play page) */
        .q-text * { margin: 0; padding: 0; }
        .q-text p { margin: 0 !important; }
        .q-text p:empty, .q-text br { display: block; content: ' '; min-height: 1em; }
        .q-text b, .q-text strong { font-weight: 700; }
        .q-text img { 
            max-width: 100%; max-height: 250px; 
            border-radius: 8px; object-fit: contain;
            margin: 2px 0 !important;
            vertical-align: middle;
            display: inline-block;
        }
        .q-text ul, .q-text ol { padding-left: 1.2em !important; margin: 2px 0 !important; }
        .q-text li { margin: 1px 0 !important; }
        /* KaTeX Math Styling */
        .q-text .katex { font-size: 1.1em; }
        .option-text .katex { font-size: 1em; }
        
        .q-img { max-width: 100%; border-radius: 12px; max-height: 250px; object-fit: contain; }

        /* Options List */
        .options-list { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
        .option-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border: 1.5px solid #E2E8F0; border-radius: 12px;
            background: #F8FAFC; transition: all 0.2s;
        }
        .option-key { 
            width: 32px; height: 32px; background: white; border: 1px solid #CBD5E1; 
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #475569;
        }
        .option-text { flex: 1; font-weight: 500; font-size: 16px; }
        
        /* Option States */
        .option-item.is-correct { background: #ECFDF5; border-color: #10B981; }
        .option-item.is-correct .option-key { background: #10B981; color: white; border-color: #10B981; }
        
        .badges-container { display: flex; gap: 5px; }
        .badge { 
            padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; color: white; 
            text-transform: uppercase; display: flex; align-items: center; gap: 4px;
        }
        .badge.red { background: var(--red-team); }
        .badge.blue { background: var(--blue-team); }
        .badge.check { background: var(--success); }

        /* Team Answer Summary Box */
        .team-summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .team-box { 
            padding: 15px; border-radius: 16px; background: white; border: 1px solid #E2E8F0;
            display: flex; flex-direction: column; align-items: center; gap: 5px; text-align: center;
        }
        .team-box.red { border-top: 4px solid var(--red-team); }
        .team-box.blue { border-top: 4px solid var(--blue-team); }
        
        .team-name { font-weight: 800; font-size: 12px; letter-spacing: 1px; color: #64748B; margin-bottom: 5px; }
        .team-ans-text { font-weight: 700; font-size: 18px; color: #0F172A; }
        .correct-tag { color: var(--success); font-weight: 700; font-size: 12px; display: flex; align-items: center; gap: 4px; }
        .wrong-tag { color: var(--danger); font-weight: 700; font-size: 12px; display: flex; align-items: center; gap: 4px; }

        /* Footer Controls */
        .controls-footer {
            margin-top: 30px; display: flex; justify-content: center; align-items: center; gap: 20px;
        }
        .nav-btn {
            width: 50px; height: 50px; border-radius: 50%; border: none; background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--text-main); font-size: 24px; transition: all 0.2s;
        }
        .nav-btn:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); background: var(--md-sys-color-primary); color: white; }
        .nav-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .page-info { font-weight: 700; font-size: 18px; color: #475569; }

        .back-menu-container {
            margin-top: 40px; text-align: center;
        }
        .btn-menu {
            display: inline-flex; align-items: center; gap: 10px;
            background: white; padding: 12px 30px; border-radius: 50px;
            color: var(--text-main); text-decoration: none; font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.2s;
        }
        .btn-menu:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="admin-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="review-container">
        <div class="review-header">
            <h1>Review Jawaban</h1>
            <p>Sesi: {{ $session->title }}</p>
        </div>

                @if(count($reviews) == 0)
            <div class="review-card active" style="text-align:center;">
                <h3>Belum ada data.</h3>
                <p>Tidak ada riwayat jawaban yang tersimpan untuk sesi ini.</p>
            </div>
        @else
            @foreach($reviews as $index => $item)
                @php 
                    $q = $item['question']; 
                    $options = [];
                    if(is_array($q->options)) $options = $q->options;
                    if(isset($options['choices'])) $options = $options['choices'];
                    
                    // Uses Pre-processed Data from Controller
                    $correctKeys = $item['correct_keys'];
                    
                    $redDisplay = $item['red']['display'] ?? [];
                    $redKeys = $item['red']['keys'] ?? [];

                    $blueDisplay = $item['blue']['display'] ?? [];
                    $blueKeys = $item['blue']['keys'] ?? [];
                @endphp

                <div class="review-card {{ $index === 0 ? 'active' : '' }}" id="card-{{ $index }}">
                    <div class="question-meta">
                        <span class="q-number">Soal {{ $index + 1 }}</span>
                        <span class="q-type">{{ str_replace('_', ' ', $q->question_type) }}</span>
                    </div>

                    @if(!empty($q->image_url))
                        <div style="margin: 20px 0; text-align: center; background: #F8FAFC; padding: 10px; border-radius: 12px;">
                            @if($index === 0)
                                <img src="{{ Str::startsWith($q->image_url, ['http', 'https']) ? $q->image_url : asset($q->image_url) }}" 
                                     class="q-img" alt="Gambar Soal">
                            @else
                                <img data-src="{{ Str::startsWith($q->image_url, ['http', 'https']) ? $q->image_url : asset($q->image_url) }}" 
                                     src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                     class="q-img lazy-img" alt="Gambar Soal" style="min-height: 200px;">
                            @endif
                        </div>
                    @endif

                    <div class="q-text">{!! $q->question_text !!}</div>

                    <!-- OPTIONS LIST (Only for Choice Types) -->
                    @if(!in_array($q->question_type, ['short_answer', 'isian_singkat']) && !empty($options) && (is_array($options) || is_object($options)))
                        <div class="options-list">
                        @foreach($options as $opt)
                            @php
                                // Robust Option Handling
                                $key = $loop->index; // Default key
                                $text = '';

                                if (is_array($opt)) {
                                    $key = $opt['key'] ?? ($opt['id'] ?? $loop->index);
                                    $text = $opt['text'] ?? ($opt['value'] ?? ($opt['answer'] ?? ($opt['label'] ?? $opt)));
                                } elseif (is_object($opt)) {
                                    $key = $opt->key ?? ($opt->id ?? $loop->index);
                                    $text = $opt->text ?? ($opt->value ?? ($opt->answer ?? ($opt->label ?? $opt)));
                                } else {
                                    $text = $opt;
                                    if(is_string($opt) && strlen($opt) <= 2) $key = $opt; 
                                }

                                // Final Safety Check
                                if (is_array($text) || is_object($text)) $text = json_encode($text);
                                
                                $keyStr = strtoupper(trim((string)$key));
                                $isCorrect = in_array($keyStr, $correctKeys);
                                $isRed = in_array($keyStr, $redKeys);
                                $isBlue = in_array($keyStr, $blueKeys);
                            @endphp
                            <div class="option-item {{ $isCorrect ? 'is-correct' : '' }}">
                                <div class="option-key">{{ $key }}</div>
                                <div class="option-text">{!! $text !!}</div>
                                <div class="badges-container">
                                    @if($isCorrect)<div class="badge check"><i data-feather="check"></i></div>@endif
                                    @if($isRed)<div class="badge red">Merah</div>@endif
                                    @if($isBlue)<div class="badge blue">Biru</div>@endif
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @endif

                    <!-- SPECIAL DISPLAY FOR SHORT ANSWER -->
                    @if(in_array($q->question_type, ['short_answer', 'isian_singkat']))
                        <div style="background: #ECFDF5; border: 1px solid #10B981; padding: 15px; border-radius: 12px; margin-top: 15px; text-align: center;">
                            <span style="font-weight: 700; color: #065F46; text-transform: uppercase; font-size: 12px; display: block; margin-bottom: 5px;">Kunci Jawaban</span>
                            <span style="font-size: 20px; font-weight: 800; color: #064E3B;">
                                {{ is_array($q->correct_answer) ? implode(', ', $q->correct_answer) : $q->correct_answer }}
                            </span>
                        </div>
                    @endif

                    <!-- TEAM COMPARISON (Always show logic status) -->
                    <div class="team-summary-grid">
                        <!-- RED -->
                        <div class="team-box red">
                            <div class="team-name">TIM MERAH</div>
                            @if(isset($item['red']))
                                <!-- Display Text Representation based on options if possible -->
                                <div class="team-ans-text">
                                    {{ implode(', ', $redDisplay) }}
                                </div>
                                @if($item['red']['is_correct'])
                                    <div class="correct-tag"><i data-feather="check-circle" width="14"></i> BENAR</div>
                                @else
                                    <div class="wrong-tag"><i data-feather="x-circle" width="14"></i> SALAH</div>
                                @endif
                            @else
                                <div style="color:#CBD5E1">Tidak menjawab</div>
                            @endif
                        </div>

                        <!-- BLUE -->
                        <div class="team-box blue">
                            <div class="team-name">TIM BIRU</div>
                            @if(isset($item['blue']))
                                <div class="team-ans-text">
                                    {{ implode(', ', $blueDisplay) }}
                                </div>
                                @if($item['blue']['is_correct'])
                                    <div class="correct-tag"><i data-feather="check-circle" width="14"></i> BENAR</div>
                                @else
                                    <div class="wrong-tag"><i data-feather="x-circle" width="14"></i> SALAH</div>
                                @endif
                            @else
                                <div style="color:#CBD5E1">Tidak menjawab</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        <div class="controls-footer">
            <button class="nav-btn" id="btn-prev" onclick="changeCard(-1)"><i data-feather="chevron-left"></i></button>
            <div class="page-info" id="indicator">1 / {{ count($reviews) }}</div>
            <button class="nav-btn" id="btn-next" onclick="changeCard(1)"><i data-feather="chevron-right"></i></button>
        </div>

        <div class="back-menu-container" style="display: flex; gap: 10px; justify-content: center;">
            @php
                $tournamentPin = session('tournament_pin');
                if(!$tournamentPin && $session->game_mode == 'tournament') {
                    // Try to get from relation if model loaded, or fallback. 
                    // Session might not load deep relations, rely on session() is safer.
                }
            @endphp

            @if($session->game_mode == 'tournament' && $tournamentPin)
                <a href="{{ route('tournament.bracket', ['pin' => $tournamentPin]) }}" class="btn-menu" style="background:var(--orange-btn); color: white;">
                    <i data-feather="grid"></i> Bracket Turnamen
                </a>
            @else
                <a href="{{ route('game.setup') }}" class="btn-menu">
                    <i data-feather="grid"></i> Menu Utama
                </a>
                
                @if($activeLobbyPin)
                <a href="{{ route('game.play') }}?pin={{ $activeLobbyPin }}&fs_request=1" class="btn-menu" style="background: #F97316; color: white;">
                    <i data-feather="play"></i> Main Lagi
                </a>
                @endif
            @endif
        </div>
    </div>

    <script>
        (function() {
            let currentIndex = 0;
            const total = {{ count($reviews) }};

            // Global functions for HTML onclick handlers
            window.changeCard = function(diff) {
                const newIndex = currentIndex + diff;
                if(newIndex >= 0 && newIndex < total) {
                    const currentCard = document.getElementById(`card-${currentIndex}`);
                    if(currentCard) currentCard.classList.remove('active');
                    
                    const nextCard = document.getElementById(`card-${newIndex}`);
                    if(nextCard) {
                        nextCard.classList.add('active');
                        
                        // Lazy Load Image Logic
                        const lazyImg = nextCard.querySelector('img.lazy-img');
                        if (lazyImg && lazyImg.dataset.src) {
                            lazyImg.src = lazyImg.dataset.src;
                            lazyImg.removeAttribute('data-src');
                            lazyImg.classList.remove('lazy-img');
                        }
                    }

                    currentIndex = newIndex;
                    window.updateControls();
                }
            };

            window.updateControls = function() {
                const prevBtn = document.getElementById('btn-prev');
                const nextBtn = document.getElementById('btn-next');
                const indicator = document.getElementById('indicator');

                if(prevBtn) prevBtn.disabled = currentIndex === 0;
                if(nextBtn) nextBtn.disabled = currentIndex === total - 1;
                if(indicator) indicator.innerText = `${currentIndex + 1} / ${total}`;
            };

            // Initializer for Turbo Navigation
            function initReviewPage() {
                // Force Scrollable Body & Fix Layout
                document.body.style.overflowY = 'auto';
                document.body.style.overflowX = 'hidden';
                document.body.style.height = 'auto';

                if(typeof feather !== 'undefined') feather.replace();
                currentIndex = 0; // Reset state
                
                // Render KaTeX Math Formulas
                if (typeof renderMathInElement === 'function') {
                    renderMathInElement(document.body, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false },
                            { left: '\\(', right: '\\)', display: false },
                            { left: '\\[', right: '\\]', display: true }
                        ],
                        throwOnError: false
                    });
                }
                
                if(total > 0) {
                    // Reset UI to first card
                    const allCards = document.querySelectorAll('.review-card');
                    allCards.forEach(c => c.classList.remove('active'));
                    const first = document.getElementById('card-0');
                    if(first) first.classList.add('active');

                    window.updateControls();
                } else {
                    const footer = document.querySelector('.controls-footer');
                    if(footer) footer.style.display = 'none';
                }
            }

            // Hook into Turbo and DOM events
            document.addEventListener('turbo:load', initReviewPage);
            // Also run if script executes after load (rare but possible)
            initReviewPage();
        })();
    </script>
</body>
</html>

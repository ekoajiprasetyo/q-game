@php
    $sessionDuration = $session->time_per_question ?? 60;
    $queryDuration = request()->query('duration', 0);
    $gameDuration = $queryDuration > 0 ? $queryDuration : $sessionDuration;

    // Get tournament PIN from Laravel session if exists
    $tournamentPin = session('tournament_pin', '');

    $gameDataPayload = [
        'session' => $session,
        'questionsRed' => $teamRedQuestions ?? [],
        'questionsBlue' => $teamBlueQuestions ?? [],
        'limit' => $limit ?? 10,
        'config' => ['duration' => $gameDuration],
        'resume' => ['redIndex' => $redIndex ?? 0, 'blueIndex' => $blueIndex ?? 0],
        'tournamentPin' => $tournamentPin,
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Q-Game | Bermain</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons & Phaser -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <!-- KaTeX for Math Formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --red-team: #FF6B6B;
            --blue-team: #4D96FF;
            --bg-color: #FFF8F0;
            --surface: rgba(255, 255, 255, 0.95);
            --text-main: #2D3142;
            --orange-btn: #F97316;
            /* Background Blobs */
            --blob-1: #EADDFF;
            --blob-2: #FFDBC8;
            --blob-3: #E8DEF8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; user-select: none; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            height: 100vh;
            overflow: hidden;
            overscroll-behavior: none;
        }

        /* --- Background Animation --- */
        .admin-bg-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: radial-gradient(circle at 10% 20%, #F3F0FF 0%, #FFF8F0 60%, #FFFFFF 100%);
        }
        .blob { position: absolute; filter: blur(80px); opacity: 0.6; animation: float 10s infinite alternate ease-in-out; }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: var(--blob-1); animation-delay: 0s; }
        .blob-2 { bottom: -10%; right: -10%; width: 60vw; height: 60vw; background: var(--blob-2); animation-delay: -5s; }
        .blob-3 { top: 40%; left: 40%; width: 30vw; height: 30vw; background: var(--blob-3); animation-delay: -2s; }
        @keyframes float { to { transform: translate(30px, 50px) scale(1.1); } }

        #game-container {
            position: absolute; top: 0; left: 0; width: 100%; height: 30%; /* Balanced: 30% */
            z-index: 1; 
        }
        
        /* UI Layer - takes BOTTOM 70% */
        #ui-layer {
            position: absolute; top: 30%; /* Matches game-container height */
            left: 0; width: 100%; height: 70%; /* Balanced space */
            z-index: 10; 
            display: flex;
            pointer-events: none; 
        }

        /* --- ZONES --- */
        .team-zone {
            flex: 1; height: 100%; padding: 20px;
            display: flex; flex-direction: column; 
            justify-content: flex-start; /* Align Top of Bottom Half */
            position: relative; pointer-events: auto;
            overflow-y: auto; /* Allow scroll if content is too tall */
        }

        .team-red-zone {
            border-right: 2px dashed rgba(0,0,0,0.1);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 107, 107, 0.1) 100%);
        }
        .team-blue-zone {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(77, 150, 255, 0.1) 100%);
        }

        /* --- HEADER HUD (Scores & Timer) --- */
        .hud-container {
            position: absolute; top: 0; width: 100%; height: 100px;
            z-index: 50;
            display: flex; justify-content: center; /* Only centers Timer */
            pointer-events: none;
        }

        /* Timer in Middle Top */
        .timer-box {
            background: rgba(255,255,255,0.95); padding: 5px 0; 
            width: 160px; /* Increased Width for larger font */
            border-radius: 0 0 24px 24px; box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            text-align: center; border-top: 6px solid #FF9B50;
            position: relative;
            z-index: 52;
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .timer-val { 
            font-size: 34px; font-weight: 900; color: var(--text-main); /* Larger & Bolder */
            line-height: 1.1; margin-bottom: 0;
            font-variant-numeric: tabular-nums; /* Keeps numbers stable width without ugly font */
            letter-spacing: -1px;
        }
        .timer-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #9CA3AF; font-weight: 700; margin-top: 2px; }

        /* Scores Moved to Safe Zone (Bottom of Game Area) */
        .score-pill {
            position: fixed; /* Fixed relative to screen */
            background: rgba(255,255,255,0.98); 
            border-radius: 20px; /* Larger radius */
            padding: 12px 30px; /* Larger padding */
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
            min-width: 110px; /* Wider */
            top: 18vh; 
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            z-index: 80;
            display: flex; flex-direction: column; justify-content: center;
            transform: scale(1.1); /* General scale up */
        }
        .score-val { font-size: 42px; font-weight: 900; line-height: 1; margin: 4px 0; white-space: nowrap; }
        .score-label { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.8; }
        
        /* Specific Positions */
        .color-red { 
            left: 20px; 
            color: var(--red-team); 
            border: 2px solid #FEE2E2; border-bottom: 4px solid #FEE2E2; 
        }
        .color-blue { 
            right: 20px; 
            color: var(--blue-team); 
            border: 2px solid #DBEAFE; border-bottom: 4px solid #DBEAFE; 
        }

        .btn-control {
            position: absolute; top: 20px; left: 20px;
            width: 42px; height: 42px; background: white; border-radius: 12px; border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text-main); pointer-events: auto; z-index: 60;
        }
        .btn-control:active { transform: scale(0.95); }

        /* --- QUESTIONS --- */
        .question-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; padding: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.6);
            width: 100%; max-width: 600px; /* Increased max width */
            align-self: center;
            transform: translateY(0); opacity: 1;
            display: flex; flex-direction: column; /* Default */
            transition: all 0.3s ease;
        }

        /* Mode Grid (Active when image present or content long) */
        .question-card.grid-mode {
            max-width: 900px; /* Allow wider card */
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }
        
        .q-content {
            display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px;
            width: 100%;
        }
        
        /* In Grid Mode, q-content is Left Column */
        .question-card.grid-mode .q-content {
            margin-bottom: 0;
            padding-right: 18px; /* Increased padding */
            border-right: 2px solid rgba(0,0,0,0.05); /* Thicker separator */
            max-height: 450px; overflow-y: auto; /* Taller scroll area */
            display: flex; flex-direction: column; justify-content: center; /* Center content vertically too */
        }
        
        /* In Grid Mode, Answer Area is Right Column */
        .question-card.grid-mode .answer-area-wrapper {
             display: flex; flex-direction: column; justify-content: center; /* Vertically center keyboard */
             height: 100%; /* Match height of q-content */
        }

        .q-img-wrapper {
            width: 100%; display: flex; justify-content: center;
            border-radius: 12px; overflow: hidden; background: #f3f4f6;
            display: none; /* JS will toggle */
            margin-bottom: 10px;
        }
        .q-img { max-height: 150px; max-width: 100%; object-fit: contain; }
        
        /* Grid Mode Image Sizing - MAXIMIZED */
        .question-card.grid-mode .q-img { 
            max-height: 400px; /* Maximum Layout Height */
            width: 100%;       /* Force Full Width */
            object-fit: contain; /* Ensure full image visible */
            background: rgba(0,0,0,0.02); /* Subtle contrast */
        }
        
        .question-card.grid-mode .q-img-wrapper {
             background: transparent; 
             margin-bottom: 20px;
             width: 100%; display: flex; justify-content: center;
        }

        .q-text {
            font-size: 16px; font-weight: 500; color: var(--text-main);
            line-height: 1.35;
        }
        /* Rich Text Content Styling */
        .q-text * { margin: 0; padding: 0; }
        .q-text p { margin: 0 !important; }
        .q-text p:empty, .q-text br { display: block; content: ' '; min-height: 1em; } 
        .q-text b, .q-text strong { font-weight: 700; }
        .q-text img { 
            max-width: 100%; 
            max-height: 450px; 
            width: auto !important;  /* Reset to natural width (fixes aspect ratio) */
            height: auto !important; /* Reset to natural height (fixes aspect ratio) */
            
            border-radius: 8px; 
            object-fit: contain;
            
            display: block;          /* Own line */
            margin: 10px auto !important; /* Center horizontally */
        }
        .q-text ul, .q-text ol { padding-left: 1.2em !important; margin: 2px 0 !important; }
        .q-text li { margin: 1px 0 !important; }
        /* KaTeX Math Styling */
        .q-text .katex { font-size: 1.1em; }
        .ans-btn .katex { font-size: 1em; }

        /* --- ANSWERS COLOR --- */
        .answers-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

        .ans-btn {
            padding: 10px 15px; border-radius: 12px;
            font-size: 14px; font-weight: 600; color: #334155; 
            cursor: pointer; transition: all 0.2s; 
            text-align: left; /* Poin 2: Align Left */
            border: none;
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
            display: flex; align-items: center; justify-content: flex-start;
            min-height: 48px; width: 100%;
            line-height: 1.2;
        }
        .ans-btn:hover:not(:disabled) { filter: brightness(0.92); transform: translateY(-2px); }
        .ans-btn:active:not(:disabled) { top: 2px; box-shadow: none; transform: translateY(2px); }
        
        /* Poin 4: Tombol Berwarna (Pastel) */
        .btn-opt-0 { background: #FFD1D1; color: #991B1B; } 
        .btn-opt-1 { background: #D1FAE5; color: #065F46; } 
        .btn-opt-2 { background: #DBEAFE; color: #1E40AF; } 
        .btn-opt-3 { background: #FEF3C7; color: #92400E; } 

        /* Poin 5: Feedback Warna Lama (Light) */
        .ans-btn.correct { background: #ECFDF5 !important; color: #059669 !important; border: 2px solid #10B981 !important; box-shadow: none !important; }
        .ans-btn.wrong { background: #FEF2F2 !important; color: #B91C1C !important; border: 2px solid #EF4444 !important; box-shadow: none !important; }
        
        .opt-key { display: none; } /* Poin 1: Hide Letters logic just in case */
        
        /* True False Colors (Initial State) */
        .btn-opt-true { background: #10B981; color: white; border: none; box-shadow: 0 4px 0 #047857; }
        .btn-opt-false { background: #EF4444; color: white; border: none; box-shadow: 0 4px 0 #991B1B; }  

        .ans-btn:disabled { opacity: 0.8; cursor: not-allowed; transform: none !important; }
        /* Dim unselected buttons when disabled */
        .answers-grid:has(.ans-btn:disabled) .ans-btn:not(.correct):not(.wrong) {
            opacity: 0.5;
        }

        /* --- CONTROLS & OVERLAYS --- */
        .top-right-controls {
            position: absolute; top: 20px; right: 20px; z-index: 100;
            display: flex; gap: 10px; pointer-events: auto;
        }
        .btn-control-orange {
            width: 44px; height: 44px; background: var(--orange-btn); 
            border-radius: 12px; border: none; color: white;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.4);
            transition: all 0.2s;
        }
        .btn-control-orange:hover { transform: scale(1.05); }
        .btn-control-orange:active { transform: scale(0.95); }

        .top-left-controls {
            position: absolute; top: 20px; left: 20px; z-index: 100;
            display: flex; gap: 10px; pointer-events: auto;
        }
        .btn-control-red {
            width: 44px; height: 44px; background: #EF4444; 
            border-radius: 12px; border: none; color: white;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
            transition: all 0.2s;
        }
        .btn-control-red:hover { transform: scale(1.05); }
        .btn-control-red:active { transform: scale(0.95); }

        /* GAME OVERLAY (Base) */
        .game-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 248, 240, 0.85); 
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            z-index: 2000; display: none; flex-direction: column; 
            align-items: center; justify-content: center;
        }
        /* PAUSE OVERLAY (Modern Dark) */
        #pause-overlay {
            background: rgba(15, 23, 42, 0.9);
        }
        #pause-overlay .overlay-title {
            color: #F8FAFC; text-shadow: 0 0 20px rgba(255,255,255,0.2);
            font-size: 42px; letter-spacing: 4px;
        }
        
        #exit-confirm-modal { z-index: 3000; }
        .game-overlay.active { display: flex; animation: fadeIn 0.3s; }
        
        .overlay-title { font-size: 32px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; text-align: center; }
        .btn-large-start {
            padding: 15px 40px; font-size: 20px; font-weight: 700; color: white;
            background: var(--orange-btn); border: none; border-radius: 50px;
            cursor: pointer; box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
            transition: all 0.2s; display: flex; align-items: center; gap: 10px;
        }
        .btn-large-start:hover { transform: scale(1.05); }

        .countdown-frame {
            width: 120px; height: 120px; border-radius: 50%; background: white;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px;
        }
        .countdown-val { font-size: 60px; font-weight: 900; color: var(--orange-btn); }
             opacity: 0.5;
        }

        /* CAPSULE SUBMIT BUTTON */

        .btn-capsule {
            background: var(--orange-btn);
            color: white; border: none;
            border-radius: 50px; /* Capsule */
            padding: 10px 20px;
            font-size: 14px; font-weight: 700;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 3px 0 #C2410C;
            transition: transform 0.1s;
            margin-top: 5px;
            position: relative; z-index: 20; /* Ensure Clickable */
        }
        .btn-capsule:active { transform: translateY(2px); box-shadow: none; }
        .btn-capsule:disabled { background: #9CA3AF; box-shadow: none; }
        .btn-capsule.hidden { display: none; } /* For Numpad Logic */

        /* SHORT ANSWER INPUT (Neutral Style) */
        .short-answer-display {
            width: 100%;
            padding: 12px 20px;
            font-size: 20px;
            font-weight: 700;
            color: #334155;
            background: #F8FAFC;
            border: 2px solid #E2E8F0;
            border-radius: 16px;
            text-align: center;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.03); 
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            margin-bottom: 10px;
            min-height: 52px; /* Fixed height to match standard input */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer; /* Interaction hint */
            position: relative;
        }
        .short-answer-display.active {
            background: #FFFFFF;
            border-color: var(--orange-btn);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.15);
        }
        .short-answer-display.disabled {
             opacity: 0.8;
             cursor: not-allowed;
        }

        /* Cursor Blink Animation */
        .cursor-blink {
            display: inline-block; width: 2px; height: 24px; background: #334155;
            animation: blink 1s infinite;
            margin-left: 2px;
            vertical-align: middle;
        }
        @keyframes blink { 50% { opacity: 0; } }

        /* --- VIRTUAL KEYBOARD --- */
        .vk-container {
            margin-top: 10px; display: flex; flex-direction: column; gap: 4px; width: 100%;
            background: rgba(0,0,0,0.03); padding: 6px; border-radius: 12px;
        }
        .vk-row { display: flex; justify-content: center; gap: 4px; width: 100%; }
        .vk-btn {
            flex: 1; padding: 8px 0; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px;
            font-weight: 600; color: var(--text-main); box-shadow: 0 2px 0 rgba(0,0,0,0.05);
            font-size: 14px; text-align: center; min-width: 32px; min-height: 38px;
            display: flex; align-items: center; justify-content: center;
        }
        .vk-btn:active { transform: translateY(2px); box-shadow: none; background: #f1f5f9; }
        .vk-btn.wide { flex: 1.5; font-size: 14px; background: #FFEEEE; color: #DC2626; border-color: #FECACA; }
        .vk-btn.space { flex: 5; }
        .vk-btn.space-row-btn { font-weight: 700; border-radius: 8px; }
        .vk-btn.submit-key { background: #10B981; color: white; border: none; }
        
        .vk-numpad {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
            max-width: 300px; margin: 10px auto; width: 100%;
            padding: 8px; background: rgba(0,0,0,0.03); border-radius: 12px;
        }
        .vk-numpad .vk-btn { font-size: 24px; height: 55px; }

    </style>
</head>
<body>
    
    <div class="admin-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- CONTROLS LEFT (EXIT) -->
    <div class="top-left-controls">
        <button class="btn-control-red" onclick="showExitConfirmation()" title="Keluar">
            <i data-feather="x"></i>
        </button>
    </div>

    <!-- CONTROLS RIGHT -->
    <div class="top-right-controls">
        <button class="btn-control-orange" onclick="togglePauseGame()">
            <i data-feather="pause" id="icon-pause-btn"></i>
        </button>
        <button class="btn-control-orange" onclick="toggleFullscreenGame()">
            <i data-feather="maximize" id="icon-fs-btn"></i>
        </button>
    </div>

    <!-- START OVERLAY -->
    <div id="start-overlay" class="game-overlay">
        <div class="overlay-title">SIAP BERMAIN?</div>
        <button class="btn-large-start" onclick="startGameOverlay()">
            <i data-feather="play"></i> MULAI
        </button>
    </div>

    <!-- PAUSE/COUNTDOWN OVERLAY (Modern Design) -->
    <div id="pause-overlay" class="game-overlay">
        <div id="pause-content-static" style="display:flex; flex-direction:column; align-items:center; gap:20px;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="width:50px; height:50px; background:var(--orange-btn); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(249, 115, 22, 0.4);">
                    <i data-feather="pause" style="color:white; width:24px; height:24px;"></i>
                </div>
                <div class="overlay-title" style="color:white; font-size:42px; letter-spacing:3px; margin:0; font-weight:900;">DIJEDA</div>
            </div>
            <button class="btn-large-start" onclick="resumeGameOverlay()" style="margin-top:10px; background: var(--orange-btn); color: white; border:none; box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);">
                <i data-feather="play"></i> LANJUTKAN
            </button>
        </div>
        <div id="pause-content-countdown" style="display:none; flex-direction:column; align-items:center; gap:20px;">
             <div class="countdown-frame"><span class="countdown-val" id="resume-count">3</span></div>
             <div class="overlay-title" style="color:white; font-size:28px;">BERSIAP...</div>
        </div>
    </div>

    <!-- Exit Confirmation Modal (Added) -->
    <div id="exit-confirm-modal" class="modal-overlay">
        <div class="modal-content" style="max-width:400px; text-align:center;">
            <div style="font-size:60px; margin-bottom:10px;">⚠️</div>
            <h2 class="modal-title" style="color:#DC2626; margin-bottom:10px;">AKHIRI PERMAINAN?</h2>
            <p style="margin-bottom:25px; font-weight:500; color:#4B5563; line-height:1.5;">
                Progres permainan ini <b>tidak akan disimpan</b> di riwayat dan Anda akan kembali ke menu utama.
            </p>
            <div class="modal-actions">
                <button class="btn-modal secondary" onclick="cancelExit()">Batal</button>
                <button class="btn-modal primary" style="background:#DC2626;" onclick="finalizeExit()">Keluar</button>
            </div>
        </div>
    </div>

    <div id="game-container"></div> <!-- Phaser takes top 35% -->

    <!-- Game Over Modal -->
    <div id="game-over-modal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="modal-title">PERMAINAN SELESAI!</h2>
            
            <div class="winner-display">
                <div id="winner-icon">🏆</div>
                <div id="winner-text">TIM MERAH MENANG!</div>
                <div id="win-reason" style="font-size: 14px; opacity: 0.8; margin-top: 5px;">Menang KO!</div>
            </div>

            <div class="final-scores">
                <div class="score-card red">
                    <div class="sc-label">MERAH</div>
                    <div class="sc-val" id="final-score-red">0</div>
                </div>
                <div class="vs-badge">VS</div>
                <div class="score-card blue">
                    <div class="sc-label">BIRU</div>
                    <div class="sc-val" id="final-score-blue">0</div>
                </div>
            </div>

            <div class="modal-actions">
                <a href="{{ route('game.setup') }}" class="btn-modal secondary">Menu Utama</a>
                <button id="btn-play-again" class="btn-modal primary">Main Lagi</button>
                <a id="btn-review" href="{{ route('game.review', $session->id) }}" class="btn-modal success" style="background:#10B981; color:white; border:none;">Review</a>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(8px);
            z-index: 1000; display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s;
        }
        .modal-overlay.show { display: flex; opacity: 1; }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px; border-radius: 30px;
            text-align: center; width: 90%; max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid white;
        }
        .modal-overlay.show .modal-content { transform: scale(1); }

        .modal-title {
            font-size: 24px; font-weight: 800; color: #2D3142; margin-bottom: 20px; letter-spacing: -1px;
        }

        .winner-display { margin-bottom: 30px; }
        #winner-icon { font-size: 60px; margin-bottom: 10px; animation: bounce 1s infinite; }
        #winner-text { font-size: 28px; font-weight: 900; color: #F59E0B; text-transform: uppercase; line-height: 1.2; }
        
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        
        @keyframes toastSlideIn {
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .final-scores {
            display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 30px;
        }
        .score-card {
            background: #F3F4F6; padding: 15px 25px; border-radius: 16px; min-width: 100px;
        }
        .score-card.red { background: #FEF2F2; color: #B91C1C; border: 2px solid #FECACA; }
        .score-card.blue { background: #EFF6FF; color: #1E40AF; border: 2px solid #BFDBFE; }
        
        .sc-val { font-size: 32px; font-weight: 800; line-height: 1; }
        .sc-label { font-size: 12px; font-weight: 700; opacity: 0.7; }

        .vs-badge { font-weight: 900; color: #9CA3AF; font-size: 14px; }

        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn-modal {
            padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer; border: none; font-size: 16px; text-decoration: none;
            transition: transform 0.1s;
        }
        .btn-modal:active { transform: scale(0.95); }
        .btn-modal.primary { background: #F97316; color: white; box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3); }
        .btn-modal.secondary { background: #E5E7EB; color: #374151; }
    </style>

    <!-- HUD (Score & Timer) Top Center -->
    <div class="hud-container">
        <!-- Removed Exit Button from here -->

        <!-- Red Score -->
        <div class="score-pill color-red">
            <div class="score-label">MERAH</div>
            <div class="score-val" id="scoreRed">0</div>
        </div>

        <!-- Timer -->
        <div class="timer-box">
            <div class="timer-val" id="timerDisplay">--:--</div>
            <span class="timer-label">WAKTU</span>
        </div>

        <!-- Blue Score -->
        <div class="score-pill color-blue">
            <div class="score-label">BIRU</div>
            <div class="score-val" id="scoreBlue">0</div>
        </div>
    </div>

    <!-- UI Layer (Bottom Half) -->
    <div id="ui-layer">
        <!-- RED TEAM UI -->
        <div class="team-zone team-red-zone">
            <div id="q-card-red" class="question-card">
                <div class="q-content">
                    <div class="q-img-wrapper" id="img-wrapper-red">
                        <img id="img-red" class="q-img" src="" alt="Soal" loading="lazy">
                    </div>
                    <div class="q-text" id="q-text-red">Loading...</div>
                </div>
                <div class="answers-grid" id="ans-grid-red"></div>
            </div>
        </div>

        <!-- BLUE TEAM UI -->
        <div class="team-zone team-blue-zone">
            <div id="q-card-blue" class="question-card">
                <div class="q-content">
                    <div class="q-img-wrapper" id="img-wrapper-blue">
                        <img id="img-blue" class="q-img" src="" alt="Soal" loading="lazy">
                    </div>
                    <div class="q-text" id="q-text-blue">Loading...</div>
                </div>
                <div class="answers-grid" id="ans-grid-blue"></div>
            </div>
        </div>
    </div>

    <script id="game-data-payload" type="application/json">
        @json($gameDataPayload)
    </script>
    <script>
        // Init Global Data (Parsed in initGamePage from DOM)

        // --- PHASER SCENE ---
        window.TugWarScene = class extends Phaser.Scene {
            constructor() {
                super({ key: 'TugWarScene' });
                window.tugWarScene = this;
            }

            preload() {
                this.load.image('char', '/assets/images/character_new.png');
            }

            create() {
                const w = this.scale.width;
                const h = this.scale.height;
                const cy = h * 0.65; // Raised slightly to prevent clipping

                // Static Center Line (Vertical dashed line only)
                const line = this.add.graphics();
                line.lineStyle(2, 0x000000, 0.1); 
                line.beginPath();
                line.moveTo(w/2, h * 0.1);
                line.lineTo(w/2, h);
                line.strokePath();

                // State
                this.tugOffset = 0; 
                this.gameOver = false;
                
                // MAIN SPRITE (Contains both teams pulling)
                this.mainChar = this.add.sprite(w / 2, cy, 'char');
                this.mainChar.setOrigin(0.5, 0.5);
                this.mainChar.setScale(0.22); // Slightly larger

                // Initial Position
                this.updatePosition();
            }

            pull(team) {
                if(this.gameOver) return;

                const force = 20; // Movement per correct answer
                
                // Red pulls to Left (-), Blue pulls to Right (+)
                if (team === 'red') this.tugOffset -= force;
                else this.tugOffset += force;

                this.animateMove();
                this.checkWinCondition();
            }

            checkWinCondition() {
                // If sprite moves too far Left (-200), Red Wins
                if (this.tugOffset <= -200) {
                    this.finishGame('red');
                }
                // If sprite moves too far Right (+200), Blue Wins
                else if (this.tugOffset >= 200) {
                    this.finishGame('blue');
                }
            }

            finishGame(winner) {
                this.gameOver = true;
                if(window.currentGame) {
                    window.currentGame.showGameOver(winner, 'KO! Lawan Ditarik Keluar');
                }
            }

            animateMove() {
                const w = this.scale.width;
                const targetX = (w / 2) + this.tugOffset;

                this.tweens.add({
                    targets: this.mainChar,
                    x: targetX,
                    duration: 300,
                    ease: 'Power2'
                });
            }

            updatePosition() {
                const w = this.scale.width;
                this.mainChar.x = (w / 2) + this.tugOffset;
            }
        }

        window.TugWarGame = class {
            constructor() {
                if(!window.gameData || !window.gameData.config) {
                    console.error("GameData incomplete"); return;
                }

                // Initialize state from server data (resume logic)
                this.state = {
                    red: { 
                        index: window.gameData.resume.redIndex, 
                        score: window.gameData.session.team_red_score, 
                        finished: false 
                    },
                    blue: { 
                        index: window.gameData.resume.blueIndex, 
                        score: window.gameData.session.team_blue_score, 
                        finished: false 
                    },
                    isPlaying: false,
                    timeLeft: window.gameData.config.duration
                };
                this.timerInterval = null;
                
                // Prioritize UI Logic
                this.init();
                
                // Init Phaser with safety delay (for FS transition)
                setTimeout(() => {
                    try {
                        this.initPhaser();
                    } catch(e) { console.error("Phaser Init Failed", e); }
                }, 200);
            }

            initPhaser() {
                if (window.phaserGame) {
                    window.phaserGame.destroy(true);
                }
                const config = {
                    type: Phaser.AUTO,
                    parent: 'game-container',
                    width: window.innerWidth,
                    height: window.innerHeight * 0.35,
                    transparent: true,
                    scene: window.TugWarScene,
                    scale: { mode: Phaser.Scale.RESIZE, autoCenter: Phaser.Scale.CENTER_BOTH }
                };
                window.phaserGame = new Phaser.Game(config);
            }


            init() {
                this.state.isPlaying = true;
                this.startTimer();
                
                // Update UI scores on load/resume
                this.updateScore('red');
                this.updateScore('blue');

                // IMMEDIATELY ACTIVATING SESSION
                // This ensures timer counts even if user refreshes before answering
                if (window.gameData.session.status === 'waiting') {
                     this.saveToBackend('active'); 
                     window.gameData.session.status = 'active'; // Local update
                }

                if(typeof feather !== 'undefined') feather.replace();
                this.renderQuestion('red');
                this.renderQuestion('blue');
            }

            renderQuestion(team) {
                const data = window.gameData;
                const set = team === 'red' ? data.questionsRed : data.questionsBlue;
                const state = this.state[team];
                
                // Get DOM Elements
                const cardElement = document.getElementById(`q-card-${team}`);
                const qText = document.getElementById(`q-text-${team}`);
                const ansDiv = document.getElementById(`ans-grid-${team}`);
                const imgWrapper = document.getElementById(`img-wrapper-${team}`);
                const imgEl = document.getElementById(`img-${team}`);
                
                // Cleanup PGC Button if exists
                if(cardElement) {
                    const oldBtn = cardElement.querySelector('.pgc-submit-btn');
                    if(oldBtn) oldBtn.remove();
                }
                
                // 1. Check Data Availability
                if (!set || set.length === 0) {
                    if(qText) qText.innerText = "No Data";
                    return;
                }

                // 2. Check Game Over / Finished State
                if (state.index >= set.length) {
                    if(qText) qText.innerText = "Selesai! Menunggu lawan...";
                    if(ansDiv) ansDiv.innerHTML = "";
                    if(imgWrapper) imgWrapper.style.display = 'none';
                    state.finished = true;
                    state.finishedAt = Date.now();

                    // Check for Perfect Score & Fastest Win
                    const maxScore = set.reduce((acc, curr) => acc + (curr.points || 10), 0);
                    if (state.score >= maxScore) {
                        this.showGameOver(team, 'Menang Sempurna & Tercepat!');
                        return;
                    }

                    // Check if BOTH finished
                    if (this.state.red.finished && this.state.blue.finished) {
                        this.showGameOver(null, 'Semua Soal Terjawab!');
                    }
                    return;
                }

                const q = set[state.index];
                
                // 3. AUTO LAYOUT LOGIC (Grid Mode)
                const hasImage = !!q.image_url;
                const isLongText = q.question_text && q.question_text.length > 150; // Lower threshold to 150
                const type = q.question_type || 'multiple_choice';
                const isInputType = type === 'short_answer' || type === 'isian_singkat';

                if (cardElement) {
                    // Force Grid Mode if: Image exists, Long Text, OR Input Type (Keyboard needs space)
                    if (hasImage || isLongText || isInputType) {
                        cardElement.classList.add('grid-mode');
                    } else {
                        cardElement.classList.remove('grid-mode');
                    }
                }
                
                // Utility class for CSS targeting
                if(ansDiv) ansDiv.classList.add('answer-area-wrapper');

                // 4. RENDER TEXT
                if(qText) qText.innerHTML = q.question_text || "Error"; 
                
                // 5. RENDER IMAGE
                if (q.image_url) {
                    let src = q.image_url;
                    if (!src.startsWith('http') && !src.startsWith('/')) {
                        src = "/storage/" + src;
                    }
                    if(imgEl) {
                        imgEl.src = src;
                        imgEl.onerror = () => { if(imgWrapper) imgWrapper.style.display = 'none'; };
                    }
                    if(imgWrapper) imgWrapper.style.display = 'flex';
                } else {
                    if(imgWrapper) imgWrapper.style.display = 'none';
                }

                // 6. RENDER ANSWERS
                if(!ansDiv) return;
                ansDiv.innerHTML = '';
                
                // Reset Grid Style
                ansDiv.style.display = 'grid';
                ansDiv.style.gridTemplateColumns = '1fr 1fr';
                ansDiv.style.gap = '8px';

                // const type = q.question_type || 'multiple_choice'; // Removed duplicate declaration

                if (type === 'multiple_choice' || type === 'pilihan_ganda') {
                    let choices = [];
                    if (Array.isArray(q.options)) {
                         choices = [...q.options]; 
                    } else if (q.options && typeof q.options === 'object') {
                         choices = Object.keys(q.options).map(k => ({ key: k, text: q.options[k] }));
                    }
                    
                    choices.sort(() => Math.random() - 0.5);

                    choices.forEach((opt, idx) => {
                        const btn = document.createElement('button');
                        btn.className = `ans-btn btn-opt-${idx % 4}`;
                        btn.innerHTML = opt.text || opt.value;
                        btn.onclick = () => this.handleAnswer(team, btn, opt.key || opt.id, q.correct_answer);
                        ansDiv.appendChild(btn);
                    });

                } else if (type === 'true_false' || type === 'benar_salah') {
                    const opts = [
                        { key: 'true', text: 'BENAR', cls: 'btn-opt-true' }, 
                        { key: 'false', text: 'SALAH', cls: 'btn-opt-false' } 
                    ];
                    opts.forEach(opt => {
                        const btn = document.createElement('button');
                        btn.className = `ans-btn ${opt.cls}`;
                        btn.style.fontWeight = '800';
                        btn.style.justifyContent = 'center';
                        btn.innerText = opt.text;
                        btn.onclick = () => this.handleAnswer(team, btn, opt.key, q.correct_answer);
                        ansDiv.appendChild(btn);
                    });

                } else if (type === 'short_answer' || type === 'isian_singkat') {
                    ansDiv.style.display = 'flex';
                    ansDiv.style.flexDirection = 'column';
                    ansDiv.style.gap = '8px';

                    const input = document.createElement('div');
                    input.className = 'short-answer-display active';
                    input.dataset.val = ''; 
                    
                    const cursor = document.createElement('span');
                    cursor.className = 'cursor-blink';
                    input.appendChild(cursor);

                    // Helper to toggle submit button state
                    const updateSubmitState = (val) => {
                        const hasVal = val && val.trim().length > 0;
                        // Find buttons in numpad/keyboard if they exist
                        const numpadOk = keyboardContainer.querySelector('.vk-btn-ok');
                        const kbSubmit = keyboardContainer.querySelector('.vk-btn-submit');
                        
                        [numpadOk, kbSubmit].forEach(b => {
                            if(b) {
                                if(hasVal) {
                                    b.style.opacity = '1';
                                    b.style.background = '#10B981';
                                    b.dataset.disabled = 'false';
                                } else {
                                    b.style.opacity = '0.5';
                                    b.style.background = '#9CA3AF';
                                    b.dataset.disabled = 'true';
                                }
                            }
                        });
                    };

                    Object.defineProperty(input, 'value', {
                        get() { return this.dataset.val; },
                        set(v) {
                            if(this.disabled) return; 
                            this.dataset.val = v;
                            this.innerText = v;
                            this.appendChild(cursor);
                            updateSubmitState(v);
                        }
                    });

                    // Prevent native keyboard on click
                    input.onclick = (e) => {
                        e.preventDefault();
                        if(!input.classList.contains('disabled')) {
                            input.style.borderColor = 'var(--orange-btn)';
                        }
                    };

                    Object.defineProperty(input, 'disabled', {
                        get() { return this.classList.contains('disabled'); },
                        set(v) {
                            if(v) {
                                this.classList.add('disabled');
                                this.classList.remove('active');
                                if(cursor.parentNode === this) this.removeChild(cursor);
                            } else {
                                this.classList.remove('disabled');
                                this.classList.add('active');
                                this.appendChild(cursor);
                            }
                        }
                    });

                    const keyboardContainer = document.createElement('div');

                    const btn = document.createElement('button');
                    btn.innerText = 'Kirim Jawaban';
                    btn.className = 'btn-capsule'; 
                    btn.onclick = () => {
                        this.handleAnswer(team, input, input.value, q.correct_answer); 
                    };

                    ansDiv.appendChild(input);
                    ansDiv.appendChild(keyboardContainer);
                    ansDiv.appendChild(btn); 

                    const isNumeric = /^\d+$/.test(q.correct_answer || '');
                    if (isNumeric) {
                        btn.classList.add('hidden');
                        this.renderNumpad(keyboardContainer, input);
                    } else {
                        btn.classList.add('hidden');
                        this.renderFullKeyboard(keyboardContainer, input, () => this.handleAnswer(team, input, input.value, q.correct_answer));
                    }

                } else if (type === 'multiple_answer' || type === 'pilihan_ganda_kompleks') {
                    let choices = [];
                    if (q.options && q.options.choices) {
                        choices = q.options.choices;
                    } else if (Array.isArray(q.options)) {
                        choices = q.options;
                    }

                    choices = [...choices].sort(() => Math.random() - 0.5);

                    const selectedKeys = new Set();

                    choices.forEach((opt, idx) => {
                        const wrapper = document.createElement('button');
                        wrapper.className = `ans-btn btn-opt-${idx % 4}`; 
                        wrapper.style.display = 'flex';
                        wrapper.style.alignItems = 'center';
                        wrapper.style.gap = '12px';
                        wrapper.style.justifyContent = 'flex-start';
                        wrapper.style.textAlign = 'left';
                        wrapper.style.position = 'relative';
                        wrapper.style.transition = 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)';
                        
                        const iconSpan = document.createElement('span');
                        iconSpan.innerHTML = feather.icons['square'].toSvg({ width: 20, height: 20, "stroke-width": 2.5 });
                        iconSpan.style.opacity = '0.6';
                        
                        const lbl = document.createElement('span');
                        lbl.innerHTML = opt.text || opt.value;
                        lbl.style.flex = 1;
                        lbl.style.fontWeight = '600';

                        wrapper.onclick = () => {
                            const key = opt.key;
                            if (selectedKeys.has(key)) {
                                selectedKeys.delete(key);
                                wrapper.style.transform = 'none';
                                wrapper.style.filter = 'none';
                                wrapper.style.boxShadow = '0 4px 0 rgba(0,0,0,0.1)';
                                iconSpan.innerHTML = feather.icons['square'].toSvg({ width: 20, height: 20, "stroke-width": 2.5 });
                                iconSpan.style.color = 'inherit';
                                iconSpan.style.opacity = '0.6';
                            } else {
                                selectedKeys.add(key);
                                wrapper.style.transform = 'translateY(2px)';
                                wrapper.style.filter = 'brightness(0.95)';
                                wrapper.style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.1)';
                                iconSpan.innerHTML = feather.icons['check-square'].toSvg({ width: 22, height: 22, "stroke-width": 3 });
                                iconSpan.style.color = '#10B981'; 
                                iconSpan.style.opacity = '1';
                            }
                        };

                        wrapper.appendChild(iconSpan);
                        wrapper.appendChild(lbl);
                        ansDiv.appendChild(wrapper);
                    });

                    // Reuse cardElement from top scope to append button
                    if (cardElement) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'pgc-submit-btn';
                        btn.innerHTML = 'Kirim'; 
                        
                        btn.style.cssText = `
                            display: block; width: 100%; margin-top: 15px; padding: 14px 20px;
                            background: linear-gradient(135deg, #10B981, #059669); color: white;
                            border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
                            cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
                            transition: all 0.2s; position: relative; z-index: 50;
                        `;
                        
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            this.handleAnswer(team, btn, Array.from(selectedKeys), q.correct_answer || q.options.correct_answers);
                        });
                        
                        btn.addEventListener('mouseenter', () => { btn.style.transform = 'translateY(-2px)'; btn.style.boxShadow = '0 6px 20px rgba(16, 185, 129, 0.5)'; });
                        btn.addEventListener('mouseleave', () => { btn.style.transform = 'none'; btn.style.boxShadow = '0 4px 15px rgba(16, 185, 129, 0.4)'; });

                        cardElement.appendChild(btn);
                    }
                }

                // 7. RENDER KATEX MATH FORMULAS
                if (typeof renderMathInElement === 'function' && cardElement) {
                    renderMathInElement(cardElement, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false },
                            { left: '\\(', right: '\\)', display: false },
                            { left: '\\[', right: '\\]', display: true }
                        ],
                        throwOnError: false
                    });
                }
            }

            // --- KEYBOARDS ---
            renderNumpad(container, inputElement) {
                container.className = 'vk-numpad';
                const keys = ['1','2','3','4','5','6','7','8','9','⌫','0','OK'];
                keys.forEach(key => {
                    const btn = document.createElement('button');
                    btn.className = 'vk-btn';
                    btn.innerText = key;
                    if(key === 'OK') {
                        btn.className += ' vk-btn-ok'; // Marker class
                        btn.style.background = '#9CA3AF'; // Start disabled
                        btn.style.opacity = '0.5';
                        btn.style.color = 'white';
                        btn.dataset.disabled = 'true';
                        
                        btn.onclick = () => { 
                            if(btn.dataset.disabled === 'true') {
                                // Show Toast
                                const t = document.createElement('div');
                                t.innerText = 'Jawaban belum diisi!';
                                t.style.cssText = 'position:fixed; top:20px; right:20px; background:rgba(220, 38, 38, 0.9); color:white; padding:12px 24px; border-radius:12px; z-index:9999; font-weight:600; box-shadow: 0 4px 12px rgba(0,0,0,0.2); animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; display:flex; align-items:center; gap:8px;';
                                t.innerHTML = '<span>⚠️</span> Jawaban belum diisi!';
                                document.body.appendChild(t);
                                setTimeout(() => t.remove(), 2000);
                                return;
                            } 
                             // Call logic directly using the shimmed value
                             this.handleAnswer(
                                 // Infer team from container ID or passed context? 
                                 // We need to know which team this is.
                                 // Hack: Check closest team zone ID
                                 container.closest('.team-red-zone') ? 'red' : 'blue', 
                                 inputElement, // Pass the Fake Input DIV
                                 inputElement.value, // The shimmed value getter
                                 // We need the correct answer to validate. But handleAnswer expects just 3 args usually?
                                 // Wait, handleAnswer(team, element, selected, correct). We need 'correct'.
                                 // Limitation: renderNumpad doesn't know 'correct'.
                                 // Solution: Simulate click on the hidden 'Kirim' button which HAS the closure with 'correct'.
                                 null 
                             );
                             
                             // Better approach: Find the sibling hidden button and click it.
                             // The structure is: ansDiv -> [FakeInput, NumpadContainer, HiddenButton]
                             const ansDiv = container.parentElement;
                             const hiddenBtn = ansDiv.querySelector('.btn-capsule');
                             if(hiddenBtn) hiddenBtn.click();
                        };
                    } else if (key === '⌫') {
                        btn.style.background = '#FECACA'; btn.style.color = '#B91C1C';
                        btn.onclick = () => { 
                            // Support both Input and Fake Div
                            let val = inputElement.value || ''; 
                            val = val.slice(0, -1);
                            inputElement.value = val; // Trigger setter (Div) or native (Input)
                        };
                    } else {
                        btn.onclick = () => {
                            let val = inputElement.value || '';
                            inputElement.value = val + key;
                        };
                    }
                    container.appendChild(btn);
                });
            }

            renderFullKeyboard(container, inputElement, submitCallback) {
                container.className = 'vk-container';
                
                // Initialize State if not exist in this instance (or use closure var if preferred, but instance property is safer)
                // We'll attach it to the container to keep it localized or just use a local let if re-render is fully self-contained.
                // Better: Check if we have a state object attached to container, if not init.
                // Initialize State
                if (!container.kbState) {
                    container.kbState = { shift: false, caps: false };
                }

                const renderKeys = () => {
                    container.innerHTML = ''; 

                    const isUpper = container.kbState.caps || container.kbState.shift;
                    const rows = [
                        ['1','2','3','4','5','6','7','8','9','0'],
                        ['q','w','e','r','t','y','u','i','o','p'],
                        ['caps','a','s','d','f','g','h','j','k','l'],
                        ['shift','z','x','c','v','b','n','m','⌫']
                    ];

                    rows.forEach(rowKeys => {
                        const rowDiv = document.createElement('div');
                        rowDiv.className = 'vk-row';
                        
                        rowKeys.forEach(rawChar => {
                            const btn = document.createElement('button');
                            btn.className = 'vk-btn';
                            
                            let displayChar = rawChar;
                            if (rawChar.length === 1 && /[a-z]/.test(rawChar)) {
                                displayChar = isUpper ? rawChar.toUpperCase() : rawChar;
                            }
                            
                            btn.innerText = displayChar;

                            if (rawChar === 'caps') {
                                btn.className += ' wide';
                                btn.innerText = 'CAPS';
                                btn.style.fontSize = '12px';
                                if (container.kbState.caps) { 
                                    btn.style.background = '#10B981'; btn.style.color = 'white'; 
                                } else {
                                    btn.style.background = '#E2E8F0'; btn.style.color = '#475569';
                                }
                                btn.onclick = () => {
                                    container.kbState.caps = !container.kbState.caps;
                                    renderKeys();
                                };
                            }
                            else if (rawChar === 'shift') {
                                btn.className += ' wide';
                                btn.innerText = '⇧';
                                if (container.kbState.shift) { 
                                    btn.style.background = '#10B981'; btn.style.color = 'white'; 
                                } else {
                                    btn.style.background = '#E2E8F0'; btn.style.color = '#475569';
                                }
                                btn.onclick = () => {
                                    container.kbState.shift = !container.kbState.shift;
                                    renderKeys();
                                };
                            }
                            else if (rawChar === '⌫') {
                                btn.className += ' wide'; 
                                btn.style.background = '#FECACA'; 
                                btn.innerText = '⌫';
                                btn.onclick = () => {
                                    // Robust Delete
                                    let val = inputElement.value || '';
                                    val = val.slice(0, -1);
                                    inputElement.value = val;
                                };
                            }
                            else {
                                const charToInput = (rawChar.length === 1 && /[a-z]/.test(rawChar)) ? displayChar : rawChar;
                                btn.onclick = () => {
                                    // Robust Input
                                    let val = inputElement.value || '';
                                    inputElement.value = val + charToInput;
                                    
                                    if (container.kbState.shift) {
                                        container.kbState.shift = false;
                                        renderKeys();
                                    }
                                };
                            }
                            
                            rowDiv.appendChild(btn);
                        });
                        container.appendChild(rowDiv);
                    });
                    
                    const spaceRow = document.createElement('div');
                    spaceRow.className = 'vk-row';
                    
                    const spaceBtn = document.createElement('button');
                    spaceBtn.className = 'vk-btn space-row-btn';
                    spaceBtn.style.flex = 2;
                    spaceBtn.innerText = 'SPACE';
                    spaceBtn.onclick = () => {
                        let val = inputElement.value || '';
                        inputElement.value = val + ' ';
                    };
                    
                    const submitBtn = document.createElement('button');
                    submitBtn.className = 'vk-btn space-row-btn submit-key vk-btn-submit'; // Marker class
                    submitBtn.style.flex = 1;
                    submitBtn.style.background = '#9CA3AF'; // Start disabled
                    submitBtn.style.opacity = '0.5';
                    submitBtn.dataset.disabled = 'true';
                    submitBtn.innerText = 'Kirim';
                    submitBtn.onclick = () => {
                        if(submitBtn.dataset.disabled === 'true') {
                             const t = document.createElement('div');
                             t.innerText = 'Jawaban belum diisi!';
                             t.style.cssText = 'position:fixed; top:20px; right:20px; background:rgba(220, 38, 38, 0.9); color:white; padding:12px 24px; border-radius:12px; z-index:9999; font-weight:600; box-shadow: 0 4px 12px rgba(0,0,0,0.2); animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; display:flex; align-items:center; gap:8px;';
                             t.innerHTML = '<span>⚠️</span> Jawaban belum diisi!';
                             document.body.appendChild(t);
                             setTimeout(() => t.remove(), 2000);
                             return;
                        }
                        // DELEGATE TO HIDDEN BUTTON to ensure context consistency (esp 'correct' answer via closure)
                        const ansDiv = container.parentElement;
                         const hiddenBtn = ansDiv.querySelector('.btn-capsule');
                         if(hiddenBtn) hiddenBtn.click();
                    };

                    spaceRow.appendChild(spaceBtn);
                    spaceRow.appendChild(submitBtn);
                    container.appendChild(spaceRow);
                };

                renderKeys();
            }

            handleAnswer(team, element, selected, correct) {
                if (!this.state.isPlaying) return;
                
                // Prevent Double Submission (Race Condition Fix)
                if (this.state[team].processingAnswer) return;
                
                const q = (team === 'red' ? window.gameData.questionsRed : window.gameData.questionsBlue)[this.state[team].index];
                const type = q.question_type || 'multiple_choice';
                
                // Set Processing Flag immediately
                this.state[team].processingAnswer = true;
                
                let isCorrect = false;

                // Validation
                if (type === 'multiple_choice' || type === 'true_false' || type === 'benar_salah' || type === 'pilihan_ganda') {
                     const s = String(selected).toLowerCase();
                     const c = String(correct).toLowerCase();
                     if (s === c) isCorrect = true;
                     // Support Admin Codes: 'T' for True, 'F' for False
                     else if ((s === 'true' || s === 'benar') && (c === '1' || c === 'true' || c === 'benar' || c === 't')) isCorrect = true;
                     else if ((s === 'false' || s === 'salah') && (c === '0' || c === 'false' || c === 'salah' || c === 'f')) isCorrect = true;
                } 
                else if (type === 'short_answer' || type === 'isian_singkat') {
                     const userAns = String(selected).trim().toLowerCase();
                     const correctAns = String(correct).trim().toLowerCase();
                     isCorrect = userAns === correctAns; 
                     if (!isCorrect && q.options && q.options.answers) {
                         isCorrect = q.options.answers.some(ans => String(ans).trim().toLowerCase() === userAns);
                     }
                }
                else if (type === 'multiple_answer' || type === 'pilihan_ganda_kompleks') {
                     // Normalize User Input
                     const userArr = Array.isArray(selected) ? selected : [selected];
                     const userSet = new Set(userArr.map(s => String(s).trim().toUpperCase()));

                     let correctKeys = [];
                     
                     // 1. Handle Array directly
                     if (Array.isArray(correct)) {
                         correctKeys = correct;
                     } 
                     // 2. Handle String (JSON or CSV)
                     else if (typeof correct === 'string') {
                         const clean = correct.trim();
                         // Try JSON first
                         if (clean.startsWith('[') || clean.startsWith('{')) {
                             try { correctKeys = JSON.parse(clean); } 
                             catch(e) { 
                                 // If JSON fails, fallback to CSV if comma exists
                                 if(clean.includes(',')) correctKeys = clean.split(',');
                                 else correctKeys = [clean];
                             }
                         } else if (clean.includes(',')) {
                             correctKeys = clean.split(',');
                         } else {
                             correctKeys = [clean];
                         }
                     }
                     // 3. Fallback (Object keys?)
                     else if (correct && typeof correct === 'object') {
                         correctKeys = Object.values(correct);
                     }

                     const goalSet = new Set(correctKeys.map(s => String(s).trim().toUpperCase()));
                     
                     // Compare Sets
                     // console.log('[PGC Debug] User:', [...userSet], 'Goal:', [...goalSet]);
                     if (userSet.size === goalSet.size) isCorrect = [...userSet].every(key => goalSet.has(key));
                }

                // Feedback
                const grid = document.getElementById(`ans-grid-${team}`);
                if(type === 'short_answer' || type === 'isian_singkat') {
                     element.disabled = true; 
                     element.style.borderColor = isCorrect ? '#10B981' : '#EF4444';
                     element.style.background = isCorrect ? '#ECFDF5' : '#FEF2F2';
                } 
                else if (type === 'multiple_answer' || type === 'pilihan_ganda_kompleks') {
                     // Fix PGC Error: Use 'element' directly (it's the submit button)
                     if(element) {
                        element.style.background = isCorrect ? '#10B981' : '#EF4444';
                        element.disabled = true;
                     } else {
                         // Fallback just in case (e.g. key press) - find .pgc-submit-btn
                         const card = document.getElementById(`q-card-${team}`);
                         const btn = card ? card.querySelector('.pgc-submit-btn') : null;
                         if(btn) {
                             btn.style.background = isCorrect ? '#10B981' : '#EF4444';
                             btn.disabled = true;
                         }
                     }
                }
                else {
                    Array.from(grid.querySelectorAll('button')).forEach(b => b.disabled = true);
                    if (isCorrect) element.classList.add('correct');
                    else element.classList.add('wrong');
                }

                if (isCorrect) {
                     this.state[team].score += (q.points || 10);
                     this.updateScore(team);
                     this.saveToBackend('playing');
                     
                     // Trigger Phaser Animation
                     if(window.tugWarScene && window.tugWarScene.pull) {
                         window.tugWarScene.pull(team);
                     }
                }

                // SUBMIT ANSWER TO BACKEND FOR REVIEW
                try {
                    const formData = new FormData();
                    formData.append('session_id', window.gameData.session.id);
                    formData.append('team', team);
                    formData.append('question_id', q.id);
                    formData.append('is_correct', isCorrect ? 1 : 0);
                    formData.append('round_index', this.state[team].index + 1);
                    
                    let ansVal = selected;
                    if(Array.isArray(selected) || typeof selected === 'object') ansVal = JSON.stringify(selected);
                    formData.append('answer', ansVal);

                    fetch('{{ route("game.submit-answer") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: formData
                    }).catch(err => console.error(err));
                } catch(e) { console.error("Submit error", e); }

                setTimeout(() => {
                    this.state[team].index++;
                    // Release Processing Flag
                    this.state[team].processingAnswer = false;
                    this.renderQuestion(team);
                }, 1500);
            }

            updateScore(team) {
                const el = document.getElementById(team === 'red' ? 'scoreRed' : 'scoreBlue');
                if(el) el.innerText = this.state[team].score;
            }

            startTimer() {
                const sid = window.gameData.session.id;
                const key = `qgame_end_${sid}`;
                const storedEnd = sessionStorage.getItem(key);

                if (storedEnd) {
                    // Resume from storage
                    this.state.endTime = parseInt(storedEnd);
                } else {
                    // First Start
                    this.state.endTime = Date.now() + (this.state.timeLeft * 1000);
                    sessionStorage.setItem(key, this.state.endTime);
                }

                this.timerInterval = setInterval(() => {
                    if (!this.state.isPlaying || this.state.isPaused) return;

                    const now = Date.now();
                    const diff = this.state.endTime - now;
                    const newTimeLeft = Math.floor(diff / 1000);
                    
                    if (newTimeLeft !== this.state.timeLeft) {
                        this.state.timeLeft = newTimeLeft;
                        if (this.state.timeLeft < 0) this.state.timeLeft = 0;
                        this.updateTimerDisplay(this.state.timeLeft);
                    }

                    if(diff <= 0) { 
                        clearInterval(this.timerInterval); 
                        this.state.timeLeft = 0;
                        this.updateTimerDisplay(0);
                        this.endGame(); 
                    }
                }, 100);
                
                // Immediate update
                const diff = this.state.endTime - Date.now();
                this.updateTimerDisplay(Math.max(0, Math.floor(diff/1000)));
            }

            updateTimerDisplay(sec) {
               const display = document.getElementById('timerDisplay');
               if(display) {
                   let m = Math.floor(sec / 60);
                   let s = sec % 60;
                   display.innerText = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
               }
            }

            togglePause(showOverlay = true) {
                if (!this.state.isPlaying) return;

                if (this.state.isPaused) {
                   this.resumeGame();
                } else {
                   this.state.isPaused = true;
                   this.state.pausedAt = Date.now();
                   
                   // RESET UI STATE
                   document.getElementById('pause-content-static').style.display = 'flex';
                   document.getElementById('pause-content-countdown').style.display = 'none';
                   
                   if(showOverlay) document.getElementById('pause-overlay').classList.add('active'); // Only show if requested
                   
                   if(window.tugWarScene && window.tugWarScene.physics && window.tugWarScene.physics.world) {
                       window.tugWarScene.physics.world.pause();
                   }
                }
            }
            
            resumeGame() {
                if(!this.state.isPaused) return;
                this.state.isPaused = false;
                const now = Date.now();
                const pausedDuration = now - this.state.pausedAt;
                
                // Shift EndTime forward
                this.state.endTime += pausedDuration;
                const sid = window.gameData.session.id;
                sessionStorage.setItem(`qgame_end_${sid}`, this.state.endTime);
                
                document.getElementById('pause-overlay').classList.remove('active');
                if(window.tugWarScene && window.tugWarScene.physics && window.tugWarScene.physics.world) {
                       window.tugWarScene.physics.world.resume();
                }
            }

            endGame() {
                clearInterval(this.timerInterval);
                this.state.isPlaying = false;
                const sid = window.gameData.session.id;
                sessionStorage.removeItem(`qgame_end_${sid}`);
                
                // NOTE: saveToBackend is now called in showGameOver AFTER winner is determined
                this.showGameOver(null, 'Waktu Habis!');
            }

            saveToBackend(status = null, winner = null) {
                const sid = window.gameData.session.id;
                const body = {
                    session_id: sid,
                    red_score: this.state.red.score,
                    blue_score: this.state.blue.score,
                    duration: window.gameData.config.duration // Include duration for session persistence
                };
                if(status) body.status = status;
                if(winner) body.winner = winner;

                fetch('/game/api/update-session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(body)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        // Update "Main Lagi" button to use new PIN
                        const playAgainBtn = document.getElementById('btn-play-again');
                        if(playAgainBtn && data.new_lobby_pin) {
                            playAgainBtn.onclick = () => {
                                const url = `/game/play?pin=${data.new_lobby_pin}&fs_request=1`;
                                if (typeof Turbo !== 'undefined') Turbo.visit(url);
                                else window.location.href = url;
                            };
                        }
                        
                        // Update "Review" link with the history session ID
                        const reviewBtn = document.getElementById('btn-review');
                        if(reviewBtn && data.session_id) {
                            reviewBtn.href = `/game/review/${data.session_id}`;
                        }
                    }
                })
                .catch(e => console.error('Save failed:', e));
            }

            showGameOver(winner, reason) {
                this.state.isPlaying = false;
                clearInterval(this.timerInterval);
                if(window.phaserGame) window.phaserGame.destroy(true);

                const sRed = this.state.red.score;
                const sBlue = this.state.blue.score;
                
                // Calculate Max Score for "Perfect" check
                const allQs = window.gameData.questionsRed || [];
                const maxScore = allQs.reduce((acc, q) => acc + (q.points || 10), 0);

                // Determine winner if not explicit (Time / Questions ran out)
                if (!winner) {
                    if (sRed > sBlue) winner = 'red';
                    else if (sBlue > sRed) winner = 'blue';
                    else {
                        // TIE-BREAKER: Check who finished faster
                        const tRed = this.state.red.finished ? (this.state.red.finishedAt || Infinity) : Infinity;
                        const tBlue = this.state.blue.finished ? (this.state.blue.finishedAt || Infinity) : Infinity;

                        if (tRed < tBlue) {
                            winner = 'red';
                            reason = 'Seri! Unggul Waktu.';
                        } else if (tBlue < tRed) {
                            winner = 'blue';
                            reason = 'Seri! Unggul Waktu.';
                        } else {
                            winner = 'draw';
                        }
                    }
                }
                
                const isTournament = window.gameData.session.game_mode === 'tournament';

                // SAVE FINAL RESULT TO BACKEND
                this.saveToBackend('finished', winner);

                // *** TOURNAMENT MODE UI ***
                if (isTournament) {
                    const tournamentPin = window.gameData.tournamentPin || '';
                    const redName = (window.gameData.session.team_red_name || 'TIM MERAH').toUpperCase();
                    const blueName = (window.gameData.session.team_blue_name || 'TIM BIRU').toUpperCase();
                    
                    const overlay = document.getElementById('game-over-modal');
                    const content = overlay.querySelector('.modal-content');
                    
                    const winnerText = winner === 'red' ? redName + ' MENANG!' : winner === 'blue' ? blueName + ' MENANG!' : 'SERI!';
                    const winnerColor = winner === 'red' ? '#DC2626' : winner === 'blue' ? '#2563EB' : '#F59E0B';

                    content.innerHTML = `
                        <h2 class="modal-title">PERTANDINGAN SELESAI!</h2>
                        <div class="winner-display">
                            <div id="winner-icon" style="font-size:60px;">🏆</div>
                            <div id="winner-text" style="font-size:24px;font-weight:800;color:${winnerColor}; margin-top: 10px;">
                                ${winnerText}
                            </div>
                            <div style="font-size: 14px; opacity: 0.8; margin-top: 5px; color:#4B5563;">${reason}</div>
                        </div>
                        <div style="margin-top:30px; display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: center; width: 100%;">
                            <button onclick="const url='/tournament/bracket?pin=${tournamentPin}'; if(typeof Turbo!=='undefined') Turbo.visit(url); else window.location.href=url;" class="btn-large-start" style="background:var(--orange-btn); border:none; border-radius:50px; cursor:pointer; font-size: 14px; padding: 12px 10px; box-shadow: 0 6px 20px rgba(249, 115, 22, 0.3); transition: transform 0.2s; font-weight: 700; flex:1; white-space: nowrap; display:flex; justify-content:center; align-items:center;">
                                KEMBALI KE TURNAMEN
                            </button>
                            <a href="/game/review/${window.gameData.session.id}" class="btn-modal" style="background:#10B981; color:white; padding: 12px 20px; border-radius:50px; font-weight:700; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:6px; font-size: 14px; width: auto; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3); white-space: nowrap;">
                                <i data-feather="eye" style="width:16px;"></i> REVIEW
                            </a>
                        </div>
                    `;
                    overlay.classList.add('show');
                    if(typeof feather !== 'undefined') feather.replace();
                    
                    // Robust Confetti (Create Canvas Manually)
                    if(winner !== 'draw' && typeof confetti !== 'undefined') {
                        // Create dedicated canvas
                        let c = document.createElement('canvas');
                        c.style.position = 'fixed'; c.style.width = '100%'; c.style.height = '100%';
                        c.style.left = '0'; c.style.top = '0'; c.style.zIndex = '3000'; c.style.pointerEvents = 'none';
                        document.body.appendChild(c);
                        
                        let myConfetti = confetti.create(c, { resize: true });
                        const end = Date.now() + 5000;

                        (function frame() {
                            myConfetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 }, zIndex: 3001 });
                            myConfetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 }, zIndex: 3001 });
                            if (Date.now() < end) requestAnimationFrame(frame);
                            else c.remove(); // Cleanup
                        }());
                    }
                    return;
                }

                // *** NORMAL MODE: Show full game over modal ***
                document.getElementById('final-score-red').innerText = sRed;
                document.getElementById('final-score-blue').innerText = sBlue;
                document.getElementById('win-reason').innerText = reason;

                let icon = '🏆';
                let text = 'SERI!';
                let color = '#F59E0B';

                if(winner === 'red') {
                    text = 'TIM MERAH MENANG!';
                    color = '#DC2626';
                } else if(winner === 'blue') {
                    text = 'TIM BIRU MENANG!';
                    color = '#2563EB';
                } else {
                    icon = '🤝';
                }

                document.getElementById('winner-icon').innerText = icon;
                document.getElementById('winner-text').innerText = text;
                document.getElementById('winner-text').style.color = color;

                document.getElementById('game-over-modal').classList.add('show');

                // Confetti celebration
                if(winner !== 'draw' && typeof confetti !== 'undefined') {
                    try { confetti.reset(); } catch(e){} // Force Reset for Turbo
                    const end = Date.now() + 3000;
                    (function frame() {
                        confetti({
                            particleCount: 5,
                            angle: 60,
                            spread: 55,
                            origin: { x: 0 },
                            colors: ['#F97316', '#DC2626', '#2563EB', '#F59E0B'],
                            zIndex: 3000
                        });
                        confetti({
                            particleCount: 5,
                            angle: 120,
                            spread: 55,
                            origin: { x: 1 },
                            colors: ['#F97316', '#DC2626', '#2563EB', '#F59E0B'],
                            zIndex: 3000
                        });

                        if (Date.now() < end) {
                            requestAnimationFrame(frame);
                        }
                    }());
                }
            }
        }

        // --- GLOBAL HELPERS ---
        window.togglePauseGame = function() {
            if(window.currentGame) window.currentGame.togglePause();
        }
        
        window.toggleFullscreenGame = function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(e => console.log(e));
            } else {
                document.exitFullscreen();
            }
        }
        
        window.resumeGameOverlay = function() {
             document.getElementById('pause-content-static').style.display = 'none';
             document.getElementById('pause-content-countdown').style.display = 'flex';
             
             let count = 3;
             const el = document.getElementById('resume-count');
             el.innerText = count;
             
             const t = setInterval(() => {
                 count--;
                 if(count > 0) el.innerText = count;
                 else {
                     clearInterval(t);
                     if(window.currentGame) window.currentGame.resumeGame();
                     document.getElementById('pause-content-static').style.display = 'block';
                     document.getElementById('pause-content-countdown').style.display = 'none';
                 }
             }, 1000);
        }

        window.startWithCountdown = function() {
             // Reuse the pause overlay structure for clean countdown
             const pauseOverlay = document.getElementById('pause-overlay');
             const staticContent = document.getElementById('pause-content-static');
             const countContent = document.getElementById('pause-content-countdown');
             const countEl = document.getElementById('resume-count');
             
             // Ensure paused state
             if(window.currentGame && !window.currentGame.state.isPaused) {
                 window.currentGame.togglePause();
             }

             document.getElementById('start-overlay').classList.remove('active');
             
             pauseOverlay.classList.add('active');
             staticContent.style.display = 'none';
             countContent.style.display = 'flex';
             
             let count = 5;
             countEl.innerText = count;
             
             const t = setInterval(() => {
                 count--;
                 if(count > 0) countEl.innerText = count;
                 else {
                     clearInterval(t);
                     pauseOverlay.classList.remove('active');
                     if(window.currentGame) {
                         window.currentGame.resumeGame();
                         window.currentGame.saveToBackend('playing');
                     }
                     staticContent.style.display = 'block';
                     countContent.style.display = 'none';
                 }
             }, 1000);
        }

        window.startGameOverlay = function() {
             document.documentElement.requestFullscreen().catch(e => console.log(e));
             // Start countdown after fullscreen request
             window.startWithCountdown();
        }
        
        // --- EXIT LOGIC ---
        window.showExitConfirmation = function() {
            // Force pause engine BUT NO OVERLAY
            if(window.currentGame && !window.currentGame.state.isPaused) {
                 window.currentGame.togglePause(false); 
            }
            document.getElementById('exit-confirm-modal').classList.add('show');
        }
        
        window.cancelExit = function() {
            document.getElementById('exit-confirm-modal').classList.remove('show');
            // Resume game immediately if it was paused for exit
            if(window.currentGame && window.currentGame.state.isPaused) {
                 window.currentGame.resumeGame();
            }
        }
        
        window.finalizeExit = function() {
            const sid = window.gameData.session.id;
            sessionStorage.removeItem(`qgame_end_${sid}`);
            
            // Delete Session from Backend
            fetch('/game/api/cancel-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ session_id: sid })
            }).finally(() => {
                // Redirect to Setup and Reset
                const url = '/game/setup?reset=1';
                if (typeof Turbo !== 'undefined') Turbo.visit(url);
                else window.location.href = url;
            });
        }

        // Aggressive Cleanup when leaving page
        window.aggressiveCleanup = function() {
             if(window.phaserGame) {
                 try { window.phaserGame.destroy(true); } catch(e){}
                 window.phaserGame = null;
             }
             if(window.currentGame) {
                 if(window.currentGame.timerInterval) clearInterval(window.currentGame.timerInterval);
                 window.currentGame = null;
             }
             // Reset body styles to default (fix for Review page layout)
             document.body.style.overflow = '';
             document.body.style.height = ''; 
             
             if(typeof confetti !== 'undefined') {
                 try { confetti.reset(); } catch(e){}
             }
        };
        document.addEventListener('turbo:before-visit', window.aggressiveCleanup);

        // --- INIT LOGIC ---
        function initGamePage() {
             const container = document.getElementById('game-container');
             if(container) {
                 // 1. Cleanup Previous Phaser Instance
                 if(window.phaserGame) {
                     window.phaserGame.destroy(true); // true = remove canvas
                     window.phaserGame = null;
                 }
                 window.tugWarScene = null; // Reset scene reference
                 
                 // Ensure container is clean (remove any leftover canvas)
                 container.innerHTML = '';

                 // 2. Cleanup Previous Game Logic
                 if(window.currentGame) {
                     if(window.currentGame.timerInterval) clearInterval(window.currentGame.timerInterval);
                     window.currentGame = null;
                 }

                 // 3. Reset UI Elements (in case of Turbo cache)
                 const gameOverModal = document.getElementById('game-over-modal');
                 if(gameOverModal) gameOverModal.classList.remove('show');
                 
                 const pauseOverlay = document.getElementById('pause-overlay');
                 if(pauseOverlay) pauseOverlay.classList.remove('active');
                 
                 const startOverlay = document.getElementById('start-overlay');
                 if(startOverlay) startOverlay.classList.remove('active');

                 // Reset score displays
                 const scoreRed = document.getElementById('scoreRed');
                 const scoreBlue = document.getElementById('scoreBlue');
                 if(scoreRed) scoreRed.innerText = '0';
                 if(scoreBlue) scoreBlue.innerText = '0';
                 
                 // 4. Load Data from DOM (Turbo Friendly)
                 const payloadEl = document.getElementById('game-data-payload');
                 if(payloadEl) {
                     window.gameData = JSON.parse(payloadEl.textContent);
                 } else if(typeof window.gameData === 'undefined') {
                     console.error("Game Data not loaded yet. Retrying...");
                     setTimeout(initGamePage, 100); 
                     return;
                 }

                 // 5. Clear old timer only if NEW game
                 const sid = window.gameData.session.id;
                 if (window.gameData.session.status === 'waiting') {
                     sessionStorage.removeItem(`qgame_end_${sid}`);
                 }

                 // 6. Start New Game
                 if(typeof feather !== 'undefined') feather.replace();
                 
                 // Lock body for game
                 document.body.style.overflow = 'hidden';

                 setTimeout(() => {
                    try {
                        if(document.getElementById('game-container')) {
                             window.currentGame = new TugWarGame();
                        }
                        
                         // Check Startup Autos
                         const params = new URLSearchParams(window.location.search);
                         if(params.get('fs_request') === '1') {
                             const sid = window.gameData.session.id;
                             sessionStorage.removeItem(`qgame_end_${sid}`); // Force Clear "Finished" flag
                             
                             // Force Trigger Countdown Visualization
                             // We ignore current fullscreen state to ensure User sees the 5..4..3.. 
                             window.startWithCountdown();
                             
                             // Try Best-Effort Fullscreen
                             if (!document.fullscreenElement) {
                                 document.documentElement.requestFullscreen().catch(() => {});
                             }
                         }

                    } catch(e) { console.error("Game Init Error", e); }
                 }, 150);
                 
             }
        }

        document.addEventListener("DOMContentLoaded", initGamePage);
        document.addEventListener("turbo:load", () => {
             if(document.getElementById('game-container') && !window.currentGame) initGamePage();
        });

        // Global KaTeX render function for math formulas
        window.renderKaTeX = function(element) {
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(element || document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '\\[', right: '\\]', display: true}
                    ],
                    throwOnError: false
                });
            }
        };

    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Q-Game | Pengaturan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <!-- Vite Assets (Includes Turbo via app.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">
    <style>
        :root {
            --primary: #FF9B50;
            --primary-dark: #E25E3E;
            --secondary: #6C63FF;
            --bg-color: #FFF8F0;
            --surface: #FFFFFF;
            --text-main: #2D3142;
            --text-muted: #85746C;
            --border: #F0E5DB;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; user-select: none; }

        body.setup-page {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            height: 100vh;
            overflow: hidden;
            color: var(--text-main);
        }

        .bg-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: radial-gradient(circle at 10% 20%, #F3F0FF 0%, #FFF8F0 60%, #FFFFFF 100%);
        }
        .blob { position: absolute; filter: blur(80px); opacity: 0.6; animation: float 10s infinite alternate; }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: #EADDFF; }
        .blob-2 { bottom: -10%; right: -10%; width: 60vw; height: 60vw; background: #FFDBC8; }
        @keyframes float { to { transform: translate(30px, 50px); } }

        .main-layout { display: flex; height: 100%; width: 100%; position: relative; }

        .col-games {
            flex: 1; padding: 40px; display: flex; flex-direction: column;
            justify-content: flex-start; padding-top: 120px; padding-left: 10%; padding-right: 5%;
        }
        @media (max-width: 1024px) { .col-games { padding-left: 5%; padding-right: 5%; padding-top: 100px; } }

        .section-title { font-size: 24px; font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 12px; }
        .games-list { display: flex; flex-direction: column; gap: 15px; max-width: 500px; width: 100%; }

        .game-card {
            background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.8); border-radius: 16px;
            padding: 12px 20px; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; align-items: center; gap: 15px; position: relative;
        }
        .game-card:hover { transform: translateX(5px); background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        .game-card.active { background: var(--primary); border-color: var(--primary); box-shadow: 0 8px 20px rgba(255, 155, 80, 0.3); }
        .game-card.active .game-info h3, .game-card.active .game-info p { color: white !important; }
        .game-card.active .game-icon-wrapper { background: transparent; box-shadow: none; }
        .game-card.active i { color: white !important; }

        .game-icon-wrapper {
            width: 60px; height: 60px; background: transparent; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: transform 0.2s;
        }
        .game-icon { width: 55px; height: 55px; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
        .game-icon-wrapper.surprise-icon { background: linear-gradient(135deg, #A78BFA, #6D28D9); border-radius: 20px; color: #fff; font-size: 29px; box-shadow: 0 10px 24px rgba(139, 92, 246, 0.33); }

        .game-info h3 { font-size: 16px; font-weight: 600; margin-bottom: 2px; color: var(--text-main); transition: color 0.2s; }
        .game-info p { font-size: 12px; color: var(--text-muted); line-height: 1.3; margin: 0; transition: color 0.2s; }

        .col-settings {
            width: 420px; height: 100%; position: absolute; right: 0; top: 0; z-index: 100;
            transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            display: flex; flex-direction: column; pointer-events: none;
        }
        .settings-card {
            width: 100%; height: 100%; background: #FFFFFF; border-radius: 0;
            box-shadow: -5px 0 30px rgba(0,0,0,0.05); display: flex; flex-direction: column;
            pointer-events: auto; border-left: 1px solid rgba(0,0,0,0.05);
        }
        .col-settings.visible { transform: translateX(0); }

        .settings-header { background: var(--primary); padding: 25px 30px; color: white; display: flex; align-items: center; justify-content: space-between; border-bottom: none; }
        .settings-title { font-size: 20px; font-weight: 800; color: white; }
        .close-settings {
            width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2); border: none;
            color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;
        }
        .close-settings:hover { background: rgba(255,255,255,0.3); color: white; }

        .settings-content { flex: 1; padding: 30px; display: flex; flex-direction: column; gap: 24px; overflow-y: auto; }

        .input-group label {
            font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-left: 6px;
        }
        .custom-input {
            width: 100%; padding: 14px 24px;
            background: var(--primary);
            border: 2px solid transparent; border-radius: 50px;
            font-size: 16px; font-weight: 700; color: white;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 15px rgba(255, 155, 80, 0.3);
        }
        .custom-input:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 155, 80, 0.4); background: #FFAE70; }
        .custom-input.filled { background: var(--primary); border-color: transparent; }

        .custom-input i { color: white !important; }
        .custom-input-value { font-family: 'Inter', monospace; letter-spacing: 1px; color: white !important; }

        .btn-wrapper-center { display: flex; justify-content: center; padding: 0 30px 40px 30px; }
        .btn-start {
            padding: 14px 40px; min-width: 180px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white; border: none; border-radius: 50px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 10px 20px rgba(226, 94, 62, 0.2); letter-spacing: 0.5px;
        }
        .btn-start:hover { transform: scale(1.05); box-shadow: 0 15px 30px rgba(226, 94, 62, 0.3); }
        .btn-start:disabled { opacity: 0.7; cursor: wait; }

        .back-link {
            position: absolute; top: 25px; left: 25px; display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; background: white; border-radius: 50px; text-decoration: none;
            color: var(--text-main); font-weight: 600; font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.2s; z-index: 10;
        }
        .back-link:hover { transform: translateX(-4px); }

        .btn-fullscreen-floating {
            position: absolute; top: 25px; right: 25px; width: 44px; height: 44px;
            border-radius: 12px; background: white; border: none; cursor: pointer; z-index: 300;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.2s; color: var(--primary);
        }
        .btn-fullscreen-floating:hover { transform: scale(1.05); }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(4px); z-index: 2000;
            display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;
        }
        .modal-overlay.active { display: flex; opacity: 1; }

        .numpad-card {
            background: white; width: 300px; border-radius: 24px; padding: 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); transform: scale(0.95); transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.active .numpad-card { transform: scale(1); }

        .numpad-display {
            background: var(--primary); padding: 15px; border-radius: 16px; text-align: center; font-size: 28px;
            font-weight: 700; color: white; margin-bottom: 20px; letter-spacing: 2px;
            border: 2px solid transparent; box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
        }
        .numpad-display.active { border-color: rgba(255,255,255,0.5); }

        .numpad-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }

        .numpad-btn {
            height: 55px;
            border: none;
            background: white;
            font-size: 20px;
            font-weight: 500;
            color: var(--text-main);
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.1s;
            box-shadow: 0 2px 0 #E2E8F0;
            border: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .numpad-btn:active { transform: translateY(2px); box-shadow: none; }
        .numpad-btn.action { background: var(--primary); color: white; border: none; box-shadow: 0 2px 0 #E25E3E; }
        .numpad-btn.clear { color: #EF4444; background: #FEF2F2; border-color: #FECACA; box-shadow: 0 2px 0 #FECACA; }

        .toast {
            position: fixed; top: 30px; bottom: auto; left: 50%; transform: translateX(-50%) translateY(-100px);
            background: #333; color: white; padding: 12px 24px; border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); font-size: 14px; font-weight: 500; z-index: 2100; /* Higher than modal overlay(2000) */
            opacity: 0; transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: flex; align-items: center; gap: 10px;
        }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .toast.error { background: #EF4444; }
        .toast.success { background: #10B981; }

    </style>
</head>
<body class="setup-page">

    <div class="bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <!-- Turbo enabled link -->
    <a href="{{ route('game') }}" class="back-link">
        <i data-feather="arrow-left" width="16"></i> Kembali
    </a>

    <button id="btn-fullscreen-setup" class="btn-fullscreen-floating" onclick="toggleFullscreenSetup()">
        <i data-feather="maximize"></i>
    </button>

    <!-- COUNTDOWN OVERLAY -->
    <div id="countdown-overlay" class="countdown-overlay">
        <div class="countdown-circle">
            <span id="countdown-number">5</span>
        </div>
        <div class="countdown-label">BERSIAP...</div>
    </div>

    <style>
        .countdown-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 155, 80, 0.95); z-index: 2000;
            display: none; flex-direction: column; align-items: center; justify-content: center;
        }
        .countdown-overlay.active { display: flex; animation: fadeIn 0.3s; }
        .countdown-circle {
            width: 150px; height: 150px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 40px rgba(255,255,255,0.5);
            animation: pulse 1s infinite;
        }
        #countdown-number { font-size: 80px; font-weight: 800; color: var(--primary); }
        .countdown-label { font-size: 24px; color: white; margin-top: 30px; font-weight: 700; letter-spacing: 2px; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>

    <div class="main-layout">
        <div class="col-games">
            <h2 class="section-title"><i data-feather="grid" style="color:var(--primary)"></i> Pilih Permainan</h2>
            <div class="games-list">
                <div class="game-card" onclick="selectGame('tarik_tambang', this)">
                    <div class="game-icon-wrapper"><img src="{{ asset('assets/images/tug_of_war_icon.png') }}" alt="Icon" class="game-icon"></div>
                    <div class="game-info"><h3>Tarik Tambang</h3><p>Adu kecepatan antar tim.</p></div>
                    <div style="margin-left: auto;"><i data-feather="chevron-right" color="#ccc"></i></div>
                </div>
                <div class="game-card" onclick="window.location.href='{{ route('surprise.setup') }}'">
                    <div class="game-icon-wrapper surprise-icon" aria-hidden="true">🎁</div>
                    <div class="game-info"><h3>Kotak Kejutan</h3><p>Pilih kartu, jawab pertanyaan, dan kumpulkan poin bersama tim.</p></div>
                    <div style="margin-left: auto;"><i data-feather="chevron-right" color="#ccc"></i></div>
                </div>
            </div>
        </div>

        <div class="col-settings" id="settingsPanel">
            <div class="settings-card">
                <div class="settings-header">
                    <button class="close-settings" onclick="closeSettings()"><i data-feather="x" width="18"></i></button>
                    <span class="settings-title">Pengaturan Game</span>
                    <div style="width:36px;"></div>
                </div>
                <div class="settings-content">
                    <div class="input-group">
                        <label>Kode PIN Sesi</label>
                        <div class="custom-input" id="field-pin" onclick="openPinModal()">
                            <span id="display-pin" class="custom-input-value">_ _ _ _ _ _</span>
                            <i data-feather="key" width="18"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Jumlah Soal</label>
                        <div class="custom-input" id="field-questions" onclick="openQuestionModal()">
                            <span id="display-questions" class="custom-input-value">0 Soal</span>
                            <i data-feather="help-circle" width="18"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Durasi Permainan</label>
                        <div class="custom-input" id="field-duration" onclick="openDurationModal()">
                            <span id="display-duration" class="custom-input-value">00:00</span>
                            <i data-feather="clock" width="18"></i>
                        </div>
                    </div>
                </div>
                <div class="btn-wrapper-center">
                    <button id="startGameBtn" class="btn-start" onclick="startGame()">
                        <i data-feather="send" style="transform: rotate(45deg); padding-bottom:4px;"></i>
                        <span>MELUNCUR</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Numpad -->
    <div class="modal-overlay" id="numpadModal">
        <div class="numpad-card">
            <h3 id="modalTitle" style="text-align:center; margin-bottom:15px; color:#64748B; font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Input</h3>
            <div class="numpad-display" id="modalDisplay"></div>
            <div class="numpad-grid">
                <button class="numpad-btn" onclick="padInput('1')">1</button>
                <button class="numpad-btn" onclick="padInput('2')">2</button>
                <button class="numpad-btn" onclick="padInput('3')">3</button>
                <button class="numpad-btn" onclick="padInput('4')">4</button>
                <button class="numpad-btn" onclick="padInput('5')">5</button>
                <button class="numpad-btn" onclick="padInput('6')">6</button>
                <button class="numpad-btn" onclick="padInput('7')">7</button>
                <button class="numpad-btn" onclick="padInput('8')">8</button>
                <button class="numpad-btn" onclick="padInput('9')">9</button>
                <button class="numpad-btn clear" onclick="padClear()">C</button>
                <button class="numpad-btn" onclick="padInput('0')">0</button>
                <button class="numpad-btn action" onclick="padConfirm()"><i data-feather="check"></i></button>
            </div>
        </div>
    </div>

    <!-- TOAST FIXED STRUCTURE -->
    <div id="toast" class="toast">
        <span id="toastIcon" style="display:flex;"><i data-feather="info"></i></span>
        <span id="toastMessage">Notification</span>
    </div>

    <script>
        // --- GLOBAL VARIABLES (State) ---
        window.gameState = {
            gameType: null,
            pin: '',
            questions: 0,
            maxQuestions: 0, // From Backend
            isVerified: false,
            durationSec: 0,
            durationDisplay: '',
            currentMode: null,
            tempValue: ''
        };

        // --- GLOBAL HELPER DEFINITIONS ---
        if (typeof window.gameHelpers === 'undefined') {
            window.gameHelpers = {
                toggleFullscreen: function() {
                    const elem = document.documentElement;
                    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                        if (elem.requestFullscreen) elem.requestFullscreen().catch(e=>console.warn(e));
                        else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                    } else {
                        if (document.exitFullscreen) document.exitFullscreen();
                        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    }
                },
                updateFullscreenIcon: function(btnId) {
                    const btn = document.getElementById(btnId);
                    if (!btn) return;
                    const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;
                    btn.innerHTML = isFullscreen ? '<i data-feather="minimize"></i>' : '<i data-feather="maximize"></i>';
                    if (typeof feather !== 'undefined') feather.replace();
                }
            };
            document.addEventListener('fullscreenchange', () => {
                window.gameHelpers.updateFullscreenIcon('btn-fullscreen');
                window.gameHelpers.updateFullscreenIcon('btn-fullscreen-setup');
            });
        }

        window.toggleFullscreenSetup = window.gameHelpers.toggleFullscreen;

        window.selectGame = function(type, el) {
            window.gameState.gameType = type;
            document.querySelectorAll('.game-card').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('settingsPanel').classList.add('visible');
        }

        window.closeSettings = function() {
            document.getElementById('settingsPanel').classList.remove('visible');
            setTimeout(() => {
                document.querySelectorAll('.game-card').forEach(c => c.classList.remove('active'));
                window.gameState.gameType = null;
            }, 300);
        }

        window.openPinModal = function() {
            window.gameState.currentMode = 'PIN';
            window.gameState.tempValue = window.gameState.pin;
            document.getElementById('modalTitle').innerText = 'Masukkan Kode PIN';
            window.updateModalDisplay();
            window.showModal();
        }

        window.openQuestionModal = function() {
            if (!window.gameState.isVerified) {
                window.showToast('Masukkan & Verifikasi PIN terlebih dahulu!', 'error');
                return;
            }
            window.gameState.currentMode = 'QUESTIONS';
            window.gameState.tempValue = window.gameState.questions > 0 ? window.gameState.questions.toString() : '';
            document.getElementById('modalTitle').innerText = `Jumlah Soal (Max: ${window.gameState.maxQuestions})`;
            window.updateModalDisplay();
            window.showModal();
        }

        window.openDurationModal = function() {
            window.gameState.currentMode = 'DURATION';
            window.gameState.tempValue = window.gameState.durationDisplay;
            document.getElementById('modalTitle').innerText = 'Durasi (MM:SS)';
            window.updateModalDisplay();
            window.showModal();
        }

        window.padInput = function(num) {
            const mode = window.gameState.currentMode;
            if (mode === 'PIN') {
                if (window.gameState.tempValue.length < 6) window.gameState.tempValue += num;
            }
            else if (mode === 'QUESTIONS') {
                if (window.gameState.tempValue.length < 3) window.gameState.tempValue += num;
            }
            else if (mode === 'DURATION') {
                if (window.gameState.tempValue.length < 4) window.gameState.tempValue += num;
            }
            window.updateModalDisplay();
        }

        window.padClear = function() {
            window.gameState.tempValue = '';
            window.updateModalDisplay();
        }

        window.updateModalDisplay = function() {
            const display = document.getElementById('modalDisplay');
            const mode = window.gameState.currentMode;

            if (mode === 'PIN') {
                display.innerText = window.gameState.tempValue ? window.gameState.tempValue.split('').join(' ') : '_ _ _ _ _ _';
            }
            else if (mode === 'QUESTIONS') {
                display.innerText = window.gameState.tempValue || '0';
            }
            else if (mode === 'DURATION') {
                let raw = window.gameState.tempValue.padStart(4, '0');
                if (window.gameState.tempValue === '') raw = '0000';
                display.innerText = raw.substring(0,2) + ':' + raw.substring(2,4);
            }
        }

        window.showModal = function() {
            document.getElementById('numpadModal').classList.add('active');
            if (typeof feather !== 'undefined') feather.replace();
        }

        window.hideModal = function() {
            document.getElementById('numpadModal').classList.remove('active');
        }

        // --- CORE LOGIC: CONFIRM INPUT ---
        window.padConfirm = async function() {
            const mode = window.gameState.currentMode;

            if (mode === 'PIN') {
                const pin = window.gameState.tempValue;
                if (!pin || pin.length < 3) {
                    window.showToast('Kode PIN terlalu pendek', 'error');
                    return;
                }

                // --- VERIFY PIN AJAX ---
                const btn = document.querySelector('.numpad-btn.action');
                const oldIcon = btn.innerHTML;
                btn.innerHTML = '...'; btn.disabled = true;

                try {
                    // FIRST: Check if it's a Tournament PIN
                    const tournamentResponse = await fetch("{{ route('tournament.verify-pin') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ pin: pin })
                    });
                    const tournamentData = await tournamentResponse.json();

                    if (tournamentResponse.ok && tournamentData.success && tournamentData.is_tournament) {
                        // *** TOURNAMENT MODE ***
                        window.gameState.pin = pin;
                        window.gameState.isVerified = true;
                        window.gameState.isTournament = true;
                        window.gameState.tournament = tournamentData.tournament;

                        // Update PIN Display
                        const display = document.getElementById('display-pin');
                        display.innerText = pin.split('').join(' ');
                        document.getElementById('field-pin').classList.add('filled');

                        // Disable Questions & Duration fields for Tournament
                        document.getElementById('field-questions').style.opacity = '0.5';
                        document.getElementById('field-questions').style.pointerEvents = 'none';
                        document.getElementById('display-questions').innerText = 'Mode Turnamen';

                        document.getElementById('field-duration').style.opacity = '0.5';
                        document.getElementById('field-duration').style.pointerEvents = 'none';
                        document.getElementById('display-duration').innerText = 'Mode Turnamen';

                        window.showToast('🏆 PIN Mode Turnamen: ' + tournamentData.tournament.title, 'success');
                        window.hideModal();
                        return;
                    }

                    // SECOND: Check if it's a normal Game Session PIN
                    const response = await fetch("{{ route('game.verify-pin') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ pin: pin })
                    });
                    const data = await response.json();

                    if (response.ok && data.success) {
                        window.gameState.pin = pin;
                        window.gameState.isVerified = true;
                        window.gameState.isTournament = false;
                        window.gameState.session_id = data.session.id;
                        window.gameState.maxQuestions = data.session.total_questions || 0;

                        // Update UI
                        const display = document.getElementById('display-pin');
                        display.innerText = pin.split('').join(' ') + ` - ${window.gameState.maxQuestions} Soal`;
                        display.style.color = 'var(--text-main)';
                        document.getElementById('field-pin').classList.add('filled');

                        // Re-enable fields (in case previously disabled by tournament)
                        document.getElementById('field-questions').style.opacity = '1';
                        document.getElementById('field-questions').style.pointerEvents = 'auto';
                        document.getElementById('field-duration').style.opacity = '1';
                        document.getElementById('field-duration').style.pointerEvents = 'auto';

                        // Set default questions to Max
                        window.gameState.questions = window.gameState.maxQuestions;
                        document.getElementById('display-questions').innerText = window.gameState.maxQuestions + ' Soal';
                        document.getElementById('display-questions').style.color = 'var(--text-main)';
                        document.getElementById('field-questions').classList.add('filled');

                        window.showToast('Kode PIN Valid!', 'success');
                        window.hideModal();
                    } else {
                        window.showToast('Kode PIN tidak ditemukan', 'error');
                        window.gameState.isVerified = false;
                        window.gameState.isTournament = false;
                        window.gameState.pin = '';
                        document.getElementById('display-pin').innerText = '_ _ _ _ _ _';
                        document.getElementById('field-pin').classList.remove('filled');
                    }
                } catch (e) {
                    console.error(e);
                    window.showToast('Gagal memverifikasi PIN', 'error');
                } finally {
                    btn.innerHTML = oldIcon; btn.disabled = false;
                }
            }
            else if (mode === 'QUESTIONS') {
                const val = parseInt(window.gameState.tempValue || '0');

                // NEW LOGIC: Validate against Max Questions
                if (val > window.gameState.maxQuestions) {
                    window.showToast(`Jumlah soal terlalu banyak! Max: ${window.gameState.maxQuestions}`, 'error');
                    // Reset value
                    window.gameState.tempValue = window.gameState.maxQuestions.toString();
                    window.updateModalDisplay();
                    return; // Do NOT close modal
                }

                window.gameState.questions = val;
                const display = document.getElementById('display-questions');
                if (val > 0) {
                    display.innerText = val + ' Soal'; display.style.color = 'var(--text-main)';
                    document.getElementById('field-questions').classList.add('filled');
                } else {
                    display.innerText = '0 Soal'; display.style.color = '#9CA3AF';
                    document.getElementById('field-questions').classList.remove('filled');
                }
                window.hideModal();
            }
            else if (mode === 'DURATION') {
                let raw = window.gameState.tempValue.padStart(4, '0');
                let mins = parseInt(raw.substring(0,2)); let secs = parseInt(raw.substring(2,4));
                let totalSecs = (mins * 60) + secs;
                window.gameState.durationDisplay = window.gameState.tempValue;
                window.gameState.durationSec = totalSecs;

                const display = document.getElementById('display-duration');
                if (totalSecs > 0) {
                    display.innerText = raw.substring(0,2) + ':' + raw.substring(2,4); display.style.color = 'var(--text-main)';
                    document.getElementById('field-duration').classList.add('filled');
                } else {
                    display.innerText = '00:00'; display.style.color = '#9CA3AF';
                    document.getElementById('field-duration').classList.remove('filled');
                }
                window.hideModal();
            }
        }

        window.showToast = function(msg, type = 'info') {
            const t = document.getElementById('toast');
            if(!t) return;
            const iconContainer = document.getElementById('toastIcon');
            if (iconContainer) {
                if(type === 'error') iconContainer.innerHTML = '<i data-feather="alert-circle"></i>';
                else if(type === 'success') iconContainer.innerHTML = '<i data-feather="check-circle"></i>';
                else iconContainer.innerHTML = '<i data-feather="info"></i>';
            }
            t.className = 'toast show ' + type;
            document.getElementById('toastMessage').innerText = msg;
            if (typeof feather !== 'undefined') feather.replace();
            setTimeout(() => { if(t) t.classList.remove('show'); }, 3000);
        }

        window.startGame = function() {
            if (!window.gameState.gameType) return;

            // Check verification
            if (!window.gameState.isVerified) {
                window.showToast('Kode PIN belum diverifikasi!', 'error'); return;
            }

            // *** TOURNAMENT MODE: Redirect to Bracket ***
            if (window.gameState.isTournament) {
                const btn = document.getElementById('startGameBtn');
                btn.innerHTML = '<span>Memuat...</span>';
                btn.disabled = true;

                // Direct navigation with Turbo to maintain fullscreen state
                const targetUrl = "{{ route('tournament.bracket') }}?pin=" + window.gameState.pin;

                if (typeof Turbo !== 'undefined') {
                    Turbo.visit(targetUrl);
                } else {
                     window.location.href = targetUrl;
                }
                return;
            }

            // *** NORMAL MODE ***
            if (!window.gameState.session_id) {
                window.showToast('Kode PIN belum diverifikasi!', 'error'); return;
            }
            if (window.gameState.questions <= 0) {
                 window.showToast('Jumlah soal belum diatur!', 'error'); return;
            }
            if (window.gameState.durationSec <= 0) {
                 window.showToast('Durasi permainan belum diatur!', 'error'); return;
            }

            const btn = document.getElementById('startGameBtn');
            btn.innerHTML = '<span>Meluncur...</span>';
            btn.disabled = true;

            // Trigger Countdown
            const overlay = document.getElementById('countdown-overlay');
            const numEl = document.getElementById('countdown-number');
            overlay.classList.add('active');

            let count = 5;
            numEl.innerText = count;

            const timer = setInterval(() => {
                count--;
                if (count > 0) {
                    numEl.innerText = count;
                } else {
                    clearInterval(timer);

                    // Clear Previous Session Timer to ensure Fresh Start from Setup
                    if(window.gameState && window.gameState.session_id) {
                         sessionStorage.removeItem(`qgame_end_${window.gameState.session_id}`);
                    }

                    // Pass current fullscreen state
                    const fs = (document.fullscreenElement || document.webkitFullscreenElement) ? '1' : '0';

                    const params = new URLSearchParams({
                        game_mode: window.gameState.gameType,
                        pin: window.gameState.pin,
                        questions: window.gameState.questions,
                        duration: window.gameState.durationSec,
                        session_id: window.gameState.session_id,
                        fs_request: fs
                    });

                    if (typeof Turbo !== 'undefined') {
                        Turbo.visit("{{ route('game.play') }}?" + params.toString());
                    } else {
                        window.location.href = "{{ route('game.play') }}?" + params.toString();
                    }
                }
            }, 1000);
        }

        document.addEventListener("turbo:load", () => {
            // Force reset inline styles from other pages (Bracket/Game)
            document.body.style.minHeight = '';
            document.body.style.height = '';
            document.body.style.overflowY = '';
            document.body.style.overflowX = '';

            // Force Setup Layout Stability handled by CSS body.setup-page

            // Reset UI State (fix for Turbo Cache showing countdown)
            const overlay = document.getElementById('countdown-overlay');
            if(overlay) overlay.classList.remove('active');

            const btn = document.getElementById('startGameBtn');
            if(btn) {
                btn.innerHTML = '<span>Meluncur</span> <i data-feather="play"></i>';
                btn.disabled = false;
            }

            // Check for Full Reset (End Game)
            const p = new URLSearchParams(window.location.search);
            if(p.get('reset') === '1') {
                window.history.replaceState({}, '', window.location.pathname);

                // 1. Deselect Game Cards
                document.querySelectorAll('.game-card').forEach(c => c.classList.remove('active'));

                // 2. Hide Settings Panel (Mobile Toggle)
                const panel = document.getElementById('settingsPanel');
                if(panel) panel.classList.remove('active');

                // 3. Clear Input Displays
                const dPin = document.getElementById('display-pin');
                if(dPin) { dPin.innerText = '_ _ _ _ _ _'; dPin.style.color = '#9CA3AF'; document.getElementById('field-pin').classList.remove('filled'); }
                const dQ = document.getElementById('display-questions');
                if(dQ) { dQ.innerText = '0 Soal'; dQ.style.color = '#9CA3AF'; document.getElementById('field-questions').classList.remove('filled'); }
                const dDur = document.getElementById('display-duration');
                if(dDur) { dDur.innerText = '00:00'; dDur.style.color = '#9CA3AF'; document.getElementById('field-duration').classList.remove('filled'); }

                // 3.5 Re-enable fields (in case disabled by tournament mode)
                document.getElementById('field-questions').style.opacity = '1';
                document.getElementById('field-questions').style.pointerEvents = 'auto';
                document.getElementById('field-duration').style.opacity = '1';
                document.getElementById('field-duration').style.pointerEvents = 'auto';

                // 4. Show Feedback
                setTimeout(() => { if(window.showToast) window.showToast("Sesi diakhiri.", "info"); }, 300);
            }

            window.gameState = {
                gameType: null, pin: '', questions: 0, maxQuestions: 0, session_id: null, isVerified: false,
                isTournament: false, tournament: null,
                durationSec: 0, durationDisplay: '', currentMode: null, tempValue: ''
            };
            if (typeof feather !== 'undefined') feather.replace();
            window.gameHelpers.updateFullscreenIcon('btn-fullscreen-setup');

            const modal = document.getElementById('numpadModal');
            if(modal) {
                modal.addEventListener('click', (e) => {
                    if(e.target === modal) window.hideModal();
                });
            }
        });
    </script>
</body>
</html>

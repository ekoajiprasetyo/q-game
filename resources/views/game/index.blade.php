<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Q-Game | Game Edukatif Interaktif</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Vite Assets (Includes Turbo via app.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary: #FF9B50;
            --primary-dark: #E25E3E;
            --accent-purple: #B47EFF;
            --bg-color: #FFF8F0;
            --surface: #FFFFFF;
            --text-main: #2D3142;
            --text-muted: #85746C;
            --blob-1: #EADDFF;
            --blob-2: #FFDBC8;
            --blob-3: #E8DEF8;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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

        /* --- Header Buttons --- */
        .top-nav {
            position: absolute; top: 20px; right: 20px; display: flex; gap: 12px; z-index: 100;
            animation: fadeInUpBig 0.8s ease-out forwards;
        }

        .btn-icon-orange {
            width: 44px; height: 44px; background: var(--primary); color: white; border: none; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(255, 155, 80, 0.3);
        }
        .btn-icon-orange:hover { transform: scale(1.05); background: var(--primary-dark); }

        .btn-login {
            display: flex; align-items: center; gap: 8px; padding: 0 20px; height: 44px;
            background: #2ECC71; color: white; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 14px;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3); transition: all 0.2s;
        }
        .btn-login:hover { transform: translateY(-2px); background: #27AE60; }

        /* --- Content --- */
        .content-wrapper { text-align: center; z-index: 10; margin-top: -40px; }

        @keyframes fadeInUpBig { from { opacity: 0; transform: translateY(40px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes popIn { 0% { opacity: 0; transform: scale(0.5); } 60% { opacity: 1; transform: scale(1.05); } 100% { transform: scale(1); opacity: 1; } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }

        .logo-title {
            font-size: clamp(60px, 8vw, 100px); font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent-purple) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            filter: drop-shadow(0 4px 0 rgba(0,0,0,0.05)); margin-bottom: 10px; letter-spacing: -2px;
            opacity: 0; animation: fadeInUpBig 0.8s cubic-bezier(0.21, 1.11, 0.29, 0.99) 0.1s forwards;
        }

        .logo-subtitle {
            font-size: clamp(16px, 2vw, 20px); color: var(--text-muted); margin-bottom: 50px; font-weight: 400;
            opacity: 0; animation: fadeInUpBig 0.8s ease-out 0.3s forwards;
        }

        /* Play Button */
        .play-btn {
            display: inline-flex; align-items: center; gap: 15px; padding: 20px 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white; text-decoration: none; border-radius: 50px; font-size: 24px; font-weight: 700;
            box-shadow: 0 10px 25px rgba(226, 94, 62, 0.3), 0 4px 0 #BD3C1F;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;
            opacity: 0; animation: popIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s forwards, pulse 2s infinite 1.5s;
        }
        .play-btn:hover { transform: translateY(2px) scale(1.02); box-shadow: 0 5px 15px rgba(226, 94, 62, 0.4), 0 2px 0 #BD3C1F; }
        .play-btn:active { transform: translateY(4px) scale(0.98); box-shadow: none; }

        .play-icon-container { width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        .features { display: flex; gap: 15px; justify-content: center; margin-top: 50px; opacity: 0; animation: fadeInUpBig 0.8s ease-out 0.7s forwards; }
        .feature-chip {
            padding: 8px 16px; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(5px);
            border-radius: 20px; font-size: 14px; color: var(--text-main); font-weight: 500;
            display: flex; align-items: center; gap: 6px; border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;
        }
        .feature-chip:hover { transform: translateY(-3px); background: white; }

        :fullscreen body { background-color: var(--bg-color); }
    </style>
</head>
<body>

    <div class="admin-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="top-nav">
        <button id="btn-fullscreen" class="btn-icon-orange" onclick="toggleFullscreen()" title="Fullscreen">
            <i data-feather="maximize"></i>
        </button>
        <!-- Arahkan ke Dashboard. Middleware akan handle redirect jika belum login, atau langsung masuk jika sudah login (SSO) -->
        <a href="{{ route('admin.dashboard') }}" class="btn-login" data-turbo="false">
            <i data-feather="lock"></i>
            <span>Login Guru</span>
        </a>
    </div>

    <div class="content-wrapper">
        <h1 class="logo-title">Q-GAME</h1>
        <p class="logo-subtitle">Game Edukatif Interaktif untuk Kompetisi Kelas</p>

        <!-- Turbo Drive enabled by default for local links -->
        <a href="{{ route('game.setup') }}" class="play-btn">
            <div class="play-icon-container">
                <i data-feather="play" fill="white"></i>
            </div>
            <span>MULAI PERMAINAN</span>
        </a>

        <div class="features">
            <div class="feature-chip"><i data-feather="users" width="16" color="#6C63FF"></i> Kompetitif</div>
            <div class="feature-chip"><i data-feather="book-open" width="16" color="#2ECC71"></i> Edukatif</div>
            <div class="feature-chip"><i data-feather="smile" width="16" color="#FF9B50"></i> Menyenangkan</div>
        </div>
    </div>

    <div style="position: absolute; bottom: 30px; display: flex; flex-direction: column; align-items: center; gap: 8px; opacity: 0.6;">
        <i data-feather="chevrons-up"></i>
        <span style="font-size: 14px; font-weight: 500;">Sentuh tombol di atas untuk memulai</span>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1000; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>

    @if(session('error'))
        <div id="flash-error" data-message="{{ session('error') }}" style="display: none;"></div>
    @endif
    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" style="display: none;"></div>
    @endif

    <script>
        // --- GLOBAL HELPER DEFINITIONS (Run Once) ---
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
                    
                    // Set innerHTML completely new
                    if (isFullscreen) {
                        btn.innerHTML = '<i data-feather="minimize"></i>';
                    } else {
                        btn.innerHTML = '<i data-feather="maximize"></i>';
                    }
                    
                    // CRITICAL: Replace feather icons immediately after modifying DOM
                    if (typeof feather !== 'undefined') feather.replace();
                },

                showToast: function(message, type = 'error') {
                    const container = document.getElementById('toast-container');
                    if (!container) return;

                    const toast = document.createElement('div');
                    const bgColor = type === 'error' ? '#EF4444' : '#10B981';
                    const icon = type === 'error' ? 'alert-circle' : 'check-circle';
                    
                    toast.style.cssText = `
                        background: ${bgColor};
                        color: white;
                        padding: 12px 20px;
                        border-radius: 12px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-weight: 500;
                        font-size: 14px;
                        opacity: 0;
                        transform: translateY(-20px);
                        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
                        pointer-events: auto;
                        min-width: 300px;
                        justify-content: center;
                    `;
                    
                    toast.innerHTML = `
                        <i data-feather="${icon}" style="width: 18px; height: 18px;"></i>
                        <span>${message}</span>
                    `;
                    
                    container.appendChild(toast);
                    
                    // Render icon
                    if (typeof feather !== 'undefined') feather.replace();
                    
                    // Animate In
                    requestAnimationFrame(() => {
                        toast.style.opacity = '1';
                        toast.style.transform = 'translateY(0)';
                    });
                    
                    // Animate Out
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateY(-20px)';
                        setTimeout(() => toast.remove(), 300);
                    }, 4000);
                }
            };

            // Global Event Listener for Fullscreen Change (Attached once to Document)
            document.addEventListener('fullscreenchange', () => {
                // Try updating index button
                window.gameHelpers.updateFullscreenIcon('btn-fullscreen');
                // Try updating setup button (if on setup page)
                window.gameHelpers.updateFullscreenIcon('btn-fullscreen-setup');
            });
            document.addEventListener('webkitfullscreenchange', () => {
                window.gameHelpers.updateFullscreenIcon('btn-fullscreen');
                window.gameHelpers.updateFullscreenIcon('btn-fullscreen-setup');
            });
        }

        // --- PAGE SPECIFIC LOGIC (Runs on every Turbo navigation) ---
        document.addEventListener("turbo:load", () => {
            // 1. Initial Icon Render
            if (typeof feather !== 'undefined') feather.replace();

            // 2. Sync Fullscreen Icon State
            window.gameHelpers.updateFullscreenIcon('btn-fullscreen');

            // 3. Check for Flash Messages
            const errorFlash = document.getElementById('flash-error');
            if (errorFlash) {
                window.gameHelpers.showToast(errorFlash.dataset.message, 'error');
            }
            const successFlash = document.getElementById('flash-success');
            if (successFlash) {
                window.gameHelpers.showToast(successFlash.dataset.message, 'success');
            }
        });

        // Expose toggle function to global scope for onclick attributes if needed
        // but better to use event delegation or addEventListener in turbo:load
        // For simplicity with existing onclick html attributes:
        window.toggleFullscreen = window.gameHelpers.toggleFullscreen;
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Q-Game Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            /* Material You 3 Inspired Colors */
            --md-sys-color-primary: #FF9B50;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: #FFDBC8;
            --md-sys-color-on-primary-container: #341200;
            
            --md-sys-color-secondary: #E25E3E;
            --md-sys-color-secondary-container: #FFDBd0;
            
            --md-sys-color-background: #FFF8F0;
            --md-sys-color-surface: #FFFFFF;
            --md-sys-color-surface-variant: #F4DED4;
            
            --md-sys-color-outline: #85746C;
            --md-sys-color-outline-variant: #D7C2B9;
            
            --md-sys-color-error: #BA1A1A;
            
            --border-radius-capsule: 50px;
            --border-radius-large: 28px;
            --border-radius-medium: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: transparent;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            perspective: 1000px;
        }

        .login-card {
            background: var(--md-sys-color-surface);
            border-radius: var(--border-radius-large);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.06),
                        0 20px 25px -5px rgba(255, 155, 80, 0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Dual Tone Header */
        .login-header {
            background-color: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        /* Decorative curves for header */
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: var(--md-sys-color-surface);
            border-top-left-radius: 40px;
            border-top-right-radius: 40px;
        }

        .brand-logo-container {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .brand-logo-container i {
            width: 36px;
            height: 36px;
            color: var(--md-sys-color-on-primary);
            stroke-width: 2.5;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Body Section */
        .login-body {
            padding: 1.5rem 2rem 2.5rem;
            background: var(--md-sys-color-surface);
            position: relative;
            z-index: 1; /* Above the decorative header curve */
        }

        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--md-sys-color-outline);
            margin-left: 1rem;
        }

        /* Capsule Input Fields */
        .input-group {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid var(--md-sys-color-outline-variant);
            border-radius: var(--border-radius-capsule);
            font-size: 1rem;
            background-color: var(--md-sys-color-surface);
            color: #1F2937;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--md-sys-color-primary);
            background-color: #FFF9F5; /* Very subtle tint */
            padding-left: 1.75rem; /* Slight shift on focus effect */
        }

        .form-control.is-invalid {
            border-color: var(--md-sys-color-error);
            background-color: #FFF5F5;
        }

        .error-message {
            color: var(--md-sys-color-error);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            margin-left: 1rem;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--md-sys-color-outline);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .password-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--md-sys-color-primary);
        }

        /* Checkbox Capsule */
        .remember-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            margin-left: 0.5rem;
            cursor: pointer;
        }

        .custom-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--md-sys-color-outline);
            border-radius: 6px;
            background: var(--md-sys-color-surface);
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .custom-checkbox:checked {
            background-color: var(--md-sys-color-primary);
            border-color: var(--md-sys-color-primary);
        }

        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 10px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transform: translate(-50%, -50%);
        }

        /* Login Button Capsule */
        .btn-login {
            width: 100%;
            padding: 1rem;
            background-color: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            border: none;
            border-radius: var(--border-radius-capsule);
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            background-color: #E88B45; /* Darker shade */
            box-shadow: 0 8px 20px rgba(255, 155, 80, 0.4);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            color: var(--md-sys-color-outline);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: var(--border-radius-capsule);
            transition: all 0.2s;
        }

        .back-link:hover {
            background-color: var(--md-sys-color-surface-variant);
            color: var(--md-sys-color-secondary);
        }

        /* --- Animated Background Styles --- */
        .admin-bg-container {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            overflow: hidden;
            background: radial-gradient(circle at 10% 20%, #F3F0FF 0%, #FFF8F0 60%, #FFFFFF 100%);
        }

        .blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 10s infinite alternate ease-in-out;
        }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: #EADDFF; animation-delay: 0s; }
        .blob-2 { bottom: -10%; right: -10%; width: 60vw; height: 60vw; background: #FFDBC8; animation-delay: -5s; }
        .blob-3 { top: 40%; left: 40%; width: 30vw; height: 30vw; background: #E8DEF8; animation-delay: -2s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 50px) scale(1.1); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="admin-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="brand-logo-container">
                    <i data-feather="target"></i>
                </div>
                <h1 class="login-title">Q-GAME</h1>
                <p class="login-subtitle">Masuk untuk mengelola kuis dan game</p>
            </div>

            <div class="login-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group form-group-email">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                required 
                                autofocus
                            >
                        </div>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group form-group-password">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i data-feather="eye" style="width: 20px; height: 20px;"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="remember-group" for="remember">
                        <input type="checkbox" id="remember" name="remember" class="custom-checkbox" {{ old('remember') ? 'checked' : '' }}>
                        <span style="font-size: 0.9rem; color: var(--md-sys-color-outline); font-weight: 500;">Ingat Saya</span>
                    </label>

                    <button type="submit" class="btn-login">
                        <span>Masuk Dashboard</span>
                        <i data-feather="arrow-right" style="width: 20px; height: 20px;"></i>
                    </button>

                    <div style="text-align: center;" class="back-link-wrapper">
                        <a href="{{ route('game') }}" class="back-link">
                            <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
                            Kembali ke Game
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        feather.replace();

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.setAttribute('data-feather', 'eye-off');
            } else {
                passwordInput.type = 'password';
                toggleBtn.setAttribute('data-feather', 'eye');
            }
            feather.replace();
        }
    </script>
</body>
</html>

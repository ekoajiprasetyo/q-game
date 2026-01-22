<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Q-Game</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Summernote Lite (No Bootstrap needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <!-- KaTeX for Math Formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    
    <style>
        :root {
            /* Warm Color Palette - Game-like */
            --primary: #FF9B50;
            --primary-dark: #E58B45;
            --primary-light: #FFB980;
            --secondary: #FFD699;
            
            /* Team Colors */
            --team-blue: #6EC6FF;
            --team-red: #FF8A8A;
            
            /* Accent Colors */
            --accent-purple: #B47EFF;
            --accent-green: #7DCEA0;
            --accent-pink: #FF9ECD;
            --accent-yellow: #FFE066;
            
            /* Semantic */
            --success: #7DCEA0;
            --warning: #FFD166;
            --danger: #FF8A8A;
            --info: #6EC6FF;
            
            /* Neutral Tones */
            --dark: #2D3142;
            --dark-soft: #4F5D75;
            --gray: #9A9EB8;
            --light-gray: #E8EAF0;
            --cream: #FFF8F0;
            --cream-dark: #FFE5D9;
            --white: #FFFFFF;
            
            /* Shadows */
            --shadow-soft: 0 4px 20px rgba(45, 49, 66, 0.08);
            --shadow-medium: 0 8px 30px rgba(45, 49, 66, 0.12);
            --shadow-strong: 0 15px 50px rgba(45, 49, 66, 0.15);
            --shadow-glow: 0 8px 30px rgba(255, 155, 80, 0.3);
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Fix Summernote List Style (Override global reset) */
        .note-editable ul {
            list-style: disc !important;
            padding-left: 40px !important;
            margin-bottom: 1rem !important;
        }
        .note-editable ol {
            list-style: decimal !important;
            padding-left: 40px !important;
            margin-bottom: 1rem !important;
        }
        .note-editable ul li, .note-editable ol li {
            list-style-position: outside !important;
        }

        /* Fix Summernote Modal Styles (Lite Version) */
        .note-modal-content {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: none;
            overflow: hidden;
        }
        .note-modal-header {
            background: #fff8f0;
            border-bottom: 1px solid #eee;
            padding: 1rem 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .note-modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2D3142;
            margin: 0;
        }
        .note-modal-body {
            padding: 1.5rem;
        }
        .note-form-group {
            margin-bottom: 1.25rem;
        }
        .note-form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4F5D75;
        }
        .note-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #E8EAF0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .note-input:focus {
            border-color: #FF9B50;
            outline: none;
        }
        .note-btn-primary {
            background: #FF9B50 !important;
            color: white !important;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .note-btn-primary:hover {
            background: #E58B45 !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: transparent; /* Use animated bg */
            min-height: 100vh;
            color: var(--dark);
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: var(--white);
            box-shadow: var(--shadow-soft);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-glow);
        }

        .logo-icon svg {
            width: 24px;
            height: 24px;
            color: white;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dark);
        }

        .logo-text span {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent-purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Navigation */
        .nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: var(--radius-full);
            color: var(--dark-soft);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .nav-link svg {
            width: 18px;
            height: 18px;
        }

        .nav-link:hover {
            background: var(--cream);
            color: var(--dark);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-glow);
        }

        .nav-divider {
            width: 1px;
            height: 24px;
            background: var(--light-gray);
            margin: 0 0.5rem;
        }

        /* Play Button */
        .btn-play {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, var(--accent-green) 0%, #5AB890 100%);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(125, 206, 160, 0.4);
        }

        .btn-play:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(125, 206, 160, 0.5);
        }

        .btn-play svg {
            width: 18px;
            height: 18px;
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-glow);
        }

        .page-title-icon svg {
            width: 26px;
            height: 26px;
            color: white;
        }

        .page-subtitle {
            color: var(--gray);
            margin-top: 0.25rem;
            font-size: 0.95rem;
        }

        /* ===== CARDS ===== */
        .card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-medium);
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--cream);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* ===== STAT CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.orange::before { background: linear-gradient(180deg, var(--primary), var(--primary-dark)); }
        .stat-card.blue::before { background: linear-gradient(180deg, var(--team-blue), #4DA6FF); }
        .stat-card.green::before { background: linear-gradient(180deg, var(--accent-green), #5AB890); }
        .stat-card.purple::before { background: linear-gradient(180deg, var(--accent-purple), #9B5DE5); }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 28px;
            height: 28px;
            color: white;
        }

        .stat-card.orange .stat-icon { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
        .stat-card.blue .stat-icon { background: linear-gradient(135deg, var(--team-blue), #4DA6FF); }
        .stat-card.green .stat-icon { background: linear-gradient(135deg, var(--accent-green), #5AB890); }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, var(--accent-purple), #9B5DE5); }

        .stat-content {
            flex: 1;
            text-align: right;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--radius-full);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-family: inherit;
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-glow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 155, 80, 0.4);
        }

        .btn-secondary {
            background: var(--cream);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background: var(--cream-dark);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--accent-green) 0%, #5AB890 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #E57373 100%);
            color: white;
        }

        .btn-ghost {
            background: transparent;
            color: var(--dark-soft);
            padding: 0.5rem;
        }

        .btn-ghost:hover {
            background: var(--cream);
            color: var(--dark);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .btn-icon {
            padding: 0.625rem;
            border-radius: var(--radius-md);
        }

        /* ===== TABLE ===== */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
            background: var(--cream);
        }

        td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--cream);
            font-size: 0.9rem;
        }

        tr:hover td {
            background: var(--cream);
        }

        /* ===== FORMS ===== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1.25rem;
            font-size: 0.95rem;
            border: 2px solid var(--light-gray);
            border-radius: var(--radius-lg);
            background: var(--white);
            color: var(--dark);
            font-family: inherit;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 155, 80, 0.15);
        }

        .form-control::placeholder {
            color: var(--gray);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%239A9EB8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }

        /* Modern Custom Select/Dropdown System */
        .custom-dropdown {
            position: relative;
            width: 100%;
        }

        .custom-dropdown input {
            padding-right: 3rem !important;
        }

        .custom-dropdown-icon {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
            transition: var(--transition);
        }

        .custom-dropdown.active .custom-dropdown-icon {
            transform: translateY(-50%) rotate(180deg);
            color: var(--primary);
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            width: 100%;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-strong);
            z-index: 1200;
            max-height: 250px;
            overflow-y: auto;
            display: none;
            border: 1px solid var(--light-gray);
            padding: 0.5rem;
            animation: dropdownFadeIn 0.2s ease-out;
        }

        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-menu.active {
            display: block;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--dark-soft);
        }

        .dropdown-item:hover {
            background: var(--cream);
            color: var(--primary-dark);
        }

        .dropdown-item.selected {
            background: rgba(255, 155, 80, 0.1);
            color: var(--primary-dark);
            font-weight: 600;
        }

        .dropdown-no-results {
            padding: 1rem;
            text-align: center;
            color: var(--gray);
            font-size: 0.85rem;
            font-style: italic;
        }

        /* Hide browser default datalist icons */
        input::-webkit-calendar-picker-indicator {
            display: none !important;
        }

        /* ===== PROFILE DROPDOWN ===== */
        .profile-dropdown {
            position: relative;
            margin-left: 1rem;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.35rem;
            padding-right: 1rem;
            background: var(--white);
            border: 2px solid var(--cream-dark);
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: var(--transition);
        }

        .profile-trigger:hover, .profile-dropdown.active .profile-trigger {
            border-color: var(--primary);
            background: var(--cream);
        }

        .profile-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .profile-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--dark);
        }

        .profile-role {
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: capitalize;
        }

        .profile-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 200px;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--light-gray);
            padding: 0.5rem;
            display: none;
            z-index: 1000;
            animation: dropdownFadeIn 0.2s ease-out;
        }

        .profile-dropdown.active .profile-menu {
            display: block;
        }

        .profile-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark-soft);
            text-decoration: none;
            font-size: 0.9rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
            width: 100%;
            border: none;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .profile-menu-item:hover {
            background: var(--cream);
            color: var(--primary-dark);
        }

        .profile-menu-divider {
            height: 1px;
            background: var(--light-gray);
            margin: 0.5rem 0;
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-full);
        }

        .badge-orange {
            background: rgba(255, 155, 80, 0.15);
            color: var(--primary-dark);
        }

        .badge-blue {
            background: rgba(110, 198, 255, 0.2);
            color: #3A9FE7;
        }

        .badge-green {
            background: rgba(125, 206, 160, 0.2);
            color: #4A9D6E;
        }

        .badge-purple {
            background: rgba(180, 126, 255, 0.2);
            color: #9B5DE5;
        }

        .badge-blue {
            background: rgba(84, 160, 255, 0.2);
            color: #2e86de;
        }

        .badge-pink {
            background: rgba(255, 159, 243, 0.2);
            color: #f368e0;
        }

        .badge-teal {
            background: rgba(0, 206, 201, 0.2);
            color: #00cec9;
        }

        .badge-red {
            background: rgba(255, 138, 138, 0.2);
            color: #E05757;
        }

        .badge-yellow {
            background: rgba(255, 224, 102, 0.3);
            color: #C9A227;
        }

        /* ===== TOAST NOTIFICATIONS ===== */
        .toast-container {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-width: 400px;
        }

        .toast {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-medium);
            animation: slideInRight 0.3s ease, fadeOut 0.3s ease 4.7s forwards;
            position: relative;
            overflow: hidden;
        }

        .toast::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.5);
            animation: toastProgress 5s linear forwards;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes toastProgress {
            from { width: 100%; }
            to { width: 0%; }
        }

        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toast-icon svg {
            width: 20px;
            height: 20px;
            color: white;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.125rem;
        }

        .toast-message {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            opacity: 0.7;
            transition: var(--transition);
        }

        .toast-close:hover {
            opacity: 1;
        }

        .toast-close svg {
            width: 18px;
            height: 18px;
        }

        .toast-success {
            background: linear-gradient(135deg, var(--accent-green), #5AB890);
            color: white;
        }

        .toast-success .toast-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .toast-error {
            background: linear-gradient(135deg, var(--danger), #E05757);
            color: white;
        }

        .toast-error .toast-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .toast-warning {
            background: linear-gradient(135deg, var(--warning), #E5B94D);
            color: var(--dark);
        }

        .toast-warning .toast-icon {
            background: rgba(0, 0, 0, 0.1);
        }

        .toast-warning .toast-icon svg {
            color: var(--dark);
        }

        .toast-info {
            background: linear-gradient(135deg, var(--info), #4DA6FF);
            color: white;
        }

        .toast-info .toast-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--cream), var(--cream-dark));
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(45, 49, 66, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-strong);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            display: flex; /* Flexbox layout */
            flex-direction: column; /* Vertical stack */
            overflow: hidden; /* Prevent outer scroll */
            transform: scale(0.9) translateY(20px);
            transition: var(--transition);
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0; /* Fixed height */
            z-index: 10;
        }

        .modal-header.modal-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #E05757 100%);
        }

        .modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-title svg {
            width: 22px;
            height: 22px;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .modal-close svg {
            width: 18px;
            height: 18px;
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto; /* Scrollable content */
            flex: 1; /* Take remaining space */
            min-height: 0; /* Allow shrinking for scroll */
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 2px solid var(--cream);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-shrink: 0; /* Fixed height */
            background: var(--white);
            z-index: 10;
        }

        /* ===== ICON PICKER ===== */
        .icon-picker {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 0.75rem;
            background: var(--cream);
            border-radius: var(--radius-md);
            border: 2px solid var(--light-gray);
        }

        .icon-picker-item {
            width: 100%;
            aspect-ratio: 1;
            border: 2px solid transparent;
            background: var(--white);
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: var(--transition);
        }

        .icon-picker-item:hover {
            border-color: var(--primary-light);
            transform: scale(1.1);
        }

        .icon-picker-item.selected {
            border-color: var(--primary);
            background: rgba(255, 155, 80, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 155, 80, 0.2);
        }

        .icon-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .icon-preview-display {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--cream), var(--cream-dark));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border: 2px solid var(--light-gray);
        }

        /* ===== PAGINATION ===== */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.5rem;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0.5rem;
        }

        .pagination li a, .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            background: var(--cream);
            color: var(--dark-soft);
        }

        .pagination li.active span {
            background: var(--primary);
            color: white;
        }

        .pagination li.disabled span {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination li a:hover {
            background: var(--cream-dark);
            color: var(--dark);
        }

        .pagination svg {
            width: 16px;
            height: 16px;
        }

        /* Hide Laravels default responsive pagination text */
        .pagination-container nav > div:first-child {
            display: none !important;
        }
        
        .pagination-container nav > div:last-child {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .pagination-container nav p {
            font-size: 0.8rem;
            color: var(--gray);
            margin: 0;
        }

        /* ===== UTILITIES ===== */
        .text-muted { color: var(--gray); }
        .text-center { text-align: center; }
        
        .d-flex { display: flex; }
        .d-grid { display: grid; }
        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 1rem; }
        .gap-4 { gap: 1.5rem; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .justify-end { justify-content: flex-end; }
        .flex-wrap { flex-wrap: wrap; }

        .mb-0 { margin-bottom: 0; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }

        /* Grid layouts */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .hover-row:hover {
            background: var(--cream);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .topbar-container {
                padding: 0 1rem;
            }

            .nav-link span {
                display: none;
            }

            .nav-link {
                padding: 0.625rem;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stat-value {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .btn-play span {
                display: none;
            }
        }

        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            padding: 0.5rem;
            background: none;
            border: none;
            color: var(--dark);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .nav {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background: var(--white);
                flex-direction: column;
                padding: 1rem;
                box-shadow: var(--shadow-medium);
            }

            .nav.active {
                display: flex;
            }

            .nav-link {
                width: 100%;
                justify-content: flex-start;
            }

            .nav-link span {
                display: inline;
            }

            .nav-divider {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Animated Background -->
    <div class="admin-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-container">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <div class="logo-icon">
                    <i data-feather="crosshair"></i>
                </div>
                <div class="logo-text">Q-<span>Game</span></div>
            </a>

            <button class="menu-toggle" id="menuToggle">
                <i data-feather="menu"></i>
            </button>

            <nav class="nav" id="mainNav">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-feather="home"></i>
                    <span>Dashboard</span>
                </a>
                
                @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i data-feather="users"></i>
                    <span>Users</span>
                </a>
                @endif

                <a href="{{ route('admin.topics.index') }}" class="nav-link {{ request()->routeIs('admin.topics.*') ? 'active' : '' }}">
                    <i data-feather="folder"></i>
                    <span>Topik</span>
                </a>
                <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                    <i data-feather="help-circle"></i>
                    <span>Bank Soal</span>
                </a>
                <a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
                    <i data-feather="clock"></i>
                    <span>Riwayat</span>
                </a>
                <a href="{{ route('admin.tournaments.index') }}" class="nav-link {{ request()->routeIs('admin.tournaments.*') ? 'active' : '' }}">
                    <i data-feather="award"></i> <!-- Using 'award' or 'trophy' if available -->
                    <span>Turnamen</span>
                </a>

                <div class="nav-divider"></div>

                <!-- Profile Dropdown -->
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-trigger" id="profileTrigger">
                        <div class="profile-avatar">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="profile-info">
                            <span class="profile-name">{{ Auth::user()->name ?? 'User' }}</span>
                            <span class="profile-role">{{ Auth::user()->role ?? 'Guest' }}</span>
                        </div>
                        <i data-feather="chevron-down" style="width: 16px; height: 16px; color: var(--gray);"></i>
                    </div>
                    <div class="profile-menu">
                        <!--
                        <a href="#" class="profile-menu-item">
                            <i data-feather="user"></i>
                            Edit Profil
                        </a>
                        <div class="profile-menu-divider"></div>
                        -->
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="profile-menu-item" style="color: var(--danger);">
                                <i data-feather="log-out"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        @yield('content')
    </main>

    <!-- Toast Container -->
    <!-- Global Delete Confirmation Modal -->
    <div class="modal-overlay" id="confirmDeleteModal">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header modal-danger">
                <h3 class="modal-title">
                    <i data-feather="alert-triangle"></i>
                    Konfirmasi Hapus
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('confirmDeleteModal')">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body" style="text-align: center; padding: 2rem 1.5rem;">
                <div style="width: 64px; height: 64px; background: rgba(255, 138, 138, 0.1); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--danger);">
                    <i data-feather="trash-2" style="width: 32px; height: 32px;"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 0.5rem; color: var(--dark);">Apakah Anda yakin?</h4>
                <p class="text-muted" style="font-size: 0.9rem; line-height: 1.5;">Data yang dihapus tidak dapat dikembalikan. Seluruh data terkait juga mungkin akan ikut terhapus.</p>
            </div>
            <div class="modal-footer" style="background: var(--cream); border-top: none; justify-content: center; padding: 1.25rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('confirmDeleteModal')" style="min-width: 100px;">Batal</button>
                <form id="globalDeleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary" style="background: var(--danger); border-color: var(--danger); min-width: 100px;">
                        Hapus Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div class="modal-overlay" id="confirmBulkDeleteModal">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header modal-danger" style="background: var(--danger); color: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                <h3 class="modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
                    <i data-feather="trash-2"></i>
                    Hapus Terpilih
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('confirmBulkDeleteModal')" style="background: transparent; border: none; color: white; cursor: pointer;">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body" style="text-align: center; padding: 2rem 1.5rem;">
                <div style="width: 64px; height: 64px; background: rgba(255, 138, 138, 0.1); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--danger);">
                    <i data-feather="trash-2" style="width: 32px; height: 32px;"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 0.5rem; color: var(--dark);">Hapus item terpilih?</h4>
                <p class="text-muted" style="font-size: 0.9rem; line-height: 1.5;">Anda akan menghapus <span id="bulkDeleteCount" style="font-weight: 700; color: var(--danger);">0</span> item. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer" style="background: var(--cream); border-top: none; justify-content: center; padding: 1.25rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('confirmBulkDeleteModal')" style="min-width: 100px;">Batal</button>
                <button type="button" id="confirmBulkDeleteBtn" class="btn btn-primary" style="background: var(--danger); border-color: var(--danger); min-width: 100px;">
                    Hapus Semua
                </button>
            </div>
        </div>
    </div>
    
    <div class="toast-container" id="toastContainer">
        @if(session('success'))
            <div class="toast toast-success" id="successToast">
                <div class="toast-icon">
                    <i data-feather="check-circle"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">Berhasil!</div>
                    <div class="toast-message">{{ session('success') }}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()">
                    <i data-feather="x"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="toast toast-error" id="errorToast">
                <div class="toast-icon">
                    <i data-feather="alert-circle"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">Gagal!</div>
                    <div class="toast-message">{{ session('error') }}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()">
                    <i data-feather="x"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="toast toast-error" id="validationToast">
                <div class="toast-icon">
                    <i data-feather="alert-triangle"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">Validasi Gagal</div>
                    <div class="toast-message">{{ $errors->first() }}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()">
                    <i data-feather="x"></i>
                </button>
            </div>
        @endif
    </div>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                mainNav.classList.toggle('active');
            });
        }

        // Auto-remove toasts after animation
        setTimeout(() => {
            document.querySelectorAll('.toast').forEach(toast => {
                toast.remove();
            });
        }, 5000);

        // Global Modal Functions
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                setTimeout(() => feather.replace(), 10);
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function confirmDelete(actionUrl) {
            const form = document.getElementById('globalDeleteForm');
            if (form) {
                form.action = actionUrl;
                openModal('confirmDeleteModal');
            }
        }

        // Close on overlay click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                closeModal(e.target.id);
            }
        });

        // Profile Dropdown Toggle
        const profileTrigger = document.getElementById('profileTrigger');
        const profileDropdown = document.getElementById('profileDropdown');

        if (profileTrigger && profileDropdown) {
            profileTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdown.classList.toggle('active');
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!profileDropdown.contains(e.target)) {
                    profileDropdown.classList.remove('active');
                }
            });
        }

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                    closeModal(modal.id);
                });
                if (profileDropdown) profileDropdown.classList.remove('active');
            }
        });

        async function toggleGamePin(btn, topicId, materialId) {
            if (btn.dataset.pin) {
                // Sudah ada PIN, copy ke clipboard
                navigator.clipboard.writeText(btn.dataset.pin).then(() => {
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = 'Tersalin!';
                    if (typeof feather !== 'undefined') {
                        btn.innerHTML = '<i data-feather="check" style="width:14px;height:14px;margin-right:4px;"></i> Tersalin!';
                        feather.replace();
                    }
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        if (typeof feather !== 'undefined') feather.replace();
                    }, 2000);
                });
                return;
            }

            // Generate/Get PIN
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Wait...'; // Simple loading text
            
            try {
                const baseUrl = "{{ url('/') }}";
                const url = `${baseUrl}/admin/topics/${topicId}/materials/${materialId}/generate-pin`;
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    cache: 'no-store'
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();
                
                if (data.success) {
                    const pin = data.pin;
                    
                    const updateBtn = (b) => {
                        b.dataset.pin = pin;
                        b.classList.remove('btn-primary');
                        b.classList.add('btn-success');
                        b.title = "Klik untuk menyalin";
                        b.disabled = false;
                        
                        // Default text if feather fails
                        b.innerHTML = `${pin}`;
                        
                        if (typeof feather !== 'undefined') {
                            b.innerHTML = `<i data-feather="copy" style="width:14px;height:14px;margin-right:4px;"></i> ${pin}`;
                        }
                    };

                    updateBtn(btn);
                    if (typeof feather !== 'undefined') feather.replace();
                    
                    // Sync other buttons
                    document.querySelectorAll(`.pin-btn-${materialId}`).forEach(otherBtn => {
                        if (otherBtn !== btn) updateBtn(otherBtn);
                    });
                    if (typeof feather !== 'undefined') feather.replace();

                } else {
                    throw new Error(data.message || 'API Fail');
                }
            } catch (e) {
                console.error(e);
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-danger');
                btn.innerHTML = 'Err: ' + e.message; // Show error inside button
                
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-primary');
                    btn.disabled = false;
                    if (typeof feather !== 'undefined') feather.replace();
                }, 3000);
            }
        }
    </script>

    <!-- KaTeX Auto-render Initialization -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initial render
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '\\[', right: '\\]', display: true}
                    ],
                    throwOnError: false
                });
            }
        });

        // Global function to re-render KaTeX (call after dynamic content changes)
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

    @stack('scripts')
</body>
</html>

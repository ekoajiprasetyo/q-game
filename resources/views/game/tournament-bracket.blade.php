<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tournament->title }} - Turnamen</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Vite Assets (Crucial for Turbo) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">
    
    <style>
        .bracket-page {
            --primary: #FF9B50;
            --primary-dark: #E25E3E;
            --accent-green: #22C55E;
            --accent-blue: #3B82F6;
            --accent-purple: #8B5CF6;
            --accent-yellow: #F59E0B;
            --gray: #6B7280;
            --light-gray: #E5E7EB;
            --bg-color: #FFF8F0;
            --text-main: #2D3142;
            --text-muted: #85746C;
            --blob-1: #EADDFF;
            --blob-2: #FFDBC8;
            --blob-3: #E8DEF8;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-soft: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.12);
            
            /* Phase Colors */
            --phase-round1: #3B82F6;
            --phase-round2: #8B5CF6;
            --phase-semi: #F59E0B;
            --phase-final: #EF4444;
            
            /* Connector */
            --connector-color: #94A3B8;
            --connector-width: 4px; /* Increased from 2px */
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            min-height: 100vh;
            color: var(--text-main);
        }
        
        /* Animated Background */
        .bg-container {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 10% 20%, #F3F0FF 0%, #FFF8F0 60%, #FFFFFF 100%);
        }
        
        .blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 10s infinite alternate ease-in-out;
        }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: var(--blob-1); }
        .blob-2 { bottom: -10%; right: -10%; width: 60vw; height: 60vw; background: var(--blob-2); animation-delay: -5s; }
        .blob-3 { top: 40%; left: 40%; width: 30vw; height: 30vw; background: var(--blob-3); animation-delay: -2s; }
        @keyframes float { to { transform: translate(30px, 50px) scale(1.1); } }
        
        .tournament-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 6rem; /* Adjusted for better spacing */
            position: relative;
            z-index: 10; /* Ensure content is above background */
        }
        
        /* Back Button - Orange */
        .back-link {
            position: fixed;
            top: 25px; left: 25px;
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px;
            background: var(--primary);
            border-radius: 50px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(255, 155, 80, 0.3);
            transition: all 0.2s;
            z-index: 100;
        }
        .back-link:hover { 
            transform: translateX(-4px); 
            background: var(--primary-dark);
        }
        .back-link i { color: white !important; }
        
        /* Header */
        .tournament-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: rgba(255,255,255,0.85);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-soft);
            position: relative;
            z-index: 20; /* Ensure header is on top of container flow */
        }
        
        .tournament-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent-purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .tournament-header .subtitle { color: var(--text-muted); font-size: 1rem; }
        
        .pin-badge {
            display: inline-flex;
            align-items: center; gap: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            letter-spacing: 3px;
            margin-top: 1rem;
            color: white;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(255, 155, 80, 0.3);
        }
        
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            display: flex;
            align-items: center; gap: 6px;
            padding: 6px 14px;
            background: rgba(255,255,255,0.7);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .stat-item i { color: var(--primary); }
        
        /* Bracket Layout */
        .bracket-scroll {
            overflow-x: auto;
            padding: 1rem 0 2rem;
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
            min-width: 300px;
            position: relative;
            z-index: 10;
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
        
        .round-title.round-1 { background: var(--phase-round1); }
        .round-title.round-2 { background: var(--phase-round2); }
        .round-title.semi { background: var(--phase-semi); }
        .round-title.final { background: linear-gradient(135deg, var(--phase-final), #DC2626); }
        .round-title.champion { background: linear-gradient(135deg, #FFD700, #F59E0B); color: #92400E; }
        
        .round-matches {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 24px;
            min-height: 100%;
        }
        
        /* Match Card Container with Connector */
        .match-wrapper {
            position: relative;
        }
        
        /* Match Card */
        .match-card {
            background: rgba(255,255,255,0.95);
            border-radius: var(--radius-lg);
            color: var(--text-main);
            box-shadow: var(--shadow-md);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .match-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .match-card.completed { border-color: var(--accent-green); }
        
        .match-card.playable {
            border-color: var(--primary);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 155, 80, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(255, 155, 80, 0); }
        }
        
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
        
        .match-header.round-1 { background: var(--phase-round1); }
        .match-header.round-2 { background: var(--phase-round2); }
        .match-header.semi { background: var(--phase-semi); }
        .match-header.final { background: linear-gradient(135deg, var(--phase-final), #DC2626); }
        
        .match-body { padding: 0.75rem; }
        
        /* Match Info (Material & Duration) */
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
            color: var(--primary-dark);
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
            /* No green background anymore */
            background: white; 
            color: var(--text-main);
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
        
        /* Team Number Icon */
        .team-number {
            width: 26px; height: 26px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
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
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-play {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.7rem 1.5rem;
            box-shadow: 0 4px 15px rgba(255, 155, 80, 0.4);
        }
        
        .btn-play:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(255, 155, 80, 0.5);
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-green { background: rgba(34, 197, 94, 0.15) !important; color: #16A34A !important; }
        .badge-yellow { background: rgba(245, 158, 11, 0.15) !important; color: #D97706 !important; }
        .badge-gray { background: rgba(107, 114, 128, 0.15) !important; color: var(--gray) !important; }
        .badge-blue { background: rgba(59, 130, 246, 0.15) !important; color: #2563EB !important; }
        .badge-red { background: rgba(239, 68, 68, 0.15) !important; color: #DC2626 !important; }
        
        .team-badge {
            font-size: 0.6rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        /* Winner Card */
        .winner-card {
            background: linear-gradient(135deg, #FFD700, #FDB931);
            text-align: center;
            padding: 1.5rem;
            color: #92400E;
        }
        
        .winner-card .trophy {
            font-size: 3rem;
            animation: bounce 2s infinite;
        }
        
        .winner-card .name {
            font-size: 1.3rem;
            font-weight: 800;
            margin-top: 0.75rem;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1.5rem;
            color: var(--text-muted);
        }
        .footer a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tournament-container { padding: 1rem; padding-top: 6rem; }
            .tournament-header h1 { font-size: 1.6rem; }
            .bracket-wrapper { gap: 50px; }
            .bracket-round { min-width: 260px; }
            .match-wrapper::after { width: 25px; right: -25px; }
        }

        /* Fullscreen Button */
        .btn-fullscreen-floating {
            position: fixed;
            top: 25px; right: 25px;
            width: 44px; height: 44px;
            border-radius: 50px;
            background: #FF9B50 !important;
            color: white;
            border: none;
            cursor: pointer;
            z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 155, 80, 0.4);
            transition: all 0.2s;
        }
        .btn-fullscreen-floating:hover {
            transform: scale(1.05);
            background: var(--primary-dark);
            box-shadow: 0 6px 20px rgba(255, 155, 80, 0.6);
        }
    </style>
</head>
<body class="bracket-page">
    <!-- Background -->
    <div class="bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    
    <!-- Back Button (Orange) -->
    <a href="{{ route('game.setup') }}?reset=1" class="back-link">
        <i data-feather="arrow-left" width="16"></i> Kembali
    </a>

    <button id="btn-fullscreen" class="btn-fullscreen-floating" onclick="toggleFullscreen()">
        <i data-feather="maximize"></i>
    </button>
    
    <div class="tournament-container">
        <!-- Header -->
        <div class="tournament-header">
            <h1>🏆 {{ $tournament->title }}</h1>
            <p class="subtitle">{{ $tournament->description ?? 'Turnamen Tarik Tambang Edukatif' }}</p>
            <div class="pin-badge">
                <i data-feather="key" width="16"></i>
                PIN: {{ $tournament->pin }}
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i data-feather="users" width="14"></i>
                    {{ $tournament->teams->count() }} Tim
                </div>
                <div class="stat-item">
                    <i data-feather="target" width="14"></i>
                    {{ $tournament->matches->count() }} Match
                </div>
                <div class="stat-item">
                    <i data-feather="check-circle" width="14"></i>
                    {{ $tournament->matches->whereNotNull('winner_team_id')->count() }} Selesai
                </div>
            </div>
        </div>
        
        <!-- Bracket -->
        <div class="bracket-scroll">
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
                        <div class="round-title {{ $roundClass }}">
                            {{ $roundLabel }}
                        </div>
                        
                        <div class="round-matches" data-match-count="{{ $matches->count() }}">
                            @foreach($matches as $mIndex => $match)
                                @php
                                    $isPlayable = in_array($match->id, $playableMatchIds ?? [], true);
                                    $isCompleted = $match->winner_team_id != null;
                                    
                                    // Extract team number or Initial
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
                                    
                                    // Format duration
                                    $durationSecs = $match->time_per_question ?? 0;
                                    $durationMins = floor($durationSecs / 60);
                                    $durationSecRemainder = $durationSecs % 60;
                                    $durationFormatted = sprintf('%02d:%02d', $durationMins, $durationSecRemainder);
                                @endphp
                                
                                <div class="match-wrapper">
                                    <div class="match-card {{ $isCompleted ? 'completed' : '' }} {{ $isPlayable ? 'playable' : '' }}">
                                        
                                        <div class="match-header {{ $roundClass }}">
                                            <span>Match #{{ $match->match_number }}</span>
                                            @if($isCompleted)
                                                <i data-feather="check-circle" width="14"></i>
                                            @elseif($isPlayable)
                                                <i data-feather="play-circle" width="14"></i>
                                            @endif
                                        </div>
                                        
                                        <div class="match-body">
                                            <!-- Match Info: Material & Duration -->
                                            @if($match->is_ready && $match->material)
                                                <div class="match-info">
                                                    <span class="match-info-badge">
                                                        <i data-feather="book-open"></i>
                                                        {{ Str::limit($match->material->name, 20) }}
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
                                                    @if($match->team1)
                                                        <span class="badge badge-red team-badge">Tim Merah</span>
                                                    @endif
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
                                                    @if($match->team2)
                                                        <span class="badge badge-blue team-badge">Tim Biru</span>
                                                    @endif
                                                </div>
                                                @if($match->gameSession)
                                                    <span class="team-score">{{ $match->gameSession->team_blue_score }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Action -->
                                        <div class="match-action">
                                            @if($round == 1 && (!$match->team_1_id || !$match->team_2_id))
                                                <span class="badge badge-green" style="background: rgba(34, 197, 94, 0.1) !important; color: #16A34A !important; font-style: italic;">
                                                    <i data-feather="check" width="12"></i>
                                                    Menang Otomatis (BYE)
                                                </span>
                                            @elseif($isCompleted)
                                                <span class="badge badge-green">
                                                    <i data-feather="check" width="12"></i>
                                                    Selesai
                                                </span>
                                            @elseif($isPlayable)
                                                <form action="{{ route('tournament.match.start', $match->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-play">
                                                        <i data-feather="play" width="16"></i>
                                                        MAIN SEKARANG!
                                                    </button>
                                                </form>
                                            @elseif(!$match->team_1_id || !$match->team_2_id)
                                                @if($round == 1)
                                                    <span class="badge badge-gray" style="font-style: italic;">
                                                        Menang Otomatis (BYE)
                                                    </span>
                                                @else
                                                    <span class="badge badge-gray">
                                                        <i data-feather="clock" width="12"></i>
                                                        Menunggu Tim
                                                    </span>
                                                @endif
                                            @elseif(!$match->is_ready)
                                                <span class="badge badge-yellow">
                                                    <i data-feather="alert-circle" width="12"></i>
                                                    Belum Dikonfigurasi
                                                </span>
                                            @else
                                                <span class="badge badge-blue">
                                                    <i data-feather="lock" width="12"></i>
                                                    Menunggu Giliran
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                
                <!-- Champion Box -->
                @if($tournament->status == 'completed')
                <div class="bracket-round">
                    <div class="round-title champion">🏅 CHAMPION</div>
                    <div class="round-matches">
                        <div class="match-wrapper" style="--no-connector: true;">
                            <div class="match-card winner-card">
                                <div class="trophy">🏆</div>
                                <div class="name">{{ $tournament->matches->sortByDesc('round')->first()->winner->name ?? 'TBD' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <script>
        // Fullscreen Toggle Logic

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(e => {
                    console.log('Fullscreen blocked:', e);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        function updateFullscreenIcon() {
            const btn = document.getElementById('btn-fullscreen');
            if (btn) {
                const isFullscreen = document.fullscreenElement;
                btn.innerHTML = isFullscreen ? '<i data-feather="minimize"></i>' : '<i data-feather="maximize"></i>';
                if (typeof feather !== 'undefined') feather.replace();
            }
            
            // Force redraw bracket as viewport dimensions change (wait for transition)
            setTimeout(() => {
                 window.dispatchEvent(new Event('resize'));
            }, 300);
        }

        document.addEventListener('fullscreenchange', updateFullscreenIcon);
        
        // Encapsulate Bracket Logic for Turbo Compatibility
        window.initBracket = function() {
            // 0. Force Page Layout (Critical for Layout Stability)
            document.body.style.minHeight = '100vh';
            document.body.style.height = 'auto';
            document.body.style.overflowY = 'auto';
            document.body.style.overflowX = 'hidden';
            
            // 1. Render Icons with Retry
            if (typeof feather !== 'undefined') {
                feather.replace();
            } else {
                 // Fallback if script lazy loaded
                 setTimeout(() => { if (typeof feather !== 'undefined') feather.replace(); }, 100);
                 setTimeout(() => { if (typeof feather !== 'undefined') feather.replace(); }, 500);
            }

            const wrapper = document.getElementById('bracketWrapper');
            if (!wrapper) return;
            
            const rounds = wrapper.querySelectorAll('.bracket-round');
            const connectorGap = 40;
            const borderWidth = 4;
            
            // 1. Position Matches
            function positionMatches() {
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
                            // Calculate avg vertical center of the pair
                            const targetCenter = (rect1.top + rect1.height/2 + rect2.top + rect2.height/2) / 2;
                            
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
            
            // 2. Draw Connectors (SVG)
            function drawConnectors() {
                const wrapper = document.getElementById('bracketWrapper');
                if (!wrapper) return;
                
                // Cleanup
                wrapper.querySelectorAll('.connector').forEach(el => el.remove());
                const existingSvg = wrapper.querySelector('.bracket-lines-svg');
                if (existingSvg) existingSvg.remove();
                
                const wrapperRect = wrapper.getBoundingClientRect();
                
                // Create SVG
                const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                svg.setAttribute('class', 'bracket-lines-svg');
                Object.assign(svg.style, {
                    position: 'absolute', top: '0', left: '0',
                    width: '100%', height: '100%',
                    zIndex: '0', pointerEvents: 'none'
                });
                
                const rounds = wrapper.querySelectorAll('.bracket-round');
                const strokeColor = '#94A3B8'; // Slate 400
                const strokeWidth = 4;
                const radius = 12; // Rounded corners
                const connectorGap = 40; 
                
                rounds.forEach((round, roundIndex) => {
                    if (roundIndex >= rounds.length - 1) return;
                    
                    const currentMatches = round.querySelectorAll('.match-wrapper');
                    const nextMatches = rounds[roundIndex + 1].querySelectorAll('.match-wrapper');
                    let nextMatchIndex = 0;
                    
                    for (let i = 0; i < currentMatches.length; i += 2) {
                        const match1 = currentMatches[i];
                        const match2 = currentMatches[i + 1];
                        const targetMatch = nextMatches[nextMatchIndex];
                        
                        if (!match1 || !targetMatch) continue;
                        
                        const rect1 = match1.getBoundingClientRect();
                        const targetRect = targetMatch.getBoundingClientRect();
                        
                        // Points
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
                            // Fork
                            const rect2 = match2.getBoundingClientRect();
                            const p2 = {
                                x: rect2.right - wrapperRect.left,
                                y: rect2.top + rect2.height / 2 - wrapperRect.top
                            };
                            
                            const midX = p1.x + connectorGap;
                            
                            // Top Path
                            d += `M ${p1.x} ${p1.y} L ${midX - radius} ${p1.y}`;
                            d += `Q ${midX} ${p1.y} ${midX} ${p1.y + radius}`;
                            
                            // Bottom Path
                            d += `M ${p2.x} ${p2.y} L ${midX - radius} ${p2.y}`;
                            d += `Q ${midX} ${p2.y} ${midX} ${p2.y - radius}`;
                            
                            // Vertical
                            d += `M ${midX} ${p1.y + radius} L ${midX} ${p2.y - radius}`;
                            
                            // To Target
                            const midY = (p1.y + p2.y) / 2;
                            d += `M ${midX} ${midY} L ${pt.x} ${pt.y}`;
                            
                        } else {
                            // Single
                            const midX = (p1.x + pt.x) / 2;
                            d += `M ${p1.x} ${p1.y} L ${midX - radius} ${p1.y}`;
                            
                            if (Math.abs(p1.y - pt.y) > radius * 2) {
                                const direction = pt.y > p1.y ? 1 : -1;
                                d += `Q ${midX} ${p1.y} ${midX} ${p1.y + (radius * direction)}`;
                                d += `L ${midX} ${pt.y - (radius * direction)}`;
                                d += `Q ${midX} ${pt.y} ${midX + radius} ${pt.y}`;
                            } else {
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
                        
                        nextMatchIndex++;
                    }
                });
                
                wrapper.appendChild(svg);
            }

            // Execute with Polling Strategy (Fix for First Load Incognito/Fullscreen)
            let attempts = 0;
            const poller = setInterval(() => {
                attempts++;
                const rect = wrapper.getBoundingClientRect();
                // Wait until wrapper has valid dimensions
                if (rect.width > 0 && rect.height > 0) {
                    clearInterval(poller);
                    requestAnimationFrame(() => {
                        positionMatches();
                        drawConnectors();
                        // Double check after layout settles
                        setTimeout(() => { positionMatches(); drawConnectors(); }, 200);
                    });
                } else if (attempts > 30) { // 3 seconds timeout
                    clearInterval(poller);
                     // Force run anyway
                     positionMatches();
                     drawConnectors();
                }
            }, 100);
            
            // Resize Handler
            if(window.bracketResizeHandler) window.removeEventListener('resize', window.bracketResizeHandler);
            window.bracketResizeHandler = () => { positionMatches(); drawConnectors(); };
            window.addEventListener('resize', window.bracketResizeHandler);
        };

        document.addEventListener('DOMContentLoaded', window.initBracket);
        document.addEventListener('turbo:load', window.initBracket);
    </script>
</body>
</html>

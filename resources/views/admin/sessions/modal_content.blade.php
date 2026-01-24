@php
    // Merge rounds by question_id to avoid duplicates (when each team answer creates separate record)
    $rawRounds = $session->rounds->sortBy('id');
    $mergedRounds = [];
    
    foreach($rawRounds as $round) {
        $qid = $round->question_id;
        
        if (!isset($mergedRounds[$qid])) {
            $mergedRounds[$qid] = $round->replicate();
            $mergedRounds[$qid]->question = $round->question;
            $mergedRounds[$qid]->id = $round->id;
        } else {
            // Merge answers from this round into existing
            $existing = $mergedRounds[$qid];
            
            // Merge Red Team data
            if ($round->team_red_answer !== null && $round->team_red_answer !== '') {
                $existing->team_red_answer = $round->team_red_answer;
                $existing->team_red_correct = $round->team_red_correct;
                $existing->team_red_time_ms = $round->team_red_time_ms;
            }
            
            // Merge Blue Team data
            if ($round->team_blue_answer !== null && $round->team_blue_answer !== '') {
                $existing->team_blue_answer = $round->team_blue_answer;
                $existing->team_blue_correct = $round->team_blue_correct;
                $existing->team_blue_time_ms = $round->team_blue_time_ms;
            }
        }
    }
    
    $rounds = collect($mergedRounds)->sortBy('round_number')->values();
    $totalRounds = $rounds->count();
@endphp

<!-- Data Bridge -->
<div id="m-slides-data" data-total="{{ $totalRounds }}" style="display:none;"></div>

<style>
    .m-scoreboard {
        display: grid; grid-template-columns: 1fr auto 1fr; gap: 1rem;
        background: #FFF4E6;
        padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem;
        align-items: center; border: 1px solid #FFD8A8;
    }
    .m-score-val { font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 0.25rem; }
    .m-score-val.red { color: var(--team-red); }
    .m-score-val.blue { color: var(--team-blue); }
    .m-team-name { font-size: 0.85rem; text-transform: uppercase; font-weight: 700; color: #9CA3AF; letter-spacing: 0.5px; }
    
    .m-slide { display: none; animation: fadeIn 0.3s ease-out; }
    .m-slide.active { display: block; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .m-q-card {
        background: white; 
        border: 1px solid #E2E8F0; 
        border-radius: 12px;
        padding: 1.5rem; 
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .m-q-type {
        display: inline-block; padding: 4px 10px; background: #F1F5F9; 
        color: #64748B; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
        margin-bottom: 1rem;
    }

    .m-q-text {
        font-size: 1.1rem; font-weight: 500; color: #1E293B; margin-bottom: 1rem; line-height: 1.35;
    }
    /* Rich Text Content Styling - Zero spacing (same as game play) */
    .m-q-text * { margin: 0; padding: 0; }
    .m-q-text p { margin: 0 !important; }
    .m-q-text p:empty, .m-q-text br { display: none; }
    .m-q-text b, .m-q-text strong { font-weight: 700; }
    .m-q-text img { 
        max-width: 100%; max-height: 200px; 
        border-radius: 8px; object-fit: contain;
        margin: 2px 0 !important;
        vertical-align: middle;
        display: inline-block;
    }
    .m-q-text ul, .m-q-text ol { padding-left: 1.2em !important; margin: 2px 0 !important; }
    .m-q-text li { margin: 1px 0 !important; }
    /* KaTeX Math Styling */
    .m-q-text .katex { font-size: 1em; }
    .m-opt .katex { font-size: 0.95em; }

    .m-opt {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 12px 16px; margin-bottom: 8px;
        border: 1px solid #E2E8F0; border-radius: 10px;
        background: white; transition: all 0.2s;
    }
    .m-opt.correct { background: #ECFDF5; border-color: #10B981; }
    .m-opt-key { 
        width: 24px; height: 24px; background: #F1F5F9; color: #64748B;
        border-radius: 6px; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
    }
    .m-opt.correct .m-opt-key { background: #10B981; color: white; }

    .m-badge {
        font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 4px;
    }
    .m-badge.red { background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; }
    .m-badge.blue { background: #e0f2fe; color: #3b82f6; border: 1px solid #93c5fd; }
    
    .m-team-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .m-team-box { 
        padding: 1rem; border-radius: 12px; text-align: center; 
        position: relative; overflow: hidden;
    }
    .m-team-box.red { background: #FEF2F2; border: 1px solid #FECACA; }
    .m-team-box.blue { background: #EFF6FF; border: 1px solid #BFDBFE; }
    
    .m-result-tag {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 20px;
        margin-top: 8px;
    }
    .m-result-tag.correct { background: #DCFCE7; color: #166534; }
    .m-result-tag.wrong { background: #FEE2E2; color: #991B1B; }

    .m-nav {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1rem 0; border-top: 1px solid #E2E8F0;
        background: white;
        position: sticky;
        bottom: -1.5rem; /* Offset modal body padding */
        margin: 1.5rem -1.5rem -1.5rem -1.5rem; /* Negative margin to extend to edges */
        padding: 1rem 1.5rem;
        z-index: 10;
    }
    .m-btn {
        padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: 8px;
        background: var(--dark); color: white; transition: 0.2s; user-select: none;
    }
    .m-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .m-btn:active { transform: translateY(0); }
    .m-btn:disabled { background: #E2E8F0; color: #94A3B8; cursor: not-allowed; transform: none; opacity: 1; }
</style>

<!-- SCOREBOARD -->
<div class="m-scoreboard">
    <!-- TIM MERAH -->
    <div style="display: flex; align-items: center; justify-content: center;">
        <!-- Left Side: Trophy or Spacer -->
        <div style="width: 50px; display: flex; justify-content: flex-end; padding-right: 10px;">
             @if($session->winner_team === 'red') <div style="font-size:1.8rem; animation: bounce 1s infinite;">🏆</div> @endif
        </div>
        
        <!-- Center: Text -->
        <div style="display: flex; flex-direction: column; align-items: center; min-width: 100px;">
            <div class="m-team-name">{{ $session->team_red_name }}</div>
            <div class="m-score-val red">{{ $session->team_red_score }}</div>
        </div>

        <!-- Right Side: Spacer (Balance) -->
        <div style="width: 50px;"></div>
    </div>

    <!-- VS -->
    <div style="font-size: 1.5rem; font-weight: 800; color: #FFD8A8;">VS</div>

    <!-- TIM BIRU -->
    <div style="display: flex; align-items: center; justify-content: center;">
        <!-- Left Side: Spacer (Balance) -->
        <div style="width: 50px;"></div>

        <!-- Center: Text -->
        <div style="display: flex; flex-direction: column; align-items: center; min-width: 100px;">
            <div class="m-team-name">{{ $session->team_blue_name }}</div>
            <div class="m-score-val blue">{{ $session->team_blue_score }}</div>
        </div>

        <!-- Right Side: Trophy or Spacer -->
        <div style="width: 50px; display: flex; justify-content: flex-start; padding-left: 10px;">
            @if($session->winner_team === 'blue') <div style="font-size:1.8rem; animation: bounce 1s infinite;">🏆</div> @endif
        </div>
    </div>
</div>
<style>
    @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
</style>

@if($rounds->isEmpty())
    <div style="text-align:center; padding:3rem; color:#94A3B8;">
        Belum ada data ronde tercatat.
    </div>
@else
    <div id="m-slides-container">
        @foreach($rounds as $idx => $round)
            @php
                $q = $round->question;
                
                // Parsing Options logic
                $options = [];
                if($q->question_type === 'multiple_choice' || $q->question_type === 'multiple_answer') {
                    $rawOpts = is_string($q->options) ? json_decode($q->options, true) : $q->options;
                    if(isset($rawOpts['choices'])) $options = $rawOpts['choices']; // multiple_answer format
                    else $options = $rawOpts; // multiple_choice format
                } elseif ($q->question_type === 'true_false') {
                    $options = [
                        ['key' => 'T', 'text' => 'Benar'],
                        ['key' => 'F', 'text' => 'Salah']
                    ];
                }
                
                // Correct Keys
                $correctKeys = [];
                if(is_array($q->correct_answer)) $correctKeys = $q->correct_answer; // simple array
                elseif(strpos($q->correct_answer, ',') !== false) $correctKeys = array_map('trim', explode(',', $q->correct_answer));
                else $correctKeys = [$q->correct_answer];
                $correctKeys = array_map('strtoupper', $correctKeys);
                
                // Team Answers Keys
                $redKeys = $round->team_red_answer ? array_map('trim', explode(',', strtoupper($round->team_red_answer))) : [];
                $blueKeys = $round->team_blue_answer ? array_map('trim', explode(',', strtoupper($round->team_blue_answer))) : [];
            @endphp

            <div class="m-slide {{ $idx === 0 ? 'active' : '' }}" data-idx="{{ $idx }}">
                <div class="d-flex justify-between align-center mb-2">
                    <span class="m-q-type">
                        @if($q->question_type == 'multiple_choice') Pilihan Ganda
                        @elseif($q->question_type == 'multiple_answer') Pilihan Ganda Kompleks
                        @elseif($q->question_type == 'true_false') Benar/Salah
                        @else Isian Singkat
                        @endif
                    </span>
                    <span style="font-size:0.85rem; font-weight:700; color:#CBD5E1;">#{{ $idx + 1 }}</span>
                </div>

                <div class="m-q-card">
                    @if($q->image_url)
                        <div style="text-align: center; margin-bottom: 1.5rem; background: #F8FAFC; padding: 10px; border-radius: 8px;">
                            <img src="{{ Str::startsWith($q->image_url, ['http', 'https']) ? $q->image_url : asset($q->image_url) }}" 
                                 style="max-width: 100%; max-height: 250px; border-radius: 6px;"
                                 loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                        </div>
                    @endif

                    <div class="m-q-text">{!! $q->question_text !!}</div>

                    <!-- Options List -->
                    @if(!empty($options))
                        <div class="m-options-list">
                            @foreach($options as $opt)
                                @php
                                    $key = is_array($opt) ? ($opt['key'] ?? $loop->index) : $loop->index;
                                    $text = is_array($opt) ? ($opt['text'] ?? ($opt['value'] ?? '')) : $opt;
                                    $keyStr = strtoupper((string)$key);
                                    
                                    $isCorrect = in_array($keyStr, $correctKeys);
                                    $isRed = in_array($keyStr, $redKeys);
                                    $isBlue = in_array($keyStr, $blueKeys);
                                @endphp
                                <div class="m-opt {{ $isCorrect ? 'correct' : '' }}">
                                    <div class="m-opt-key">{{ $key }}</div>
                                    <div style="flex:1; font-weight:500; color:#334155;">{!! $text !!}</div>
                                    <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-end;">
                                        @if($isCorrect)<i data-feather="check-circle" style="width:16px; color:#10B981;"></i>@endif
                                        <div style="display:flex; gap:4px;">
                                            @if($isRed)<span class="m-badge red">Merah</span>@endif
                                            @if($isBlue)<span class="m-badge blue">Biru</span>@endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Short Answer Display -->
                         <div style="background: #ECFDF5; border: 1px solid #10B981; padding: 1.5rem; border-radius: 12px; text-align: center; margin-top: 1rem;">
                            <div style="font-size: 0.8rem; font-weight: 700; color: #047857; text-transform: uppercase;">Kunci Jawaban</div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: #065F46; margin-top: 0.5rem;">
                                {{ is_array($q->correct_answer) ? implode(', ', $q->correct_answer) : $q->correct_answer }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Team Results -->
                <div class="m-team-grid">
                    <div class="m-team-box red">
                        <div style="font-size:0.8rem; font-weight:700; color:#ef4444; margin-bottom:5px;">TIM MERAH</div>
                        <div style="font-weight:600; font-size:1.1rem; color:#7f1d1d;">
                            {{ $round->team_red_answer ?: '-' }}
                        </div>
                        @if($round->team_red_answer)
                            <div class="m-result-tag {{ $round->team_red_correct ? 'correct' : 'wrong' }}">
                                @if($round->team_red_correct) <span>✅ Benar</span> @else <span>❌ Salah</span> @endif
                            </div>
                        @else
                            <div class="m-result-tag" style="background:#F1F5F9; color:#64748B;">Tidak Menjawab</div>
                        @endif
                        @if($round->team_red_time_ms)
                            <div style="font-size:0.75rem; color:#991B1B; margin-top:5px; opacity:0.8;">{{ number_format($round->team_red_time_ms/1000, 2) }}s</div>
                        @endif
                    </div>

                    <div class="m-team-box blue">
                        <div style="font-size:0.8rem; font-weight:700; color:#3b82f6; margin-bottom:5px;">TIM BIRU</div>
                        <div style="font-weight:600; font-size:1.1rem; color:#1e3a8a;">
                            {{ $round->team_blue_answer ?: '-' }}
                        </div>
                         @if($round->team_blue_answer)
                            <div class="m-result-tag {{ $round->team_blue_correct ? 'correct' : 'wrong' }}">
                                @if($round->team_blue_correct) <span>✅ Benar</span> @else <span>❌ Salah</span> @endif
                            </div>
                        @else
                             <div class="m-result-tag" style="background:#F1F5F9; color:#64748B;">Tidak Menjawab</div>
                        @endif
                        @if($round->team_blue_time_ms)
                            <div style="font-size:0.75rem; color:#1E40AF; margin-top:5px; opacity:0.8;">{{ number_format($round->team_blue_time_ms/1000, 2) }}s</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Navigation -->
    <div class="m-nav">
        <button class="m-btn" id="m-btn-prev" onclick="changeModalSlide(-1)" disabled>
            <i data-feather="chevron-left"></i> Sebelumnya
        </button>
        <div style="font-weight: 700; color: #475569; font-size: 1rem;">
            Soal <span id="m-cur-page">1</span> dari {{ $totalRounds }}
        </div>
        <button class="m-btn" id="m-btn-next" onclick="changeModalSlide(1)" {{ $totalRounds <= 1 ? 'disabled' : '' }}>
            Selanjutnya <i data-feather="chevron-right"></i>
        </button>
    </div>
@endif

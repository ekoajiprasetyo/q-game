<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Kotak Kejutan | Q-Game</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#25213d; --purple:#7c3aed; --purple-dark:#5b21b6; --soft:#f5f3ff; --line:#e7e0fb; }
        * { box-sizing:border-box; } body { margin:0; min-height:100vh; font-family:Inter,sans-serif; color:var(--ink); background:#FFF8F0; } .qgame-bg { position:fixed; inset:0; z-index:0; overflow:hidden; pointer-events:none; background:radial-gradient(circle at 10% 20%,#F3F0FF 0%,#FFF8F0 60%,#FFF 100%); } .qgame-bg .blob { position:absolute; filter:blur(80px); opacity:.6; animation:qgame-float 10s infinite alternate ease-in-out; } .qgame-bg .blob-1 { top:-10%; left:-10%; width:50vw; height:50vw; background:#EADDFF; } .qgame-bg .blob-2 { right:-10%; bottom:-10%; width:60vw; height:60vw; background:#FFDBC8; animation-delay:-5s; } .qgame-bg .blob-3 { top:40%; left:40%; width:30vw; height:30vw; background:#E8DEF8; animation-delay:-2s; } @keyframes qgame-float { to { transform:translate(30px,50px) scale(1.1); } }
        .shell { position:relative; z-index:1; width:min(1040px,calc(100% - 32px)); margin:0 auto; padding:36px 0 60px; } .back { display:inline-flex; align-items:center; gap:7px; color:#6b5c99; text-decoration:none; font-weight:600; font-size:.9rem; } .button-icon { width:18px; height:18px; flex:none; stroke:currentColor; stroke-width:2.25; stroke-linecap:round; stroke-linejoin:round; fill:none; } .fullscreen-toggle { position:fixed; z-index:2; top:22px; right:22px; display:grid; place-items:center; width:44px; height:44px; border:0; border-radius:12px; background:#FF9B50; color:#fff; cursor:pointer; box-shadow:0 4px 12px #e25e3e55; transition:transform .2s,background .2s; } .fullscreen-toggle:hover { transform:scale(1.05); background:#E25E3E; } .fullscreen-toggle .button-icon { width:21px; height:21px; } .fullscreen-toggle .icon-minimize { display:none; } .fullscreen-toggle.is-fullscreen .icon-maximize { display:none; } .fullscreen-toggle.is-fullscreen .icon-minimize { display:block; }
        .hero { display:flex; gap:20px; align-items:center; margin:26px 0 28px; } .icon { width:68px; height:68px; display:grid; place-items:center; border-radius:22px; font-size:32px; background:linear-gradient(135deg,#a78bfa,#6d28d9); box-shadow:0 12px 30px #8b5cf655; }
        h1 { margin:0; font-size:clamp(1.65rem,4vw,2.4rem); } .hero p { margin:7px 0 0; color:#655d78; }
        .notice { padding:13px 16px; border-radius:12px; margin:0 0 18px; background:#fef2f2; color:#b42318; } .grid { display:grid; grid-template-columns:1.15fr .85fr; gap:20px; } .panel { background:#fff; border:1px solid var(--line); border-radius:20px; padding:24px; box-shadow:0 12px 32px #4c1d9510; }
        h2 { font-size:1.05rem; margin:0 0 18px; } label { display:block; font-size:.84rem; font-weight:700; margin:16px 0 7px; } input,select { width:100%; padding:12px; border:1px solid #dcd6ea; border-radius:10px; background:#fff; font:inherit; color:var(--ink); } input:focus,select:focus { outline:3px solid #ddd6fe; border-color:#8b5cf6; }
        .hint { font-size:.78rem; color:#766f87; margin:7px 0 0; } .teams { display:grid; gap:9px; margin-top:12px; } .team { display:grid; grid-template-columns:34px 1fr 42px; gap:8px; align-items:center; } .team b { text-align:center; color:#766f87; } .team input[type=color] { height:40px; padding:3px; cursor:pointer; }
        .sizes { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; } .size input { position:absolute; opacity:0; } .size span { display:block; padding:11px 4px; text-align:center; border:1px solid #dcd6ea; border-radius:10px; font-weight:700; cursor:pointer; } .size input:checked + span { color:#fff; background:var(--purple); border-color:var(--purple); }
        .launch { width:100%; margin-top:24px; padding:14px; border:0; border-radius:12px; color:#fff; cursor:pointer; font:700 1rem Inter,sans-serif; background:linear-gradient(135deg,var(--purple),var(--purple-dark)); box-shadow:0 10px 22px #7c3aed44; display:inline-flex; align-items:center; justify-content:center; gap:8px; } .launch:hover { transform:translateY(-1px); }
        .phase { padding:14px; background:var(--soft); border-radius:12px; font-size:.85rem; line-height:1.5; color:#5e5575; } .phase strong { color:#5b21b6; } .power-up-actions { display:flex; gap:8px; margin:12px 0 8px; } .power-up-actions button { padding:7px 10px; border:1px solid #c4b5fd; border-radius:8px; background:#fff; color:#6d28d9; font:700 .75rem Inter,sans-serif; cursor:pointer; } .power-up-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; margin-top:10px; } .power-up-choice { position:relative; display:block; margin:0; cursor:pointer; } .power-up-choice input { position:absolute; opacity:0; pointer-events:none; } .power-up-choice span { display:block; min-height:72px; padding:10px; border:2px solid #ddd6fe; border-radius:11px; background:#fff; transition:.15s; } .power-up-choice strong { display:block; color:#3b2a68; font-size:.82rem; } .power-up-choice small { display:block; margin-top:3px; color:#766f87; font-size:.72rem; line-height:1.3; } .power-up-choice input:checked + span { border-color:#7c3aed; background:#ede9fe; box-shadow:0 3px 10px #7c3aed22; } .selection-error { color:#b42318 !important; } .launch:disabled { cursor:not-allowed; opacity:.55; box-shadow:none; } @media(max-width:760px) { .grid{grid-template-columns:1fr}.shell{padding-top:24px}.panel{padding:18px} } @media(max-width:440px) { .power-up-grid{grid-template-columns:1fr} }
    </style>
</head>
<body>
<div class="qgame-bg" aria-hidden="true"><div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div></div>
<button class="fullscreen-toggle" type="button" id="surprise-fullscreen" aria-label="Layar penuh" title="Layar penuh"><svg class="button-icon icon-maximize" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M21 16v3a2 2 0 0 1-2 2h-3M3 16v3a2 2 0 0 0 2 2h3"/></svg><svg class="button-icon icon-minimize" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3v5H3M16 3v5h5M21 16h-5v5M3 16h5v5"/></svg></button>
<main class="shell">
    <a class="back" href="{{ route('game.setup') }}"><svg class="button-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg><span>Kembali ke pilihan permainan</span></a>
    <section class="hero"><div class="icon">🎁</div><div><h1>Kotak Kejutan</h1><p>Pilih kartu, jawab pertanyaan, dan kumpulkan poin bersama tim.</p></div></section>
    @if ($errors->any())<div class="notice"><strong>Setup belum dapat dibuat.</strong><br>{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('surprise.sessions.store') }}" class="grid" id="surprise-setup-form">
        @csrf
        <section class="panel">
            <h2>1. Bank soal dan mode</h2>
            <label for="title">Judul permainan <small>(opsional)</small></label><input id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Kuis IPA Kelas 7A">
            <label for="topic">Topik</label>
            <select id="topic" name="topic_id" required><option value="">Pilih topik</option>@foreach($topics as $topic)<option value="{{ $topic->id }}" data-count="{{ $topic->questions_count }}" {{ old('topic_id') == $topic->id ? 'selected' : '' }}>{{ $topic->name }} ({{ $topic->questions_count }} soal)</option>@endforeach</select>
            <label for="material">Materi <small>(opsional)</small></label>
            <select id="material" name="material_id">
                <option value="">Semua materi dalam topik</option>
                @foreach($topics as $topic)
                    @foreach($topic->materials as $material)
                        <option value="{{ $material->id }}" data-topic="{{ $topic->id }}" data-count="{{ $material->questions_count }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>{{ $topic->name }} — {{ $material->name }} ({{ $material->questions_count }} soal)</option>
                    @endforeach
                @endforeach
            </select>
            <p class="hint" id="availability">Pilih topik untuk melihat jumlah soal yang dapat dipakai.</p>
            <label for="mode">Mode permainan</label>
            <select id="mode" name="mode" required>
                <option value="quiz" {{ old('mode', 'quiz') === 'quiz' ? 'selected' : '' }}>Quiz — semua kartu berisi soal</option>
                <option value="classic" {{ old('mode') === 'classic' ? 'selected' : '' }}>Klasik — soal dan efek kompetitif</option>
                <option value="friendly" {{ old('mode') === 'friendly' ? 'selected' : '' }}>Ramah — soal dan efek positif/netral</option>
            </select>
            <p class="hint" id="mode-help">Mode Quiz membutuhkan satu soal untuk setiap kartu.</p>
            <div class="phase" id="powerUpConfig" hidden>
                <strong>Kartu kejutan kandidat</strong>
                <p style="margin:5px 0 0">Pilih efek yang boleh muncul. Sistem akan mengambil acak 25% kartu dari pilihan ini tanpa kartu yang sama dalam satu sesi.</p>
                <div class="power-up-actions"><button type="button" id="selectAllPowerUps">Pilih semua</button><button type="button" id="clearPowerUps">Kosongkan</button></div>
                <p class="hint" id="powerUpSelectionHint"></p>
                <div class="power-up-grid" id="powerUpSlots"></div>
            </div>
            <div class="phase" style="margin-top:22px"><strong>Efek Kotak Kejutan.</strong> Mode Klasik memuat seluruh variasi, termasuk serangan skor antartim. Mode Ramah tidak merugikan tim lawan; risikonya hanya dapat terjadi pada tim yang membuka kartu.</div>
        </section>
        <section class="panel">
            <h2>2. Tim bermain</h2>
            <label for="team-count">Jumlah tim</label><select id="team-count">@for($count=2;$count<=8;$count++)<option value="{{ $count }}" {{ old('team_count', 2) == $count ? 'selected' : '' }}>{{ $count }} tim</option>@endfor</select>
            <div class="teams" id="teams"></div>
            <label>Ukuran papan rekomendasi</label>
            <div class="sizes" id="boardOptions"></div>
            <p class="hint" id="boardHint">Pilih topik atau materi untuk melihat ukuran papan yang sesuai.</p>
            <label for="time">Batas waktu tiap soal <small>(opsional)</small></label><select id="time" name="question_time_limit"><option value="">Tanpa timer</option>@foreach([15,30,45,60,90,120] as $seconds)<option value="{{ $seconds }}" {{ old('question_time_limit') == $seconds ? 'selected' : '' }}>{{ $seconds }} detik</option>@endforeach</select>
            <button class="launch" type="submit"><span>Mulai Kotak Kejutan</span><svg class="button-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
        </section>
    </form>
</main>
@php
    $setupPowerUps = [];
    foreach (['classic', 'friendly'] as $setupMode) {
        $setupPowerUps[$setupMode] = [];
        foreach (\App\Services\SurpriseGameService::powerUpOptions($setupMode) as $code => $effect) {
            $setupPowerUps[$setupMode][] = [$code, $effect['icon'].' '.$effect['label'], $effect['description']];
        }
    }
@endphp
<script>
const palettes=['#7C3AED','#2563EB','#DC2626','#059669','#EA580C','#0891B2','#DB2777','#4F46E5'];
const oldTeams=@json(old('teams', [])); const oldPowerUps=@json(old('power_up_codes', [])); const oldBoardSize=@json(old('board_size')); const teamBox=document.getElementById('teams'); const count=document.getElementById('team-count');
const boardOptionsByTeam={2:[8,16,24,36],3:[9,15,24,36],4:[8,16,24,36],5:[10,15,20,25,30,35],6:[12,18,24,30,36],7:[14,21,28,35],8:[8,16,24,32]};
const powerUps=@json($setupPowerUps);
function escapeHtml(value){const element=document.createElement('div');element.textContent=value;return element.innerHTML;}
function renderTeams(){ teamBox.innerHTML=''; for(let i=0;i<Number(count.value);i++){const old=oldTeams[i]||{};teamBox.insertAdjacentHTML('beforeend',`<div class="team"><b>${i+1}</b><input required maxlength="40" name="teams[${i}][name]" value="${escapeHtml(old.name||`Tim ${i+1}`)}" aria-label="Nama tim ${i+1}"><input required type="color" name="teams[${i}][color]" value="${old.color||palettes[i]}" aria-label="Warna tim ${i+1}"></div>`);} }
function updateMaterials(){const topic=document.getElementById('topic'), material=document.getElementById('material');[...material.options].forEach((opt,i)=>{if(i) opt.hidden=opt.dataset.topic!==topic.value;});if(material.selectedOptions[0]?.hidden) material.value='';refreshBoardOptions();}
function availableQuestions(){const topic=document.getElementById('topic'), material=document.getElementById('material'), source=material.value?material.selectedOptions[0]:topic.selectedOptions[0];return Number(source?.dataset.count||0);}
function requiredQuestions(){const board=Number(document.querySelector('input[name="board_size"]:checked')?.value||0);return document.getElementById('mode').value==='quiz'?board:Math.ceil(board*.75);}
function surpriseCount(){const board=Number(document.querySelector('input[name="board_size"]:checked')?.value||0);return document.getElementById('mode').value==='quiz'?0:board-requiredQuestions();}
function updatePowerUpSelection(){const total=surpriseCount(), selected=document.querySelectorAll('input[name="power_up_codes[]"]:checked').length, hint=document.getElementById('powerUpSelectionHint'), launch=document.querySelector('.launch');if(!total){hint.textContent=document.getElementById('mode').value==='quiz'?'':'Pilih ukuran papan untuk melihat jumlah minimum kartu kejutan.';hint.classList.remove('selection-error');launch.disabled=false;return;}hint.textContent=`${selected} dipilih · minimal ${total} kartu berbeda diperlukan.`;hint.classList.toggle('selection-error',selected<total);launch.disabled=selected<total;}
function renderPowerUpSlots(){const mode=document.getElementById('mode').value, config=document.getElementById('powerUpConfig'), slots=document.getElementById('powerUpSlots');config.hidden=mode==='quiz';slots.innerHTML='';if(mode==='quiz'){updatePowerUpSelection();return;}const selectedCodes=oldPowerUps.length?oldPowerUps:powerUps[mode].map(([code])=>code);powerUps[mode].forEach(([code,label,description])=>slots.insertAdjacentHTML('beforeend',`<label class="power-up-choice"><input type="checkbox" name="power_up_codes[]" value="${code}" ${selectedCodes.includes(code)?'checked':''}><span><strong>${label}</strong><small>${description}</small></span></label>`));slots.querySelectorAll('input').forEach(input=>input.addEventListener('change',updatePowerUpSelection));updatePowerUpSelection();}
function questionsForBoard(size){return document.getElementById('mode').value==='quiz'?size:Math.ceil(size*.75);}
function renderBoardOptions(){const available=availableQuestions(), teamCount=Number(count.value), options=boardOptionsByTeam[teamCount]||[], box=document.getElementById('boardOptions'), hint=document.getElementById('boardHint'), previous=Number(document.querySelector('input[name="board_size"]:checked')?.value||oldBoardSize||0), eligible=options.filter(size=>questionsForBoard(size)<=available), mode=document.getElementById('mode').value, minimumQuestions=options.length?Math.min(...options.map(questionsForBoard)):0;box.innerHTML='';eligible.forEach((size,index)=>{const checked=(size===previous)||(!eligible.includes(previous)&&index===0);box.insertAdjacentHTML('beforeend',`<label class="size"><input type="radio" name="board_size" value="${size}" ${checked?'checked':''} required><span>${size} kartu</span></label>`);});box.querySelectorAll('input').forEach(input=>input.addEventListener('change',refreshGameConfig));if(!available){hint.textContent='Pilih topik atau materi untuk melihat ukuran papan yang sesuai.';return;}if(eligible.length){hint.textContent=`${available} soal tersedia. Pilihan disesuaikan untuk ${teamCount} tim dan membutuhkan ${mode==='quiz'?'100%':'minimal 75%'} kartu soal.`;return;}const missing=Math.max(0,minimumQuestions-available);hint.textContent=`${available} soal tersedia. Untuk ${teamCount} tim pada mode ${mode==='quiz'?'Quiz':'ini'}, papan terkecil membutuhkan minimal ${minimumQuestions} soal${missing?` (tambah ${missing} soal lagi)`:''}.`;}
function updateAvailability(){const available=availableQuestions(), required=requiredQuestions();document.getElementById('availability').textContent=available?`${available} soal tersedia. Papan terpilih membutuhkan ${required} kartu soal.`:'Pilih topik untuk melihat jumlah soal yang dapat dipakai.';const mode=document.getElementById('mode').value;document.getElementById('mode-help').textContent=mode==='quiz'?'Mode Quiz membutuhkan satu soal untuk setiap kartu.':'Mode ini memakai minimal 75% kartu soal dan sisanya kartu kejutan.';}
function refreshGameConfig(){renderPowerUpSlots();updateAvailability();}
function refreshBoardOptions(){renderBoardOptions();refreshGameConfig();}
function isSurpriseFullscreen(){return Boolean(document.fullscreenElement||document.webkitFullscreenElement);}function updateSurpriseFullscreenButton(){const button=document.getElementById('surprise-fullscreen'),active=isSurpriseFullscreen();button.classList.toggle('is-fullscreen',active);button.setAttribute('aria-label',active?'Keluar dari layar penuh':'Layar penuh');button.title=button.getAttribute('aria-label');}function toggleSurpriseFullscreen(){if(isSurpriseFullscreen()){(document.exitFullscreen||document.webkitExitFullscreen)?.call(document);sessionStorage.removeItem('qgame-surprise-fullscreen');return;}sessionStorage.setItem('qgame-surprise-fullscreen','1');const root=document.documentElement;(root.requestFullscreen||root.webkitRequestFullscreen)?.call(root)?.catch?.(()=>{});}document.getElementById('surprise-fullscreen').addEventListener('click',toggleSurpriseFullscreen);document.addEventListener('fullscreenchange',updateSurpriseFullscreenButton);document.addEventListener('webkitfullscreenchange',updateSurpriseFullscreenButton);document.getElementById('surprise-setup-form').addEventListener('submit',()=>{if(isSurpriseFullscreen())sessionStorage.setItem('qgame-surprise-fullscreen','1');});updateSurpriseFullscreenButton();count.addEventListener('change',()=>{renderTeams();refreshBoardOptions();});document.getElementById('topic').addEventListener('change',updateMaterials);document.getElementById('material').addEventListener('change',refreshBoardOptions);document.getElementById('mode').addEventListener('change',refreshBoardOptions);document.getElementById('selectAllPowerUps').addEventListener('click',()=>{document.querySelectorAll('input[name="power_up_codes[]"]').forEach(input=>input.checked=true);updatePowerUpSelection();});document.getElementById('clearPowerUps').addEventListener('click',()=>{document.querySelectorAll('input[name="power_up_codes[]"]').forEach(input=>input.checked=false);updatePowerUpSelection();});renderTeams();refreshBoardOptions();
</script>
</body></html>

<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking for stuck tournament advancements...\n";

$matches = App\Models\TournamentMatch::whereNotNull('winner_team_id')
    ->whereNotNull('next_match_id')
    ->with(['winner', 'nextMatch'])
    ->get();

$fixedCount = 0;

foreach ($matches as $match) {
    if (!$match->winner || !$match->nextMatch) continue;

    $next = $match->nextMatch;
    $winnerId = $match->winner_team_id;
    
    // Determine target slot based on match number parity
    // Odd match number -> Team 1 (Top/Red)
    // Even match number -> Team 2 (Bottom/Blue)
    $isTeam1Slot = ($match->match_number % 2 != 0);
    
    $needsUpdate = false;
    
    if ($isTeam1Slot) {
        if ($next->team_1_id != $winnerId) {
            echo "FIX: Match #{$match->match_number} (ID:{$match->id}) Winner '{$match->winner->name}' belum maju ke Next Match #{$next->match_number} (ID:{$next->id}) Slot Top/Merah.\n";
            $next->team_1_id = $winnerId;
            $needsUpdate = true;
        }
    } else {
        if ($next->team_2_id != $winnerId) {
             echo "FIX: Match #{$match->match_number} (ID:{$match->id}) Winner '{$match->winner->name}' belum maju ke Next Match #{$next->match_number} (ID:{$next->id}) Slot Bottom/Biru.\n";
            $next->team_2_id = $winnerId;
            $needsUpdate = true;
        }
    }
    
    if ($needsUpdate) {
        $next->save();
        $fixedCount++;
        echo " -> Berhasil diperbaiki.\n";
    }
}

if ($fixedCount === 0) {
    echo "Semua advancement terlihat normal.\n";
} else {
    echo "Total $fixedCount match diperbaiki.\n";
}

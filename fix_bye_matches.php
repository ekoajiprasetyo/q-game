<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Memperbaiki Bye Matches yang ter-reset kemenangannya...\n";

// Query: Round 1, Team 1 Filled, Team 2 Empty (Bye), Winner Empty (Resetted)
$byeMatches = App\Models\TournamentMatch::where('round', 1)
    ->whereNotNull('team_1_id')
    ->whereNull('team_2_id')
    ->whereNull('winner_team_id')
    ->get();

if ($byeMatches->isEmpty()) {
    echo "Tidak ada Bye Match yang perlu diperbaiki.\n";
    exit;
}

$service = new App\Services\TournamentService();

foreach ($byeMatches as $match) {
    echo "-> Fixing Match ID {$match->id} (Round 1, Match #{$match->match_number})...\n";
    
    // 1. Restore Winner
    $match->winner_team_id = $match->team_1_id;
    $match->save();
    echo "   Winner restored to Team ID {$match->team_1_id}.\n";
    
    // 2. Advance to Next Round
    $match->load('winner');
    if ($match->winner) {
        $service->advanceWinner($match, $match->winner);
        echo "   Winner advanced to next bracket.\n";
    } else {
        echo "   ERROR: Winner team model not found.\n";
    }
}

echo "Selesai.\n";

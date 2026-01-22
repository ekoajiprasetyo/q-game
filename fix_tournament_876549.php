<?php

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\GameSession;
use App\Services\TournamentService;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pin = '876549';
$tournament = Tournament::where('pin', $pin)->first();

if (!$tournament) {
    echo "Tournament with PIN $pin not found.\n";
    // Also try to find by ID if pin logic is new or failing? 
    // But user gave PIN.
    exit(1);
}

echo "Found Tournament: {$tournament->title} (ID: {$tournament->id})\n";

// 1. Identify Game Sessions to delete
// Note: We pluck from DB directly to avoid loading all objects if many
$sessionIds = TournamentMatch::where('tournament_id', $tournament->id)
                ->whereNotNull('game_session_id')
                ->pluck('game_session_id')
                ->unique();

echo "Associated Game Sessions found: " . $sessionIds->count() . "\n";

// 2. Clear matches
echo "Resetting matches...\n";

// Clear Round > 1
// We strip teams and winners from future rounds
TournamentMatch::where('tournament_id', $tournament->id)
    ->where('round', '>', 1)
    ->update([
        'team_1_id' => null,
        'team_2_id' => null,
        'winner_team_id' => null,
        'game_session_id' => null
    ]);

// Clear Round 1 (keep teams, clear results)
TournamentMatch::where('tournament_id', $tournament->id)
    ->where('round', 1)
    ->update([
        'winner_team_id' => null,
        'game_session_id' => null
    ]);

// 3. Delete Game Sessions
if ($sessionIds->count() > 0) {
    GameSession::whereIn('id', $sessionIds)->delete();
    echo "Deleted {$sessionIds->count()} game sessions.\n";
}

// 4. Restore BYEs
// Logic: If round 1 has team_1 but no team_2, it is a BYE and needs auto-win.
$byes = TournamentMatch::where('tournament_id', $tournament->id)
    ->where('round', 1)
    ->whereNull('team_2_id')
    ->whereNotNull('team_1_id')
    ->with('team1')
    ->get();

$service = new TournamentService();

if ($byes->count() > 0) {
    echo "Restoring {$byes->count()} BYE matches...\n";
    foreach ($byes as $match) {
        if ($match->team1) {
            echo " - Advancing {$match->team1->name} (Match #{$match->match_number})\n";
            // advanceWinner updates the current match winner AND the next match slots
            $service->advanceWinner($match, $match->team1);
        } else {
            echo " - Warning: Bye match #{$match->match_number} has no team1?\n";
        }
    }
}

// 5. Ensure Status
$tournament->refresh();
// Likely 'active' is the correct status for an ongoing tournament (even if round 1 restarted)
$tournament->update(['status' => 'active']);
echo "Tournament status set to active.\n";

echo "Done.\n";

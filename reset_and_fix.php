<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Resetting Game Sessions...\n";
App\Models\GameSession::query()->delete();

echo "Resetting Tournament Matches Results...\n";
App\Models\TournamentMatch::query()->update(['game_session_id' => null, 'winner_team_id' => null]);

echo "Clearing Advanced Slots (Round > 1)...\n";
App\Models\TournamentMatch::where('round', '>', 1)->update(['team_1_id' => null, 'team_2_id' => null]);

echo "Restoring BYE Winners...\n";
$byes = App\Models\TournamentMatch::where('round', 1)->whereNull('team_2_id')->get();
$service = new App\Services\TournamentService();

foreach($byes as $b) {
    if (!$b->team_1_id) continue;
    
    $b->update(['winner_team_id' => $b->team_1_id]);
    
    // Explicitly load winner relationship
    $b = $b->fresh(['winner']);
    
    if($b->winner) {
        $service->advanceWinner($b, $b->winner);
        echo " - Restored Bye Match #{$b->match_number} (Team: {$b->winner->name})\n";
    }
}
echo "Reset Complete.\n";

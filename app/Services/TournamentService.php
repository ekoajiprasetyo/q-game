<?php

namespace App\Services;

use App\Models\TournamentMatch;
use App\Models\TournamentTeam;
use Illuminate\Support\Collection;

class TournamentService
{
    /**
     * Generate bracket matches for a tournament based on registered teams.
     */
    public function generateBracket($tournament, Collection $teams)
    {
        $teamCount = $teams->count();
        // 1. Calculate Bracket Size (Next Power of 2)
        // e.g. 6 teams -> 8 slots. 5 teams -> 8 slots. 3 teams -> 4 slots.
        $bracketSize = pow(2, ceil(log($teamCount, 2)));
        $rounds = log($bracketSize, 2);
        
        // 2. Logic to distribute Byes
        // Total slots in R1 = bracketSize.
        // Teams we have = teamCount.
        // Empty slots (Byes) = bracketSize - teamCount.
        // Matches in R1 = bracketSize / 2.
        
        // "Matches that must be Byes" logic:
        // A "Bye Match" consumes 1 Team and 1 Empty Slot.
        // A "Normal Match" consumes 2 Teams.
        
        // Number of Byes = $bracketSize - $teamCount. 
        // Example: 6 teams, 8 slots. 2 Byes.
        // Those 2 Byes mean 2 Matches will have only 1 team.
        // The rest of matches (4 total - 2 bye matches = 2) will have 2 teams.
        
        $numByes = $bracketSize - $teamCount;
        $matchesInR1 = $bracketSize / 2;
        $numByeMatches = $numByes;
        $numNormalMatches = $matchesInR1 - $numByeMatches;
        
        // Shuffle teams
        $shuffledTeams = $teams->shuffle()->values();
        
        $matches = [];
        $matchCounter = 1;

        // 3. Create Round 1 Matches
        // Strategy: First create Normal Matches, then Bye Matches.
        // Or mix them? Typically Byes are placed at top and bottom, but random is fine for this app.
        
        $teamIndex = 0;
        
        // Create Normal Matches (Full 2 teams)
        for ($i = 0; $i < $numNormalMatches; $i++) {
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round' => 1,
                'match_number' => $matchCounter++,
                'team_1_id' => $shuffledTeams[$teamIndex++]->id,
                'team_2_id' => $shuffledTeams[$teamIndex++]->id,
            ]);
            $matches[1][$match->match_number] = $match;
        }
        
        // Create Bye Matches (1 team, auto win)
        for ($i = 0; $i < $numByeMatches; $i++) {
            $team = $shuffledTeams[$teamIndex++];
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round' => 1,
                'match_number' => $matchCounter++,
                'team_1_id' => $team->id,
                'team_2_id' => null, // Empty slot
                'winner_team_id' => $team->id, // Auto Win!
            ]);
            $matches[1][$match->match_number] = $match;
        }

        // 4. Create Empty Matches for Subsequent Rounds (R2, R3...)
        for ($r = 2; $r <= $rounds; $r++) {
            $matchesInRound = $bracketSize / pow(2, $r);
            
            for ($m = 1; $m <= $matchesInRound; $m++) {
                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $r,
                    'match_number' => $m,
                ]);
                $matches[$r][$m] = $match;
            }
        }
        
        // 5. Link matches (Next Match Logic)
        // Similar to before, but we must account for R1 potentially having "Completed" matches (Byes)
        // that need to forward their winner immediately.
        
        for ($r = 1; $r < $rounds; $r++) {
            // Sort matches by number to ensure correct linkage logic
            // Note: Our array keys are match_number, so we can iterate
            ksort($matches[$r]);
            
            // Re-index strictly for the loop if needed, but keys are 1..N
            // Actually, we generated Normal matches then Bye matches. Their numbers are 1..N continuous.
            
            foreach ($matches[$r] as $mNumber => $match) {
                $nextRoundMatchNumber = ceil($mNumber / 2);
                $nextMatch = $matches[$r+1][$nextRoundMatchNumber];
                
                $match->update(['next_match_id' => $nextMatch->id]);
                
                // CRITICAL: If this match was a BYE (already has winner), advance it immediately!
                if ($match->winner_team_id) {
                    $this->advanceWinner($match, $match->winner);
                }
            }
        }
        
        // Update tournament status
        $tournament->update(['status' => 'active']);
    }

    /**
     * Reshuffle Bracket
     */
    public function resetBracket($tournament)
    {
        // 1. Check if any matches have been played (safety check)
        $hasPlayedMatches = $tournament->matches()->whereNotNull('game_session_id')->exists();
        if ($hasPlayedMatches) {
            throw new \Exception('Tidak dapat mengacak ulang. Sebagian pertandingan sudah dimainkan.');
        }

        // 2. Delete all matches
        $tournament->matches()->delete();

        // 3. Re-generate
        $teams = $tournament->teams;
        $this->generateBracket($tournament, $teams);
    }
    
    /**
     * Advance a winner to the next bracket
     */
    public function advanceWinner(TournamentMatch $match, TournamentTeam $winner)
    {
        // 1. Set winner in current match
        $match->update(['winner_team_id' => $winner->id]);
        
        // 2. Find next match
        if ($match->next_match_id) {
            $nextMatch = TournamentMatch::find($match->next_match_id);
            
            // Logic: High seed (Odd match number) goes to Team 1, Low seed (Even match number) goes to Team 2
            // Since we used $m as match_number inside the round:
            if ($match->match_number % 2 != 0) {
                 $nextMatch->update(['team_1_id' => $winner->id]);
            } else {
                 $nextMatch->update(['team_2_id' => $winner->id]);
            }
        } else {
            // This was the final match
            $match->tournament->update(['status' => 'completed']);
        }
    }
}

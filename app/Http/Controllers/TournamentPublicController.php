<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\GameSession;

class TournamentPublicController extends Controller
{
    /**
     * Verify tournament PIN (called via AJAX from setup page)
     */
    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:6']);
        
        $tournament = Tournament::where('pin', $request->pin)->first();
        
        if ($tournament) {
            return response()->json([
                'success' => true,
                'is_tournament' => true,
                'message' => 'PIN valid untuk Mode Turnamen: ' . $tournament->title,
                'tournament' => [
                    'id' => $tournament->id,
                    'title' => $tournament->title,
                    'pin' => $tournament->pin,
                    'status' => $tournament->status,
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'is_tournament' => false,
        ]);
    }

    /**
     * Show public tournament bracket
     */
    public function bracket(Request $request)
    {
        $pin = $request->query('pin');
        
        if (!$pin) {
            return redirect()->route('game.setup')->with('error', 'PIN turnamen tidak valid.');
        }
        
        $tournament = Tournament::where('pin', $pin)->first();
        
        if (!$tournament) {
            return redirect()->route('game.setup')->with('error', 'Turnamen tidak ditemukan.');
        }
        
        // Determine whether to allow opening all playable matches
        $forceOpenAll = $this->shouldForceOpenAllMatches($tournament);

        if ($forceOpenAll && $this->needsBracketRepair($tournament)) {
            $this->repairBracketAfterHistoryPurge($tournament);
            $tournament->refresh();
        }

        $tournament->load([
            'teams', 
            'matches.team1', 
            'matches.team2', 
            'matches.winner', 
            'matches.gameSession',
            'matches.topic',
            'matches.material'
        ]);
        
        // Group matches by round, then sort each group by match_number
        $matchesByRound = $tournament->matches
            ->groupBy('round')
            ->sortKeys()
            ->map(function ($matches) {
                return $matches->sortBy('match_number')->values();
            });
        
        // Get playable match IDs (all or just next, based on rule)
        $playableMatchIds = $this->getPlayableMatchIds($tournament, $forceOpenAll);
        
        return view('game.tournament-bracket', compact('tournament', 'matchesByRound', 'playableMatchIds', 'forceOpenAll'));
    }

    /**
     * Decide if all playable matches should be opened (e.g., history removed but status is active)
     */
    private function shouldForceOpenAllMatches(Tournament $tournament): bool
    {
        if ($tournament->status !== 'active') {
            return false;
        }

        $hasAnyHistory = $tournament->matches()->whereNotNull('game_session_id')->exists();

        if ($hasAnyHistory) {
            return false;
        }

        // Specific recovery for tournament PIN 876549 (history cleared)
        return $tournament->pin === '876549';
    }

    /**
     * Check if the bracket needs repair after history purge
     */
    private function needsBracketRepair(Tournament $tournament): bool
    {
        return $tournament->matches()
            ->whereNotNull('winner_team_id')
            ->whereNull('game_session_id')
            ->exists();
    }

    /**
     * Reset results and re-apply BYE advancements after history deletion
     */
    private function repairBracketAfterHistoryPurge(Tournament $tournament): void
    {
        DB::transaction(function () use ($tournament) {
            $tournament->matches()->update([
                'game_session_id' => null,
                'winner_team_id' => null,
            ]);

            $tournament->matches()
                ->where('round', '>', 1)
                ->update([
                    'team_1_id' => null,
                    'team_2_id' => null,
                ]);

            $service = app(\App\Services\TournamentService::class);

            $byes = $tournament->matches()
                ->where('round', 1)
                ->whereNull('team_2_id')
                ->whereNotNull('team_1_id')
                ->get();

            foreach ($byes as $bye) {
                $bye->update(['winner_team_id' => $bye->team_1_id]);
                $bye = $bye->fresh(['winner']);

                if ($bye->winner) {
                    $service->advanceWinner($bye, $bye->winner);
                }
            }
        });
    }

    /**
     * Get playable match IDs (all or next only)
     */
    private function getPlayableMatchIds(Tournament $tournament, bool $forceOpenAll): array
    {
        $matches = $tournament->matches()
            ->orderBy('round')
            ->orderBy('match_number')
            ->get();

        $playable = [];

        foreach ($matches as $match) {
            if ($match->winner_team_id) continue;
            if (!$match->team_1_id || !$match->team_2_id) continue;
            if (!$match->is_ready) continue;

            $playable[] = $match->id;

            if (!$forceOpenAll) {
                break;
            }
        }

        return $playable;
    }

    /**
     * Start a match from public bracket
     */
    public function startMatch(TournamentMatch $match)
    {
        // Validate
        if ($match->game_session_id && $match->gameSession && $match->gameSession->status !== 'finished') {
            // Resume existing session
            session(['current_game_session_id' => $match->game_session_id]);
            session(['tournament_pin' => $match->tournament->pin]);
            return redirect()->route('game.play', ['session_id' => $match->game_session_id, 'fs_request' => 1]);
        }

        if (!$match->team_1_id || !$match->team_2_id) {
            return redirect()->back()->with('error', 'Tim belum lengkap!');
        }

        if (!$match->is_ready) {
            return redirect()->back()->with('error', 'Match belum dikonfigurasi!');
        }

        $tournament = $match->tournament;

        // Create Game Session
        $session = GameSession::create([
            'title' => "R{$match->round} M{$match->match_number} - {$tournament->title}",
            'game_mode' => 'tournament',
            'topic_id' => $match->topic_id,
            'material_id' => $match->material_id,
            'session_pin' => null, // No PIN for tournament matches
            'team_blue_name' => $match->team2->name,
            'team_red_name' => $match->team1->name,
            'team_blue_score' => 0,
            'team_red_score' => 0,
            'total_questions' => $match->total_questions,
            'time_per_question' => $match->time_per_question,
            'pull_strength' => 50,
            'status' => 'waiting',
            'created_by' => null,
        ]);

        // Link Session to Match
        $match->update(['game_session_id' => $session->id]);

        // Store in session for game.play to pick up
        session(['current_game_session_id' => $session->id]);
        session(['tournament_pin' => $tournament->pin]);

        return redirect()->route('game.play', ['session_id' => $session->id, 'fs_request' => 1]);
    }
}

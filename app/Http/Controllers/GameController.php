<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\GameRound;

class GameController extends Controller
{
    public function index()
    {
        return view('game.index');
    }

    public function setup()
    {
        return view('game.setup');
    }
    
    public function play(Request $request)
    {
        $session = null;
        
        // Try to find session by session_id first
        $sessionId = $request->query('session_id');
        if ($sessionId) {
            $session = GameSession::with('topic.questions')->find($sessionId);
        }
        
        // If not found, try by PIN
        if (!$session) {
            $pin = $request->query('pin');
            if ($pin) {
                $session = GameSession::with('topic.questions')
                    ->where('session_pin', $pin)
                    ->first();
            }
        }
        
        if (!$session) {
            return redirect()->route('game.setup')->with('error', 'Sesi tidak ditemukan.');
        }

        if (!$session->topic) {
             return redirect()->route('game.setup')->with('error', 'Topik soal tidak ditemukan dalam sesi ini.');
        }

        $allQuestions = $session->material_id 
            ? Question::where('material_id', $session->material_id)->get()
            : $session->topic->questions;
        
        // Use limit from query param (setup config) OR session saved config OR default to all questions
        $limit = $request->query('questions') 
            ? (int)$request->query('questions') 
            : ($session->total_questions ?? $allQuestions->count());
            
        // If limit is 0 (from "Semua" option maybe) or greater than available, use count
        if($limit <= 0 || $limit > $allQuestions->count()) $limit = $allQuestions->count();

        // 1. DETERMINISTIC SHUFFLE
        // Use session ID as seed so the order is preserved on refresh
        $seed = $session->id;
        $allQuestions->shuffle($seed); // Shuffle directly modifies or returns? Laravel collection shuffle returns new collection.
        // Wait, shuffle() with seed argument is supported in recent Laravel versions? 
        // Laravel's shuffle() simply uses PHP's shuffle() or random logic. It might not accept seed directly in all versions.
        // Safer approach: Use manual sort with seeded random.
        
        $shuffled = $allQuestions->sortBy(function($q) use ($seed) {
            mt_srand($seed + $q->id); // Seed based on session + question ID for consistent random weight
            return mt_rand();
        });

        // Split for teams (using same shuffled list is fine, they just compete)
        // Or if we want different orders for teams, we can seed differently: $seed + 1, $seed + 2
        
        // Team Red
        $teamRedQuestions = $shuffled->sortBy(function($q) use ($seed) {
            mt_srand($seed + $q->id + 100); 
            return mt_rand();
        })->take($limit)->values();

        // Team Blue
        $teamBlueQuestions = $shuffled->sortBy(function($q) use ($seed) {
            mt_srand($seed + $q->id + 200); 
            return mt_rand();
        })->take($limit)->values();

        // 2. CALCULATE RESUME INDICES
        $redIndex = \App\Models\GameRound::where('game_session_id', $session->id)
            ->whereNotNull('team_red_answer')->count();
        $blueIndex = \App\Models\GameRound::where('game_session_id', $session->id)
            ->whereNotNull('team_blue_answer')->count();

        return view('game.play', compact('session', 'teamRedQuestions', 'teamBlueQuestions', 'limit', 'redIndex', 'blueIndex'));
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $pin = $request->input('pin');
        
        // Find session with this PIN
        $session = GameSession::where('session_pin', $pin)->first();

        if ($session) {
            // Note: Archiving now happens in updateSession when game finishes.
            // Session with PIN should always be a clean lobby or active game.
            
            return response()->json([
                'success' => true,
                'message' => 'PIN Valid!',
                'session' => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'total_questions' => $session->material_id 
                        ? Question::where('material_id', $session->material_id)->count()
                        : ($session->topic ? $session->topic->questions()->count() : 0),
                    'game_mode' => $session->game_mode
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'PIN tidak ditemukan.'
            ], 404);
        }
    }

    public function updateSession(Request $request)
    {
        \Log::info('updateSession called', $request->all());
        
        $session = GameSession::find($request->input('session_id'));
        if (!$session) {
            \Log::warning('Session not found: ' . $request->input('session_id'));
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }
        
        \Log::info('Session found: ID=' . $session->id . ', Status=' . $session->status);

        if ($request->has('red_score')) $session->team_red_score = $request->input('red_score');
        if ($request->has('blue_score')) $session->team_blue_score = $request->input('blue_score');

        // Save duration to session for persistence across "Play Again"
        if ($request->has('duration') && $request->input('duration') > 0) {
            $session->time_per_question = $request->input('duration');
        }

        if ($request->has('winner')) {
            $session->winner_team = $request->input('winner');
        }

        if ($request->has('status')) {
            $inputStatus = $request->input('status');
            
            // Map non-enum values to valid enum values
            $statusMap = [
                'playing' => 'active',
                'completed' => 'finished',
            ];
            $newStatus = $statusMap[$inputStatus] ?? $inputStatus;
            
            // Only update if it's a valid enum value
            $validStatuses = ['waiting', 'active', 'finished'];
            if (in_array($newStatus, $validStatuses)) {
                $session->status = $newStatus;
            }

            // STATUS CHANGE LOGIC
            if ($newStatus == 'active' && !$session->started_at) {
                $session->started_at = now();
            }

            // INSTANT ARCHIVING: When game finishes, archive immediately
            // Only archive if session still has PIN (hasn't been archived yet)
            if ($newStatus == 'finished' && $session->session_pin) {
                $session->ended_at = now();

                // 1. Create a new LOBBY session with the same PIN for future games
                $newLobby = $session->replicate();
                $newLobby->status = 'waiting';
                $newLobby->team_red_score = 0;
                $newLobby->team_blue_score = 0;
                $newLobby->winner_team = null;
                $newLobby->started_at = null;
                $newLobby->ended_at = null;
                // session_pin is copied from original, so PIN persists for reuse
                $newLobby->save();
                
                // Store new lobby PIN for response
                $newLobbyPin = $newLobby->session_pin;

                // 2. Mark the CURRENT session as HISTORY (remove PIN)
                $session->session_pin = null;
                $session->title = ($session->title ?? 'Sesi') . ' [Riwayat #' . $session->id . ']';
            }

            // TOURNAMENT INTEGRATION: Check if this session belongs to a tournament match
            if ($newStatus == 'finished' && $session->game_mode == 'tournament') {
                $match = \App\Models\TournamentMatch::where('game_session_id', $session->id)->first();
                
                if ($match) {
                     // Determine Winner Team Model
                     $winnerTeam = null;
                     // Determine match winner based on color
                     // Logic synced with TournamentPublicController:
                     // Team 1 (Top) = Red
                     // Team 2 (Bottom) = Blue
                     
                     if ($session->winner_team == 'red') $winnerTeam = $match->team1;
                     if ($session->winner_team == 'blue') $winnerTeam = $match->team2;

                     if ($winnerTeam) {
                         $tournamentService = new \App\Services\TournamentService();
                         $tournamentService->advanceWinner($match, $winnerTeam);
                     }
                }
            }
        }

        $session->save();

        return response()->json([
            'success' => true, 
            'session_id' => $session->id,
            'new_lobby_pin' => $newLobbyPin ?? null
        ]);
    }

    public function cancelSession(Request $request)
    {
        $request->validate(['session_id' => 'required']);
        $session = GameSession::find($request->session_id);
        if ($session) {
            $session->delete();
        }
        return response()->json(['success' => true]);
    }

    public function submitAnswer(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:game_sessions,id',
            'team' => 'required|in:red,blue',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable',
            'is_correct' => 'required|boolean',
            'round_index' => 'integer'
        ]);

        $round = new GameRound();
        $round->game_session_id = $validated['session_id'];
        $round->question_id = $validated['question_id'];
        $round->round_number = $request->input('round_index', 0);
        
        $answer = $validated['answer'];
        if (is_array($answer)) {
            $answer = json_encode($answer);
        }

        if ($validated['team'] === 'red') {
            $round->team_red_answer = (string)$answer;
            $round->team_red_correct = $validated['is_correct'];
        } else {
            $round->team_blue_answer = (string)$answer;
            $round->team_blue_correct = $validated['is_correct'];
        }
        
        $round->save();

        return response()->json(['success' => true]);
    }

    public function review($sessionId)
    {
        $session = GameSession::findOrFail($sessionId);
        
        // Ensure deterministic order by ID (creation sequence)
        $rounds = GameRound::where('game_session_id', $sessionId)
            ->with('question')
            ->orderBy('id', 'asc') 
            ->get();
            
        $tempReviews = [];
        foreach($rounds as $round) {
            $qid = $round->question_id;
            
            // Skip if question deleted
            if(!$round->question) continue;

            if (!isset($tempReviews[$qid])) {
                $tempReviews[$qid] = [
                    'question' => $round->question,
                    'red' => null,
                    'blue' => null,
                    'correct_keys' => $this->normalizeAnswer($round->question->correct_answer)
                ];
            }
            
            // Merge Red Data (Overwrite if newer exists, maintain persistence)
            if ($round->team_red_answer !== null || $round->team_red_correct) {
                 $tempReviews[$qid]['red'] = [
                     'raw' => $round->team_red_answer,
                     'display' => $this->decodeAnswer($round->team_red_answer),
                     'is_correct' => $round->team_red_correct
                 ];
                 $tempReviews[$qid]['red']['keys'] = array_map(function($k) { 
                     return strtoupper(trim((string)$k)); 
                 }, $tempReviews[$qid]['red']['display']);
            }

            // Merge Blue Data
            if ($round->team_blue_answer !== null || $round->team_blue_correct) {
                 $tempReviews[$qid]['blue'] = [
                     'raw' => $round->team_blue_answer,
                     'display' => $this->decodeAnswer($round->team_blue_answer),
                     'is_correct' => $round->team_blue_correct
                 ];
                 $tempReviews[$qid]['blue']['keys'] = array_map(function($k) { 
                     return strtoupper(trim((string)$k)); 
                 }, $tempReviews[$qid]['blue']['display']);
            }
        }
        
        $reviews = collect($tempReviews)
            ->sortBy(function ($item) {
                return $item['question']->id;
            })
            ->values();
        
        // Find active lobby with same topic/material for "Play Again" feature
        $activeLobbyPin = null;
        $lobbyQuery = GameSession::where('status', 'waiting')
            ->whereNotNull('session_pin');
        
        if ($session->material_id) {
            $lobbyQuery->where('material_id', $session->material_id);
        } elseif ($session->topic_id) {
            $lobbyQuery->where('topic_id', $session->topic_id);
        }
        
        $activeLobby = $lobbyQuery->first();
        if ($activeLobby) {
            $activeLobbyPin = $activeLobby->session_pin;
        }
        
        return view('game.review', compact('session', 'reviews', 'activeLobbyPin'));
    }

    protected function normalizeAnswer($raw) {
        $keys = [];
        if (is_string($raw)) {
            // Try JSON first
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $keys = $decoded;
            } 
            // Then comma separated check (only if looks like list)
            elseif (str_contains($raw, ',')) {
                $keys = explode(',', $raw);
            } 
            else {
                $keys = [$raw];
            }
        } elseif (is_array($raw)) {
            $keys = $raw;
        }
        return array_map(function($k) { return strtoupper(trim((string)$k)); }, $keys);
    }

    protected function decodeAnswer($raw) {
        if(is_null($raw) || $raw === '') return []; // Handle empty string correctly
        $decoded = json_decode($raw);
        if(json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        return [$raw];
    }
}

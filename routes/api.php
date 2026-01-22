<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Topic;
use App\Models\Question;
use App\Models\GameSession;
use App\Models\GameRound;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Topics
Route::get('/topics', function () {
    return Topic::withCount('questions')->get();
});

Route::get('/topics/{id}', function ($id) {
    return Topic::with('questions')->findOrFail($id);
});

Route::get('/topics/{id}/questions', function ($id, Request $request) {
    $limit = $request->get('limit', 10);
    return Question::where('topic_id', $id)
        ->inRandomOrder()
        ->limit($limit)
        ->get();
});

// Questions
Route::get('/questions', function (Request $request) {
    $query = Question::with('topic');
    
    if ($request->has('topic_id')) {
        $query->where('topic_id', $request->topic_id);
    }
    
    return $query->paginate($request->get('per_page', 15));
});

Route::get('/questions/random', function (Request $request) {
    $topicId = $request->get('topic_id');
    $count = $request->get('count', 10);
    
    $query = Question::query();
    
    if ($topicId) {
        $query->where('topic_id', $topicId);
    }
    
    return $query->inRandomOrder()->limit($count)->get();
});

// Get questions by session PIN
Route::get('/questions/by-pin/{pin}', function ($pin, Request $request) {
    $count = $request->get('count', 10);
    
    // Find session by PIN (will find the active lobby, NOT archived history)
    $session = GameSession::where('session_pin', $pin)->first();
    
    if (!$session) {
        return response()->json([
            'success' => false,
            'message' => 'PIN tidak ditemukan'
        ], 404);
    }

    // Note: Archiving now happens in updateSession when game finishes.
    // At this point, session should already be a clean lobby (status='waiting').
    // If somehow it's finished (edge case), just return it and let the game proceed.
    
    // Get questions based on material_id or topic_id
    $query = Question::query();
    
    if ($session->material_id) {
        $query->where('material_id', $session->material_id);
    } elseif ($session->topic_id) {
        $query->where('topic_id', $session->topic_id);
    }
    
    $questions = $query->inRandomOrder()->limit($count)->get();
    
    return response()->json([
        'success' => true,
        'session' => $session,
        'questions' => $questions
    ]);
});

Route::get('/questions/{id}', function ($id) {
    return Question::with('topic')->findOrFail($id);
});

// Game Sessions
Route::post('/game/start', function (Request $request) {
    $validated = $request->validate([
        'topic_id' => 'required|exists:topics,id',
        'title' => 'nullable|string|max:255',
        'game_mode' => 'nullable|in:race,turn_based',
        'team_blue_name' => 'nullable|string|max:100',
        'team_red_name' => 'nullable|string|max:100',
        'total_questions' => 'nullable|integer|min:1|max:50',
        'time_per_question' => 'nullable|integer|min:10|max:120',
    ]);
    
    $session = GameSession::create([
        'topic_id' => $validated['topic_id'],
        'title' => $validated['title'] ?? null,
        'game_mode' => $validated['game_mode'] ?? 'race',
        'team_blue_name' => $validated['team_blue_name'] ?? 'Tim Biru',
        'team_red_name' => $validated['team_red_name'] ?? 'Tim Merah',
        'total_questions' => $validated['total_questions'] ?? 10,
        'time_per_question' => $validated['time_per_question'] ?? 30,
        'started_at' => now(),
    ]);
    
    return response()->json([
        'success' => true,
        'session' => $session,
    ]);
});

Route::get('/game/{id}', function ($id) {
    return GameSession::with(['topic', 'rounds.question'])->findOrFail($id);
});

Route::post('/game/{id}/round', function ($id, Request $request) {
    $session = GameSession::findOrFail($id);
    
    $validated = $request->validate([
        'question_id' => 'required|exists:questions,id',
        'round_number' => 'required|integer|min:1',
        'team_blue_answer' => 'nullable|string|max:10',
        'team_red_answer' => 'nullable|string|max:10',
        'team_blue_time_ms' => 'nullable|integer',
        'team_red_time_ms' => 'nullable|integer',
        'winner_team' => 'nullable|in:blue,red,draw,none',
        'points_awarded' => 'nullable|integer',
        'rope_position_after' => 'nullable|integer',
    ]);
    
    $round = GameRound::create([
        'game_session_id' => $session->id,
        'question_id' => $validated['question_id'],
        'round_number' => $validated['round_number'],
        'team_blue_answer' => $validated['team_blue_answer'] ?? null,
        'team_red_answer' => $validated['team_red_answer'] ?? null,
        'team_blue_time_ms' => $validated['team_blue_time_ms'] ?? null,
        'team_red_time_ms' => $validated['team_red_time_ms'] ?? null,
    ]);
    
    // Process answers
    $round->processAnswers($session->game_mode);
    
    // Update session scores
    if ($round->winner_team === 'blue') {
        $session->increment('team_blue_score', $round->points_awarded);
    } elseif ($round->winner_team === 'red') {
        $session->increment('team_red_score', $round->points_awarded);
    }
    
    return response()->json([
        'success' => true,
        'round' => $round->fresh(),
        'session' => $session->fresh(),
    ]);
});

Route::post('/game/{id}/end', function ($id, Request $request) {
    $session = GameSession::findOrFail($id);
    
    $session->update([
        'ended_at' => now(),
        'winner_team' => $session->determineWinner(),
    ]);
    
    return response()->json([
        'success' => true,
        'session' => $session->fresh(['rounds']),
    ]);
});

Route::get('/game/history', function (Request $request) {
    return GameSession::with('topic')
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 10));
});

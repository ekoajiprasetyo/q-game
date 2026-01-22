<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentTeam;
use App\Models\Topic;

class TournamentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'admin') {
            $tournaments = Tournament::latest()->paginate(10);
        } else {
            $tournaments = Tournament::where('created_by', $user->id)->latest()->paginate(10);
        }
        return view('admin.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('admin.tournaments.create');
    }

    public function store(Request $request, \App\Services\TournamentService $tournamentService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'team_names' => 'required|array|min:3|max:16',
            'team_names.*' => 'required|string|max:255',
        ]);

        // Generate unique 6-digit PIN
        do {
            $pin = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Tournament::where('pin', $pin)->exists());

        // 1. Create Tournament
        $tournament = Tournament::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'setup',
            'pin' => $pin,
            'created_by' => auth()->id(),
        ]);

        // 2. Create Teams
        $teams = collect();
        foreach ($request->team_names as $index => $name) {
            $teams->push(TournamentTeam::create([
                'tournament_id' => $tournament->id,
                'name' => $name,
                'seed_number' => $index + 1,
            ]));
        }

        // 3. Generate Bracket
        $tournamentService->generateBracket($tournament, $teams);

        return redirect()->route('admin.tournaments.show', $tournament)
            ->with('success', 'Turnamen berhasil dibuat! PIN: ' . $pin);
    }

    public function show(Tournament $tournament)
    {
        $tournament->load(['teams', 'matches.team1', 'matches.team2', 'matches.winner', 'matches.gameSession', 'matches.topic', 'matches.material']);
        
        // Group matches by round for easy display
        $matchesByRound = $tournament->matches->sortBy('match_number')->groupBy('round');
        
        // Get topics for match configuration (filtered by user)
        $user = auth()->user();
        if ($user->role === 'admin') {
            $topics = Topic::with('materials')->get();
        } else {
            $topics = Topic::with('materials')->where('created_by', $user->id)->get();
        }
        
        return view('admin.tournaments.show', compact('tournament', 'matchesByRound', 'topics'));
    }

    /**
     * Configure a match with topic/material/questions/time
     */
    public function configureMatch(Request $request, TournamentMatch $match)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'material_id' => 'required|exists:materials,id',
            'total_questions' => 'required|integer|min:1|max:50',
            'time_per_question' => 'required|integer|min:10|max:3600',
        ]);

        $match->update([
            'topic_id' => $request->topic_id,
            'material_id' => $request->material_id,
            'total_questions' => $request->total_questions,
            'time_per_question' => $request->time_per_question,
            'is_ready' => true,
        ]);

        return redirect()->back()->with('success', 'Konfigurasi match berhasil disimpan!');
    }

    public function reshuffle(Tournament $tournament, \App\Services\TournamentService $tournamentService)
    {
        try {
            $tournamentService->resetBracket($tournament);
            return redirect()->back()->with('success', 'Bagan pertandingan berhasil diacak ulang!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a tournament and all related data
     */
    public function destroy(Tournament $tournament)
    {
        // Check ownership for non-admin
        $user = auth()->user();
        if ($user->role !== 'admin' && $tournament->created_by !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Delete related matches first (cascade should handle this, but being explicit)
        $tournament->matches()->delete();
        
        // Delete teams
        $tournament->teams()->delete();
        
        // Delete tournament
        $tournament->delete();

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Turnamen berhasil dihapus!');
    }
}

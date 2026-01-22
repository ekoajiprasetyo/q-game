<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Material;
use App\Models\Question;
use App\Models\GameSession;
use App\Models\GameRound;
use App\Models\Tournament;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $isAdmin = Auth::user()->role === 'admin';
        $userId = Auth::id();

        // 1. Statistics
        $topicsQuery = Topic::query();
        $materialsQuery = Material::query();
        $questionsQuery = Question::query();
        $sessionsQuery = GameSession::query()->whereIn('status', ['finished', 'completed']);
        $roundsQuery = GameRound::query();
        $tournamentsQuery = Tournament::query();

        if (!$isAdmin) {
            // Filter Topics
            $topicsQuery->where('created_by', $userId);
            
            // Filter Materials (via Topic)
            $materialsQuery->whereHas('topic', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });

            // Filter Questions
            $questionsQuery->where('created_by', $userId);

            $sessionsQuery->whereHas('topic', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });

            // Filter Rounds (via GameSession -> Topic)
            $roundsQuery->whereHas('gameSession.topic', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });
            
            // Filter Tournaments
            $tournamentsQuery->where('created_by', $userId);
        }

        $totalTopics = $topicsQuery->count();
        $totalMaterials = $materialsQuery->count();
        $totalQuestions = $questionsQuery->count();
        $totalSessions = $sessionsQuery->count();
        $totalRounds = $roundsQuery->count();

        // 2. Recent Lists
        
        // Recent Topics
        $recentTopicsQuery = Topic::withCount(['materials', 'questions']);
        if (!$isAdmin) {
            $recentTopicsQuery->where('created_by', $userId);
        }
        $recentTopics = $recentTopicsQuery->latest()->limit(5)->get();

        // Recent Materials
        $recentMaterialsQuery = Material::with(['topic', 'activeSession'])->withCount('questions');
        if (!$isAdmin) {
            $recentMaterialsQuery->whereHas('topic', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }
        $recentMaterials = $recentMaterialsQuery->latest()->limit(5)->get();
        
        // Recent Tournaments
        $recentTournamentsQuery = Tournament::query();
        if (!$isAdmin) {
            $recentTournamentsQuery->where('created_by', $userId);
        }
        $recentTournaments = $recentTournamentsQuery->latest()->limit(5)->get();

        // Recent Sessions - Only show finished games
        $recentSessionsQuery = GameSession::with(['topic', 'material'])
            ->whereIn('status', ['finished', 'completed']);
        if (!$isAdmin) {
             $recentSessionsQuery->whereHas('topic', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }
        $recentSessions = $recentSessionsQuery->latest()->limit(5)->get();
        
        // Post-process titles
        foreach($recentSessions as $session) {
            $session->custom_title = $this->getSequentialTitle($session);
        }

        return view('admin.dashboard', compact(
            'totalTopics',
            'totalMaterials',
            'totalQuestions',
            'totalSessions',
            'totalRounds',
            'recentTopics',
            'recentMaterials',
            'recentTournaments',
            'recentSessions'
        ));
    }

    private function getSequentialTitle($session) {
        $userId = $session->created_by;
        $matId = $session->material_id ?? 0;
        
        if(!$matId) return $session->topic ? $session->topic->name : $session->title;

        // Find Break Point
        $breakPoint = GameSession::where('created_by', $userId)
                        ->where('id', '<', $session->id)
                        ->where('material_id', '!=', $matId)
                        ->orderBy('id', 'desc')
                        ->first();
        
        $breakId = $breakPoint ? $breakPoint->id : 0;
        
        // Count Streak
        $count = GameSession::where('created_by', $userId)
                    ->where('material_id', $matId)
                    ->where('id', '>', $breakId)
                    ->where('id', '<=', $session->id)
                    ->count();

        $baseName = $session->material ? $session->material->name : ($session->topic ? $session->topic->name : $session->title);
        return $count > 1 ? "$baseName [Game #$count]" : $baseName;
    }
}

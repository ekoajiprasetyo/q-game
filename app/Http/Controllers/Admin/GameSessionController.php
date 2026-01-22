<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\Material;

class GameSessionController extends Controller
{
    /**
     * Display a listing of game sessions.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->role === 'admin';

        // 1. Base Query
        $query = GameSession::with(['topic', 'material'])->whereIn('status', ['finished', 'completed']);

        // 2. RBAC
        if (!$isAdmin) {
            $query->where('created_by', $userId);
        }

        // 3. Search Title / Topic / Material
        if ($request->filled('search')) { 
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('title', 'like', "%{$search}%")
                   ->orWhereHas('topic', function($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                   })
                   ->orWhereHas('material', function($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                   });
             });
        }
        
        // 4. Filters
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }
        
        if ($request->filled('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        $sessions = $query->latest()->paginate(10);
        
        // Post-process Titles
        foreach($sessions as $session) {
            $session->custom_title = $this->getSequentialTitle($session);
        }
        
        // Load Filter Data
        $topicsQuery = Topic::query();
        $materialsQuery = Material::query();

        if (!$isAdmin) {
            $topicsQuery->where('created_by', $userId);
            $materialsQuery->whereHas('topic', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }
        
        $topics = $topicsQuery->get();
        $materials = $materialsQuery->get();

        return view('admin.sessions.index', compact('sessions', 'topics', 'materials'));
    }

    /**
     * Display the specified game session.
     */
    public function show(GameSession $session)
    {
        // RBAC Check
        if (Auth::user()->role !== 'admin' && $session->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $session->load(['topic', 'rounds.question']);
        return view('admin.sessions.show', compact('session'));
    }

    public function modalContent(GameSession $session)
    {
        // RBAC Check
        if (Auth::user()->role !== 'admin' && $session->created_by !== Auth::id()) {
            abort(403);
        }

        $session->load(['topic', 'rounds.question']);
        return view('admin.sessions.modal_content', compact('session'));
    }

    /**
     * Remove the specified game session.
     */
    public function destroy(GameSession $session)
    {
        // RBAC Check
        if (Auth::user()->role !== 'admin' && $session->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses menghapus sesi ini.');
        }

        $session->rounds()->delete();
        $session->delete();

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', 'Riwayat game berhasil dihapus!');
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

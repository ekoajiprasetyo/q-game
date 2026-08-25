<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\Material;
use App\Models\SurpriseSession;

class GameSessionController extends Controller
{
    /**
     * Display a listing of game sessions.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->role === 'admin';

        // 1. Base Query - Include 'active' for better visibility locally
        $query = GameSession::with(['topic', 'material'])->whereIn('status', ['finished', 'completed', 'active']);

        // 2. RBAC - DISABLED FOR DEBUGGING
        // if (!$isAdmin) {
        //     $query->where('created_by', $userId);
        // }

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

        $surpriseSessions = SurpriseSession::with(['topic', 'material', 'teams'])
            ->where('status', 'finished')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('topic_id'), fn ($query) => $query->where('topic_id', $request->topic_id))
            ->when($request->filled('material_id'), fn ($query) => $query->where('material_id', $request->material_id))
            ->latest('ended_at')
            ->get();

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

        return view('admin.sessions.index', compact('sessions', 'surpriseSessions', 'topics', 'materials'));
    }

    /**
     * Display the specified game session.
     */
    public function show(GameSession $session)
    {
        // RBAC Check - DISABLED
        // if (Auth::user()->role !== 'admin' && $session->created_by !== Auth::id()) {
        //     abort(403, 'Anda tidak memiliki akses.');
        // }

        $session->load(['topic', 'rounds.question']);
        return view('admin.sessions.show', compact('session'));
    }

    public function modalContent(GameSession $session)
    {
        try {
            // RBAC Check - DISABLED
            // if (Auth::user()->role !== 'admin' && $session->created_by !== Auth::id()) {
            //     abort(403);
            // }

            // Safe Loading
            $session->load(['topic']);

            // Load Rounds and Question separately to debug potential relation errors
            $session->load(['rounds' => function($q) {
                $q->orderBy('round_number', 'asc');
            }, 'rounds.question']);

            return view('admin.sessions.modal_content', compact('session'));

        } catch (\Throwable $e) {
            // FORCE RETURN 200 OK so the frontend displays this error message in the modal body
            return response()->make(
                '<div style="padding:40px; color:#DC2626; text-align:center; font-family:sans-serif;">' .
                '<h3 style="margin-bottom:10px;">⚠️ Server Error</h3>' .
                '<p style="font-weight:bold; font-size:1.1em;">' . $e->getMessage() . '</p>' .
                '<div style="background:#FEF2F2; padding:15px; border-radius:8px; margin-top:15px; text-align:left; font-family:monospace; font-size:0.9em; border:1px solid #FECACA;">' .
                'Loop error or Data issue.<br>' .
                'File: ' . basename($e->getFile()) . ' (Line ' . $e->getLine() . ')' .
                '</div>' .
                '</div>',
                200
            );
        }
    }

    /**
     * Remove the specified game session.
     */
    public function destroy(GameSession $session)
    {
        // RBAC Check - Fix strict string/int mismatch & Allow cleanup of null sessions
        if (Auth::user()->role !== 'admin' && $session->created_by && $session->created_by != Auth::id()) {
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials for a topic.
     */
    public function index(Request $request, Topic $topic)
    {
        // Access Check
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke topik ini.');
        }

        $query = $topic->materials()->with('activeSession')->withCount('questions');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $materials = $query->latest()->paginate(9);

        return view('admin.materials.index', compact('topic', 'materials'));
    }

    /**
     * Show the form for creating a new material.
     */
    public function create(Topic $topic)
    {
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }
        return view('admin.materials.create', compact('topic'));
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request, Topic $topic)
    {
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $topic->materials()->create($validated);

        return redirect()
            ->route('admin.topics.materials.index', $topic)
            ->with('success', 'Materi berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a material.
     */
    public function edit(Topic $topic, Material $material)
    {
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }
        return view('admin.materials.edit', compact('topic', 'material'));
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, Topic $topic, Material $material)
    {
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $material->update($validated);

        return redirect()
            ->route('admin.topics.materials.index', $topic)
            ->with('success', 'Materi berhasil diperbarui!');
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Topic $topic, Material $material)
    {
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Check if material has questions
        if ($material->questions()->exists()) {
            return redirect()
                ->route('admin.topics.materials.index', $topic)
                ->with('error', 'Tidak dapat menghapus materi yang memiliki pertanyaan. Hapus pertanyaan terlebih dahulu.');
        }

        $material->delete();

        return redirect()
            ->route('admin.topics.materials.index', $topic)
            ->with('success', 'Materi berhasil dihapus!');
    }

    /**
     * Generate PIN specifically for this material to start a game session.
     */
    public function generatePin(Topic $topic, Material $material)
    {
        // Access Check
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }



        // Check for existing waiting session
        $session = \App\Models\GameSession::where('material_id', $material->id)
            ->where('status', 'waiting')
            ->latest()
            ->first();

        if (!$session) {
            // Try to reuse PIN from previous session of this material
            $lastSession = \App\Models\GameSession::where('material_id', $material->id)->latest()->first();
            $pin = null;

            if ($lastSession) {
                $oldPin = $lastSession->session_pin;
                // Check if this PIN is currently in use by an active session
                $isConflict = \App\Models\GameSession::where('session_pin', $oldPin)
                    ->whereIn('status', ['waiting', 'playing'])
                    ->exists();
                
                if (!$isConflict) {
                    $pin = $oldPin;
                }
            }

            if (!$pin) {
                $pin = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                // Ensure unique PIN
                while(\App\Models\GameSession::where('session_pin', $pin)->whereIn('status', ['waiting', 'playing'])->exists()) {
                    $pin = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                }
            }
            
            $session = \App\Models\GameSession::create([
                'title' => 'Sesi: ' . $material->name,
                'game_mode' => 'race', // Default mode for tug of war
                'topic_id' => $topic->id,
                'material_id' => $material->id,
                'session_pin' => $pin,
                'status' => 'waiting',
                'total_questions' => $material->questions()->count(),
                'created_by' => Auth::id(),
                'team_blue_name' => 'Tim Biru',
                'team_red_name' => 'Tim Merah',
                'pull_strength' => 10,
                'time_per_question' => 30, // Default time
            ]);
        }
        
        return response()->json([
            'success' => true,
            'pin' => $session->session_pin,
            'material_name' => $material->name,
            'question_count' => $session->total_questions
        ]);
    }
}

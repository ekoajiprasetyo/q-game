<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
    /**
     * Display a listing of topics.
     */
    public function index(Request $request)
    {
        $query = Topic::withCount(['materials', 'questions'])
            ->with(['materials' => function ($q) {
                $q->limit(5);
            }]);

        // Access Control: Teacher can only see their own topics
        if (Auth::user()->role !== 'admin') {
            $query->where('created_by', Auth::id());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        $topics = $query->latest()->paginate(9);
        
        // Subject list for filter dropdown (need to scope this too if we want strict privacy, 
        // but seeing other subjects might be useful for standardization. Let's scope it to be safe)
        $subjectQuery = Topic::distinct();
        if (Auth::user()->role !== 'admin') {
            $subjectQuery->where('created_by', Auth::id());
        }
        $subjects = $subjectQuery->pluck('subject')->filter();

        return view('admin.topics.index', compact('topics', 'subjects'));
    }

    /**
     * Show the form for creating a new topic.
     */
    public function create()
    {
        $subjectQuery = Topic::distinct();
        if (Auth::user()->role !== 'admin') {
            $subjectQuery->where('created_by', Auth::id());
        }
        $subjects = $subjectQuery->pluck('subject')->filter();

        return view('admin.topics.create', compact('subjects'));
    }

    /**
     * Store a newly created topic.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        // Assign creator
        $validated['created_by'] = Auth::id();

        Topic::create($validated);

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Topik berhasil dibuat!');
    }

    /**
     * Display the specified topic (redirect to materials).
     */
    public function show(Topic $topic)
    {
        // Access Check
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke topik ini.');
        }

        return redirect()->route('admin.topics.materials.index', $topic);
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(Topic $topic)
    {
        // Access Check
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit topik ini.');
        }

        $subjectQuery = Topic::distinct();
        if (Auth::user()->role !== 'admin') {
            $subjectQuery->where('created_by', Auth::id());
        }
        $subjects = $subjectQuery->pluck('subject')->filter();

        return view('admin.topics.edit', compact('topic', 'subjects'));
    }

    /**
     * Update the specified topic.
     */
    public function update(Request $request, Topic $topic)
    {
        // Access Check
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate topik ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $topic->update($validated);

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Topik berhasil diperbarui!');
    }

    /**
     * Remove the specified topic.
     */
    public function destroy(Topic $topic)
    {
        // Access Check
        if (Auth::user()->role !== 'admin' && $topic->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus topik ini.');
        }

        // Check if topic has materials
        if ($topic->materials()->count() > 0) {
            return redirect()
                ->route('admin.topics.index')
                ->with('error', 'Tidak dapat menghapus topik yang memiliki materi. Hapus semua materi terlebih dahulu.');
        }
        
        // Also check direct questions
        if ($topic->questions()->count() > 0) {
             return redirect()
                ->route('admin.topics.index')
                ->with('error', 'Tidak dapat menghapus topik yang memiliki soal. Hapus semua soal terlebih dahulu.');
        }

        $topic->delete();

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Topik berhasil dihapus!');
    }
}

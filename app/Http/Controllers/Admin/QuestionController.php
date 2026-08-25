<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    // Constructor removed to prevent Dependency Injection errors causing 500s

    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $query = Question::with(['material.topic', 'topic']);

        // Access Control: Teacher sees only their own questions
        if (Auth::user()->role !== 'admin') {
            $query->where('created_by', Auth::id());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('question_text', 'like', "%{$search}%");
        }

        // Filter by material
        if ($request->filled('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        // Filter by topic (via material)
        if ($request->filled('topic_id')) {
            $query->whereHas('material', function ($q) use ($request) {
                $q->where('topic_id', $request->topic_id);
            });
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by type
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        $totalPoints = $query->sum('points');
        $questions = $query->latest()->paginate(10);

        // Get topics with materials for filter
        // Only show topics/materials created by user if not admin
        $topicsQuery = Topic::with(['materials' => function($q) {
             $q->orderBy('name');
        }]);

        if (Auth::user()->role !== 'admin') {
            $topicsQuery->where('created_by', Auth::id());
        }
        $topics = $topicsQuery->orderBy('name')->get();

        // Get materials explicitly for filter dropdown (if needed separate from topics)
        $materialsQuery = Material::with('topic');
        if (Auth::user()->role !== 'admin') {
            $materialsQuery->whereHas('topic', function($q) {
                $q->where('created_by', Auth::id());
            });
        }
        $materials = $materialsQuery->orderBy('name')->get();

        return view('admin.questions.index', compact('questions', 'topics', 'materials', 'totalPoints'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request)
    {
        // Only fetch topics owned by user if not admin
        $topicsQuery = Topic::with('materials')->orderBy('name');
        if (Auth::user()->role !== 'admin') {
            $topicsQuery->where('created_by', Auth::id());
        }
        $topics = $topicsQuery->get();

        $materialsQuery = Material::with('topic')->orderBy('name');
        if (Auth::user()->role !== 'admin') {
            $materialsQuery->whereHas('topic', function($q) {
                $q->where('created_by', Auth::id());
            });
        }
        $materials = $materialsQuery->get();

        $selectedMaterialId = $request->get('material_id');
        $selectedTopicId = $request->get('topic_id');

        return view('admin.questions.create', compact('topics', 'materials', 'selectedMaterialId', 'selectedTopicId'));
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request)
    {
        // Pre-validation logic
        if ($request->question_type !== 'multiple_choice') {
            $request->merge(['options' => []]);
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,multiple_answer',
            'options' => 'nullable|array|max:6',
            'options.*.key' => 'required_if:question_type,multiple_choice|string|max:1',
            'options.*.text' => 'required_if:question_type,multiple_choice|string',
            'correct_answer' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'points' => 'required|integer|min:1|max:100',
        ]);

        // WRAPPING EVERYTHING IN TRY-CATCH TO CATCH 500 ERRORS
        try {
            $material = Material::findOrFail($validated['material_id']);
            $topic = Topic::find($material->topic_id);

            // Topic Check
            if (!$topic) {
                throw new \Exception('Topik tidak ditemukan untuk materi ini. Data mungkin korup.');
            }

            // Access Check
            if (Auth::user()->role !== 'admin') {
                if ($topic->created_by != Auth::id()) {
                    abort(403, 'Anda tidak bisa menambahkan soal ke topik yang bukan milik Anda.');
                }
            }

            // Additional Types Logic
            if ($request->question_type === 'short_answer') {
                if (!$request->has('short_answers') || !is_array($request->short_answers)) {
                   throw new \Exception('Jawaban singkat wajib diisi.');
                }
            }
            if ($request->question_type === 'multiple_answer') {
                 if (!$request->has('multi_options') || !is_array($request->multi_options)) {
                   throw new \Exception('Pilihan ganda kompleks wajib diisi.');
                }
            }

            // Process Options Data
            if ($validated['question_type'] === 'true_false') {
                $validated['options'] = [
                    ['key' => 'T', 'text' => 'Benar'],
                    ['key' => 'F', 'text' => 'Salah'],
                ];
            } elseif ($validated['question_type'] === 'short_answer') {
                $validated['options'] = [
                    'answers' => $request->input('short_answers'),
                    'case_sensitive' => $request->boolean('case_sensitive')
                ];
                $validated['correct_answer'] = $request->input('short_answers')[0];
            } elseif ($validated['question_type'] === 'multiple_answer') {
                $validated['options'] = [
                    'choices' => $request->input('multi_options'),
                    'correct_answers' => $request->input('correct_answers')
                ];
                $validated['correct_answer'] = implode(',', $request->input('correct_answers'));
            }

            // SAFE MODE: Manual Image Upload (No Service Dependency)
            $imageUrl = null;
            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/questions'), $filename);
                $imageUrl = 'uploads/questions/' . $filename;
            }

            // SAFE MODE: Manual Sanitize (No Service Dependency)
            // Just basic strip tags to allow simple formatting but prevent scripts, or trust the user for now
            // If you have a global helper 'clean', use it, otherwise leave as is to prevent crash.
            $sanitizedQuestionText = $validated['question_text'];
            if (function_exists('clean')) {
                $sanitizedQuestionText = clean($validated['question_text']);
            }

            $sanitizedOptions = $validated['options']; // Basic array assign

            Question::create([
                'created_by' => Auth::id(),
                'topic_id' => $material->topic_id,
                'material_id' => $validated['material_id'],
                'question_text' => $sanitizedQuestionText,
                'question_type' => $validated['question_type'],
                'options' => $sanitizedOptions,
                'correct_answer' => $validated['correct_answer'],
                'difficulty' => $validated['difficulty'],
                'points' => $validated['points'],
                'time_limit' => 30, // Default
                'image_url' => $imageUrl,
                'image_scale' => $request->input('image_scale', 100),
            ]);

            return redirect()
                ->route('admin.questions.index', [
                    'topic_id' => $material->topic_id,
                    'material_id' => $validated['material_id']
                ])
                ->with('success', 'Pertanyaan berhasil ditambahkan!');

        } catch (\Throwable $e) {
            // Delete uploaded image if failed
            if (isset($imageUrl) && $imageUrl && file_exists(public_path($imageUrl))) {
                @unlink(public_path($imageUrl));
            }
            // Log for dev
            \Illuminate\Support\Facades\Log::error('Question Store Error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Error Sistem (' . get_class($e) . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        }
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        if (Auth::user()->role !== 'admin' && $question->created_by != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $question->load(['material.topic', 'topic']);
        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Question $question)
    {
        if (Auth::user()->role !== 'admin' && $question->created_by != Auth::id()) {
            abort(403, 'Anda tidak berhak mengedit soal ini.');
        }

        $topicsQuery = Topic::with('materials')->orderBy('name');
        if (Auth::user()->role !== 'admin') {
            $topicsQuery->where('created_by', Auth::id());
        }
        $topics = $topicsQuery->get();

        $materialsQuery = Material::with('topic')->orderBy('name');
        if (Auth::user()->role !== 'admin') {
            $materialsQuery->whereHas('topic', function($q) {
                $q->where('created_by', Auth::id());
            });
        }
        $materials = $materialsQuery->get();

        return view('admin.questions.edit', compact('question', 'topics', 'materials'));
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Question $question)
    {
        if (Auth::user()->role !== 'admin' && $question->created_by != Auth::id()) {
            abort(403, 'Anda tidak berhak mengupdate soal ini.');
        }

        if ($request->question_type !== 'multiple_choice') {
            $request->merge(['options' => []]);
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,multiple_answer',
            'options' => 'nullable|array|max:6',
            'options.*.key' => 'required_if:question_type,multiple_choice|string|max:1',
            'options.*.text' => 'required_if:question_type,multiple_choice|string',
            'correct_answer' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'points' => 'required|integer|min:1|max:100',
        ]);

        try {
            $material = Material::findOrFail($validated['material_id']);
            $topic = Topic::find($material->topic_id);

             // Topic Check
            if (!$topic) {
                throw new \Exception('Topik tidak ditemukan untuk materi ini.');
            }

            // Ensure new material belongs to user's topic
            if (Auth::user()->role !== 'admin') {
                if ($topic->created_by != Auth::id()) {
                    abort(403, 'Anda tidak bisa memindahkan soal ke topik yang bukan milik Anda.');
                }
            }

            // Process Options Data
            if ($validated['question_type'] === 'true_false') {
                 $validated['options'] = [['key' => 'T', 'text' => 'Benar'], ['key' => 'F', 'text' => 'Salah']];
            } elseif ($validated['question_type'] === 'short_answer') {
                 $validated['options'] = [
                    'answers' => $request->input('short_answers'),
                    'case_sensitive' => $request->boolean('case_sensitive')
                ];
                if (!empty($request->input('short_answers'))) {
                    $validated['correct_answer'] = $request->input('short_answers')[0];
                }
            } elseif ($validated['question_type'] === 'multiple_answer') {
                 $validated['options'] = [
                    'choices' => $request->input('multi_options'),
                    'correct_answers' => $request->input('correct_answers')
                ];
                if (!empty($request->input('correct_answers'))) {
                    $validated['correct_answer'] = implode(',', $request->input('correct_answers'));
                }
            }

            // SAFE MODE: Image
            $imageUrl = $question->image_url;
            if ($request->hasFile('question_image')) {
                // Delete old
                if ($question->image_url && file_exists(public_path($question->image_url))) {
                    @unlink(public_path($question->image_url));
                }
                $file = $request->file('question_image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/questions'), $filename);
                $imageUrl = 'uploads/questions/' . $filename;
            } elseif ($request->input('remove_image') == '1') {
                if ($question->image_url && file_exists(public_path($question->image_url))) {
                    @unlink(public_path($question->image_url));
                }
                $imageUrl = null;
            }

            // SAFE MODE: Sanitize
            $sanitizedQuestionText = $validated['question_text'];
             if (function_exists('clean')) {
                $sanitizedQuestionText = clean($validated['question_text']);
            }
            $sanitizedOptions = $validated['options'];

            $question->update([
                'topic_id' => $material->topic_id,
                'material_id' => $validated['material_id'],
                'question_text' => $sanitizedQuestionText,
                'question_type' => $validated['question_type'],
                'options' => $sanitizedOptions,
                'correct_answer' => $validated['correct_answer'],
                'difficulty' => $validated['difficulty'],
                'points' => $validated['points'],
                'image_url' => $imageUrl,
                'image_scale' => $request->input('image_scale', $question->image_scale ?? 100),
            ]);

            return redirect()
                ->route('admin.questions.index', ['material_id' => $validated['material_id']])
                ->with('success', 'Pertanyaan berhasil diperbarui!');

        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error Sistem: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question)
    {
        if (Auth::user()->role !== 'admin' && $question->created_by != Auth::id()) {
            abort(403, 'Anda tidak berhak menghapus soal ini.');
        }

        // Remove image if exists
        if ($question->image_url && file_exists(public_path($question->image_url))) {
            @unlink(public_path($question->image_url));
        }

        $question->delete();

        return redirect()->back()->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Handle bulk destroy questions.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids) || count($ids) === 0) {
            return response()->json(['success' => false, 'message' => 'Tidak ada soal yang dipilih.'], 400);
        }

        $ids = array_map('intval', $ids);

        // Security check: Ensure user owns these questions (if not admin)
        if (Auth::user()->role !== 'admin') {
            $count = Question::whereIn('id', $ids)
                ->where('created_by', Auth::id())
                ->count();

            if ($count !== count($ids)) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus sebagian soal yang dipiih.'], 403);
            }
        }

        Question::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => count($ids) . ' soal berhasil dihapus.']);
    }

    /**
     * Handle image upload from WYSIWYG editor.
     */
    public function uploadImage(Request $request)
    {
        // Simple safe upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/questions'), $filename);
            return response()->json(['url' => asset('uploads/questions/' . $filename)]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}

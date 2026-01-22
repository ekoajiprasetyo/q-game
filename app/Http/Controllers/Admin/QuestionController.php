<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Question;
use App\Models\Topic;
use App\Services\HtmlSanitizerService;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    protected HtmlSanitizerService $sanitizer;
    protected ImageOptimizerService $imageOptimizer;

    public function __construct(HtmlSanitizerService $sanitizer, ImageOptimizerService $imageOptimizer)
    {
        $this->sanitizer = $sanitizer;
        $this->imageOptimizer = $imageOptimizer;
    }
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
        // Filter out options if not multiple_choice to avoid validation issues
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

        $material = Material::findOrFail($validated['material_id']);

        // Check Access: Ensure teacher owns the topic of this material
        if (Auth::user()->role !== 'admin') {
            $topic = Topic::find($material->topic_id);
            if ($topic->created_by !== Auth::id()) {
                abort(403, 'Anda tidak bisa menambahkan soal ke topik yang bukan milik Anda.');
            }
        }

        // Additional validation for short answers
        if ($request->question_type === 'short_answer') {
            $request->validate([
                'short_answers' => 'required|array|min:1',
                'short_answers.*' => 'required|string',
                'case_sensitive' => 'nullable|boolean',
            ]);
        }

        // Additional validation for multiple answer
        if ($request->question_type === 'multiple_answer') {
            $request->validate([
                'multi_options' => 'required|array|min:2',
                'multi_options.*.key' => 'required|string|max:1',
                'multi_options.*.text' => 'required|string',
                'correct_answers' => 'required|array|min:1',
            ]);
        }

        // Handle types logic (same as before)
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

        // Handle image upload with WebP optimization
        $imageUrl = null;
        if ($request->hasFile('question_image')) {
            $imageUrl = $this->imageOptimizer->optimize(
                $request->file('question_image'),
                'uploads/questions',
                false // Use public folder directly
            );
        }

        // Sanitize HTML content to prevent XSS
        $sanitizedQuestionText = $this->sanitizer->sanitize($validated['question_text']);
        
        // Sanitize options text if applicable
        $sanitizedOptions = $validated['options'];
        if (is_array($sanitizedOptions)) {
            if (isset($sanitizedOptions['choices'])) {
                // multiple_answer format
                $sanitizedOptions['choices'] = $this->sanitizer->sanitizeOptions($sanitizedOptions['choices']);
            } elseif (isset($sanitizedOptions['answers'])) {
                // short_answer format - answers are plain text, minimal sanitization
                $sanitizedOptions['answers'] = array_map(fn($a) => strip_tags($a), $sanitizedOptions['answers']);
            } elseif (!empty($sanitizedOptions) && isset($sanitizedOptions[0]['text'])) {
                // multiple_choice format
                $sanitizedOptions = $this->sanitizer->sanitizeOptions($sanitizedOptions);
            }
        }

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
            'time_limit' => 30,
            'image_url' => $imageUrl,
            'image_scale' => $request->input('image_scale', 100),
        ]);

        return redirect()
            ->route('admin.questions.index', [
                'topic_id' => $material->topic_id,
                'material_id' => $validated['material_id']
            ])
            ->with('success', 'Pertanyaan berhasil ditambahkan!');
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        if (Auth::user()->role !== 'admin' && $question->created_by !== Auth::id()) {
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
        if (Auth::user()->role !== 'admin' && $question->created_by !== Auth::id()) {
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
        if (Auth::user()->role !== 'admin' && $question->created_by !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengupdate soal ini.');
        }

        // Logic update sama seperti store + cek access ke material baru jika diubah
        // Filter options
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

        $material = Material::findOrFail($validated['material_id']);
        
        // Ensure new material belongs to user's topic
        if (Auth::user()->role !== 'admin') {
            $topic = Topic::find($material->topic_id);
            if ($topic->created_by !== Auth::id()) {
                abort(403, 'Anda tidak bisa memindahkan soal ke topik yang bukan milik Anda.');
            }
        }

        // Additional logic like short_answer/multiple_answer/image upload...
        // [Copying logic from previous state to ensure correctness]
        if ($request->question_type === 'short_answer') {
            $request->validate([
                'short_answers' => 'required|array|min:1',
                'short_answers.*' => 'required|string',
                'case_sensitive' => 'nullable|boolean',
            ]);
        }
        
         if ($request->question_type === 'multiple_answer') {
            $request->validate([
                'multi_options' => 'required|array|min:2',
                'correct_answers' => 'required|array|min:1',
            ]);
        }

        if ($validated['question_type'] === 'true_false') {
            $validated['options'] = [['key' => 'T', 'text' => 'Benar'], ['key' => 'F', 'text' => 'Salah']];
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

        // Image logic with WebP optimization
        $imageUrl = $question->image_url;
        if ($request->hasFile('question_image')) {
            // Delete old image
            if ($question->image_url && file_exists(public_path($question->image_url))) {
                unlink(public_path($question->image_url));
            }
            // Upload and optimize new image
            $imageUrl = $this->imageOptimizer->optimize(
                $request->file('question_image'),
                'uploads/questions',
                false
            );
        } elseif ($request->input('remove_image') == '1') {
            if ($question->image_url && file_exists(public_path($question->image_url))) {
                unlink(public_path($question->image_url));
            }
            $imageUrl = null;
        }

        $imageScale = $request->input('image_scale', $question->image_scale ?? 100);

        // Sanitize HTML content to prevent XSS
        $sanitizedQuestionText = $this->sanitizer->sanitize($validated['question_text']);
        
        // Sanitize options text if applicable
        $sanitizedOptions = $validated['options'];
        if (is_array($sanitizedOptions)) {
            if (isset($sanitizedOptions['choices'])) {
                $sanitizedOptions['choices'] = $this->sanitizer->sanitizeOptions($sanitizedOptions['choices']);
            } elseif (isset($sanitizedOptions['answers'])) {
                $sanitizedOptions['answers'] = array_map(fn($a) => strip_tags($a), $sanitizedOptions['answers']);
            } elseif (!empty($sanitizedOptions) && isset($sanitizedOptions[0]['text'])) {
                $sanitizedOptions = $this->sanitizer->sanitizeOptions($sanitizedOptions);
            }
        }

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
            'image_scale' => $imageScale,
        ]);

        return redirect()
            ->route('admin.questions.index', ['material_id' => $validated['material_id']])
            ->with('success', 'Pertanyaan berhasil diperbarui!');
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question)
    {
        if (Auth::user()->role !== 'admin' && $question->created_by !== Auth::id()) {
            abort(403, 'Anda tidak berhak menghapus soal ini.');
        }

        $materialId = $question->material_id;
        
        // Remove image if exists
        if ($question->image_url && file_exists(public_path($question->image_url))) {
            unlink(public_path($question->image_url));
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

        // Validate IDs
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:questions,id'
        ]);

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
     * Handle image upload from WYSIWYG editor with WebP optimization.
     */
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            // Optimize and convert to WebP
            $path = $this->imageOptimizer->optimize(
                $request->file('file'),
                'question-images',
                true // Use Laravel Storage
            );
            
            $url = asset($path);

            return response()->json(['url' => $url]);
        }
        
        return response()->json(['error' => 'No file uploaded'], 400);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SurpriseCard;
use App\Models\SurpriseSession;
use App\Models\Topic;
use App\Services\SurpriseGameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SurpriseGameController extends Controller
{
    public function setup(): View
    {
        $topics = Topic::query()
            ->with(['materials' => fn ($query) => $query->withCount('questions')->orderBy('name')])
            ->withCount('questions')
            ->when(! Auth::user()->isAdmin(), fn ($query) => $query->where('created_by', Auth::id()))
            ->orderBy('name')
            ->get();

        return view('surprise.setup', compact('topics'));
    }

    public function store(Request $request, SurpriseGameService $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'board_size' => ['required', 'integer', 'min:8', 'max:36'],
            'mode' => ['required', Rule::in(['quiz', 'classic', 'friendly'])],
            'power_up_codes' => ['nullable', 'array'],
            'power_up_codes.*' => ['nullable', 'string', 'max:30'],
            'question_time_limit' => ['nullable', 'integer', 'min:5', 'max:600'],
            'teams' => ['required', 'array', 'min:2', 'max:8'],
            'teams.*.name' => ['required', 'string', 'max:40', 'distinct'],
            'teams.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $teamCount = count($validated['teams']);
        $allowedBoardSizes = SurpriseGameService::boardOptionsForTeamCount($teamCount);
        if (! in_array((int) $validated['board_size'], $allowedBoardSizes, true)) {
            return back()->withInput()->withErrors(['board_size' => 'Ukuran papan tidak sesuai dengan jumlah tim.']);
        }

        $requiredPowerUps = (int) $validated['board_size'] - SurpriseGameService::questionCardCount((int) $validated['board_size'], $validated['mode']);
        $selectedPowerUps = array_values(array_unique(array_filter($validated['power_up_codes'] ?? [])));
        $allowedPowerUps = $validated['mode'] === 'quiz'
            ? []
            : array_keys(SurpriseGameService::powerUpOptions($validated['mode']));

        if (count($selectedPowerUps) < $requiredPowerUps || array_diff($selectedPowerUps, $allowedPowerUps)) {
            return back()
                ->withInput()
                ->withErrors(['power_up_codes' => $requiredPowerUps === 0
                    ? 'Mode Quiz tidak menggunakan kartu kejutan.'
                    : "Pilih minimal {$requiredPowerUps} kartu kejutan berbeda untuk mode ini."]);
        }
        $validated['power_up_codes'] = $selectedPowerUps;

        $topic = Topic::findOrFail($validated['topic_id']);
        $this->ensureOwnsTopic($topic);

        if (! empty($validated['material_id'])) {
            $material = Material::findOrFail($validated['material_id']);
            abort_unless($material->topic_id === $topic->id, 422, 'Materi tidak sesuai dengan topik yang dipilih.');
        }

        $session = $service->createSession($validated, Auth::user());

        return redirect()->route('surprise.play', $session)->with('success', 'Kotak Kejutan siap dimainkan.');
    }

    public function play(SurpriseSession $session): View
    {
        $session->load(['topic', 'material', 'teams', 'cards.question', 'currentTeam', 'currentCard.question']);
        $canModerate = Auth::check() && (Auth::user()->isAdmin() || $session->created_by === Auth::id());

        return view('surprise.play', compact('session', 'canModerate'));
    }

    public function reveal(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureCanModerate($session);
        $service->reveal($session, $card, Auth::user());

        return $this->successResponse($request);
    }

    public function resolve(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureCanModerate($session);
        $validated = $request->validate(['is_correct' => ['required', 'boolean']]);
        $service->resolveQuestion($session, $card, (bool) $validated['is_correct'], Auth::user());

        return $this->successResponse($request);
    }

    public function dismiss(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureCanModerate($session);
        $service->dismissCard($session, $card, Auth::user());

        return $this->successResponse($request);
    }

    public function timeout(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureCanModerate($session);
        $service->resolveTimedOutQuestion($session, $card, Auth::user());

        return $this->successResponse($request);
    }

    public function pauseTimer(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureCanModerate($session);
        $service->pauseQuestionTimer($session, $card, Auth::user());

        $card->refresh();

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'timer_paused' => true, 'remaining_seconds' => $card->timer_remaining_seconds])
            : back();
    }

    public function resumeTimer(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureCanModerate($session);
        $service->resumeQuestionTimer($session, $card, Auth::user());

        $card->refresh();

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'timer_paused' => false, 'remaining_seconds' => max(0, $card->revealed_at->copy()->addSeconds((int) $session->question_time_limit)->getTimestamp() - now()->getTimestamp())])
            : back();
    }

    public function status(SurpriseSession $session, Request $request, SurpriseGameService $service): RedirectResponse
    {
        $this->ensureCanModerate($session);
        $validated = $request->validate(['status' => ['required', Rule::in(['paused', 'active', 'finished'])]]);
        $service->changeStatus($session, $validated['status'], Auth::user());

        return back();
    }

    public function resolvePowerUp(SurpriseSession $session, SurpriseCard $card, Request $request, SurpriseGameService $service): RedirectResponse
    {
        $this->ensureCanModerate($session);
        $validated = $request->validate(['target_team_id' => ['nullable', 'integer']]);
        $service->resolvePowerUp($session, $card, $validated['target_team_id'] ?? null, Auth::user());

        return back();
    }

    private function ensureOwnsTopic(Topic $topic): void
    {
        abort_unless(Auth::user()->isAdmin() || $topic->created_by === Auth::id(), 403);
    }

    private function ensureCanModerate(SurpriseSession $session): void
    {
        abort_unless(Auth::user()->isAdmin() || $session->created_by === Auth::id(), 403);
    }

    private function successResponse(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }
}

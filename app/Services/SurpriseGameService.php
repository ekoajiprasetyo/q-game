<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SurpriseCard;
use App\Models\SurpriseEvent;
use App\Models\SurpriseSession;
use App\Models\SurpriseTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurpriseGameService
{
    public static function powerUpOptions(string $mode): array
    {
        $all = [
            'gift_points' => ['icon' => '🎁', 'label' => 'Hadiah Poin', 'description' => 'Dapatkan bonus acak 5–25 poin.'],
            'gold_bonus' => ['icon' => '🏅', 'label' => 'Harta Karun', 'description' => 'Dapatkan 50 poin.'],
            'double_score' => ['icon' => '✖️', 'label' => 'Pengganda Skor', 'description' => 'Skor tim saat ini menjadi dua kali lipat.'],
            'target_bonus' => ['icon' => '💝', 'label' => 'Hadiah untuk Lawan', 'description' => 'Berikan bonus acak 5–25 poin kepada satu tim lawan.', 'requires_target' => true],
            'give_points' => ['icon' => '🤝', 'label' => 'Berbagi Poin', 'description' => 'Pindahkan 5–25 poin dari tim Anda kepada satu tim lawan.', 'requires_target' => true],
            'take_points' => ['icon' => '🧲', 'label' => 'Tarik Poin', 'description' => 'Ambil 5–25 poin dari satu tim lawan.', 'requires_target' => true],
            'take_all_points' => ['icon' => '💰', 'label' => 'Sapu Poin', 'description' => 'Ambil seluruh poin dari satu tim lawan.', 'requires_target' => true],
            'target_penalty' => ['icon' => '🦈', 'label' => 'Pengurang Skor', 'description' => 'Satu tim lawan kehilangan 5–25 poin.', 'requires_target' => true],
            'swap_scores' => ['icon' => '🔄', 'label' => 'Tukar Skor', 'description' => 'Tukar skor dengan satu tim lawan.', 'requires_target' => true],
            'reset_self' => ['icon' => '🧽', 'label' => 'Skor Nol', 'description' => 'Skor tim Anda kembali menjadi 0.'],
            'reset_all' => ['icon' => '🦠', 'label' => 'Reset Bersama', 'description' => 'Semua skor tim kembali menjadi 0.'],
            'rise_to_top' => ['icon' => '🚀', 'label' => 'Loncat Teratas', 'description' => 'Skor Anda naik satu poin di atas skor tertinggi.'],
            'drop_to_bottom' => ['icon' => '🍌', 'label' => 'Jatuh Terbawah', 'description' => 'Skor Anda turun satu poin di bawah skor terendah.'],
            'blank' => ['icon' => '🪤', 'label' => 'Kejutan Kosong', 'description' => 'Tidak mendapat poin dan giliran berakhir.'],
            'self_penalty' => ['icon' => '💥', 'label' => 'Rugi Acak', 'description' => 'Kehilangan poin acak 5–25.'],
            'big_penalty' => ['icon' => '💣', 'label' => 'Penalti Besar', 'description' => 'Kehilangan 50 poin.'],
        ];

        $codes = $mode === 'friendly'
            ? ['gift_points', 'gold_bonus', 'double_score', 'target_bonus', 'give_points', 'reset_self', 'rise_to_top', 'blank', 'self_penalty', 'big_penalty']
            : array_keys($all);

        return collect($codes)->mapWithKeys(fn (string $code) => [$code => $all[$code]])->all();
    }

    public static function requiresTarget(string $code): bool
    {
        return (bool) (self::allPowerUpOptions()[$code]['requires_target'] ?? false);
    }

    public static function allPowerUpOptions(): array
    {
        return array_merge(self::powerUpOptions('classic'), [
            'bonus_small' => ['icon' => '⚡', 'label' => 'Bonus Cepat', 'description' => 'Dapatkan 5 poin tambahan.'],
            'bonus_points' => ['icon' => '✨', 'label' => 'Bonus Poin', 'description' => 'Dapatkan 10 poin tambahan.'],
            'lucky_points' => ['icon' => '🍀', 'label' => 'Poin Keberuntungan', 'description' => 'Dapatkan bonus acak 5–25 poin.'],
            'bonus_big' => ['icon' => '🌟', 'label' => 'Bonus Besar', 'description' => 'Dapatkan 20 poin tambahan.'],
            'double_points' => ['icon' => '✖️', 'label' => 'Poin Ganda Lama', 'description' => 'Dapatkan bonus 10 poin.'],
            'shield' => ['icon' => '🛡️', 'label' => 'Perisai Tim', 'description' => 'Blokir satu efek negatif yang mengenai tim Anda.'],
            'shield_plus' => ['icon' => '🛡️🛡️', 'label' => 'Perisai Ganda', 'description' => 'Dapatkan dua perisai.'],
            'extra_turn' => ['icon' => '🔁', 'label' => 'Giliran Tambahan', 'description' => 'Tim Anda langsung mendapat giliran lagi.'],
            'steal_points' => ['icon' => '💰', 'label' => 'Ambil Poin', 'description' => 'Ambil hingga 10 poin dari satu tim lawan.', 'requires_target' => true],
            'skip_turn' => ['icon' => '⏭️', 'label' => 'Lewati Giliran', 'description' => 'Satu tim lawan melewati satu giliran.', 'requires_target' => true],
        ]);
    }

    public static function boardOptionsForTeamCount(int $teamCount): array
    {
        return match ($teamCount) {
            2, 4 => [8, 16, 24, 36],
            3 => [9, 15, 24, 36],
            5 => [10, 15, 20, 25, 30, 35],
            6 => [12, 18, 24, 30, 36],
            7 => [14, 21, 28, 35],
            8 => [8, 16, 24, 32],
            default => [],
        };
    }

    public static function questionCardCount(int $boardSize, string $mode): int
    {
        return $mode === 'quiz' ? $boardSize : (int) ceil($boardSize * 0.75);
    }

    public function createSession(array $data, User $user): SurpriseSession
    {
        return DB::transaction(function () use ($data, $user) {
            $mode = $data['mode'];
            $questionCardCount = self::questionCardCount((int) $data['board_size'], $mode);
            $powerUpCandidates = array_values(array_unique($data['power_up_codes'] ?? []));
            $expectedPowerUps = (int) $data['board_size'] - $questionCardCount;
            if (count($powerUpCandidates) < $expectedPowerUps) {
                throw ValidationException::withMessages(['power_up_codes' => 'Pilih cukup kartu kejutan yang berbeda untuk ukuran papan ini.']);
            }
            $powerUpCodes = collect($powerUpCandidates)->shuffle()->take($expectedPowerUps)->values()->all();
            $questions = Question::query()
                ->where('topic_id', $data['topic_id'])
                ->when($data['material_id'] ?? null, fn ($query, $materialId) => $query->where('material_id', $materialId))
                ->inRandomOrder()
                ->take($questionCardCount)
                ->get();

            if ($questions->count() < $questionCardCount) {
                throw ValidationException::withMessages([
                    'board_size' => "Soal yang tersedia tidak cukup. Mode ini membutuhkan {$questionCardCount} kartu soal.",
                ]);
            }

            $session = SurpriseSession::create([
                'title' => $data['title'] ?: null,
                'session_pin' => $this->generatePin(),
                'status' => 'active',
                'mode' => $mode,
                'topic_id' => $data['topic_id'],
                'material_id' => $data['material_id'] ?? null,
                'board_size' => $data['board_size'],
                'question_time_limit' => $data['question_time_limit'] ?? null,
                'config' => [
                    'question_order' => 'random',
                    'power_ups' => $mode !== 'quiz',
                    'question_card_count' => $questionCardCount,
                    'selected_power_up_codes' => $powerUpCandidates,
                    'power_up_codes' => $powerUpCodes,
                ],
                'started_at' => now(),
                'created_by' => $user->id,
            ]);

            foreach ($data['teams'] as $index => $team) {
                SurpriseTeam::create([
                    'surprise_session_id' => $session->id,
                    'name' => trim($team['name']),
                    'color' => $team['color'],
                    'turn_order' => $index + 1,
                ]);
            }

            $cards = $questions->map(fn (Question $question) => [
                'card_type' => 'question', 'question_id' => $question->id,
            ]);
            foreach ($powerUpCodes as $powerUpCode) {
                $cards->push(['card_type' => $powerUpCode === 'blank' ? 'blank' : 'power_up', 'power_up_code' => $powerUpCode]);
            }

            foreach ($cards->shuffle()->values() as $index => $card) {
                SurpriseCard::create([
                    'surprise_session_id' => $session->id,
                    'position' => $index + 1,
                    ...$card,
                ]);
            }

            $firstTeam = SurpriseTeam::where('surprise_session_id', $session->id)->orderBy('turn_order')->firstOrFail();
            $session->update(['current_team_id' => $firstTeam->id]);

            $this->event($session, 'game_started', $firstTeam, null, ['mode' => $mode], $user);

            return $session->fresh();
        });
    }

    public function reveal(SurpriseSession $session, SurpriseCard $card, User $user): void
    {
        DB::transaction(function () use ($session, $card, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            $card = SurpriseCard::lockForUpdate()->findOrFail($card->id);

            if ($session->status !== 'active') {
                throw ValidationException::withMessages(['session' => 'Permainan tidak sedang aktif.']);
            }
            if ($session->current_card_id || $card->surprise_session_id !== $session->id || $card->state !== 'hidden') {
                throw ValidationException::withMessages(['card' => 'Kartu ini tidak dapat dibuka.']);
            }

            $card->update([
                'state' => 'revealed',
                'selected_by_team_id' => $session->current_team_id,
                'revealed_at' => now(),
            ]);
            $session->update(['current_card_id' => $card->id]);
            $this->event($session, 'card_revealed', $session->currentTeam, $card, ['position' => $card->position], $user);
        });
    }

    public function resolveQuestion(SurpriseSession $session, SurpriseCard $card, bool $isCorrect, User $user): void
    {
        DB::transaction(function () use ($session, $card, $isCorrect, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            $card = SurpriseCard::with('question')->lockForUpdate()->findOrFail($card->id);

            if ($session->status !== 'active' || $session->current_card_id !== $card->id || $card->state !== 'revealed' || $card->card_type !== 'question') {
                throw ValidationException::withMessages(['card' => 'Soal ini sudah atau belum dapat disahkan.']);
            }

            $team = SurpriseTeam::lockForUpdate()->findOrFail($session->current_team_id);
            $points = $isCorrect ? (int) $card->question->points : 0;
            if ($points > 0) {
                $team->increment('score', $points);
            }

            $card->update([
                'state' => 'resolved',
                'resolved_at' => now(),
                'result_payload' => ['is_correct' => $isCorrect, 'points_awarded' => $points],
            ]);

            $this->event($session, 'answer_marked', $team, $card, ['is_correct' => $isCorrect, 'points_awarded' => $points], $user);
            $this->advanceOrFinish($session, $team, $user);
        });
    }

    public function resolveTimedOutQuestion(SurpriseSession $session, SurpriseCard $card, User $user): void
    {
        $timeLimit = (int) ($session->question_time_limit ?? 0);
        if ($timeLimit <= 0 || $card->timer_paused_at || ! $card->revealed_at || $card->revealed_at->addSeconds($timeLimit)->isFuture()) {
            throw ValidationException::withMessages(['card' => 'Waktu soal belum habis.']);
        }

        $this->resolveQuestion($session, $card, false, $user);
    }

    public function pauseQuestionTimer(SurpriseSession $session, SurpriseCard $card, User $user): void
    {
        DB::transaction(function () use ($session, $card, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            $card = SurpriseCard::lockForUpdate()->findOrFail($card->id);
            $timeLimit = (int) ($session->question_time_limit ?? 0);

            if ($session->status !== 'active' || $session->current_card_id !== $card->id || $card->state !== 'revealed' || $card->card_type !== 'question' || $timeLimit <= 0 || $card->timer_paused_at) {
                throw ValidationException::withMessages(['card' => 'Timer ini tidak dapat dijeda.']);
            }

            $remaining = max(0, $card->revealed_at->copy()->addSeconds($timeLimit)->getTimestamp() - now()->getTimestamp());
            if ($remaining <= 0) {
                throw ValidationException::withMessages(['card' => 'Waktu soal sudah habis.']);
            }

            $card->update(['timer_paused_at' => now(), 'timer_remaining_seconds' => $remaining]);
            $this->event($session, 'question_timer_paused', $session->currentTeam, $card, ['seconds_remaining' => $remaining], $user);
        });
    }

    public function resumeQuestionTimer(SurpriseSession $session, SurpriseCard $card, User $user): void
    {
        DB::transaction(function () use ($session, $card, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            $card = SurpriseCard::lockForUpdate()->findOrFail($card->id);
            $timeLimit = (int) ($session->question_time_limit ?? 0);
            $remaining = (int) ($card->timer_remaining_seconds ?? 0);

            if ($session->status !== 'active' || $session->current_card_id !== $card->id || $card->state !== 'revealed' || $card->card_type !== 'question' || $timeLimit <= 0 || ! $card->timer_paused_at || $remaining <= 0) {
                throw ValidationException::withMessages(['card' => 'Timer ini tidak dapat dilanjutkan.']);
            }

            $card->update([
                'revealed_at' => now()->subSeconds($timeLimit - $remaining),
                'timer_paused_at' => null,
                'timer_remaining_seconds' => null,
            ]);
            $this->event($session, 'question_timer_resumed', $session->currentTeam, $card, ['seconds_remaining' => $remaining], $user);
        });
    }

    public function dismissCard(SurpriseSession $session, SurpriseCard $card, User $user): void
    {
        DB::transaction(function () use ($session, $card, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            $card = SurpriseCard::lockForUpdate()->findOrFail($card->id);

            if ($session->status !== 'active' || $session->current_card_id !== $card->id || $card->state !== 'revealed') {
                throw ValidationException::withMessages(['card' => 'Kartu ini tidak dapat ditutup.']);
            }

            $card->update([
                'state' => 'hidden',
                'selected_by_team_id' => null,
                'revealed_at' => null,
            ]);
            $session->update(['current_card_id' => null]);
            $this->event($session, 'card_dismissed', $session->currentTeam, $card, ['position' => $card->position], $user);
        });
    }

    public function resolvePowerUp(SurpriseSession $session, SurpriseCard $card, ?int $targetTeamId, User $user): void
    {
        DB::transaction(function () use ($session, $card, $targetTeamId, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            $card = SurpriseCard::lockForUpdate()->findOrFail($card->id);

            if ($session->status !== 'active' || $session->current_card_id !== $card->id || $card->state !== 'revealed' || ! in_array($card->card_type, ['power_up', 'blank'], true)) {
                throw ValidationException::withMessages(['card' => 'Efek ini sudah atau belum dapat diterapkan.']);
            }

            $team = SurpriseTeam::lockForUpdate()->findOrFail($session->current_team_id);
            $target = $targetTeamId ? SurpriseTeam::lockForUpdate()->find($targetTeamId) : null;
            if ($target && ($target->surprise_session_id !== $session->id || $target->id === $team->id)) {
                throw ValidationException::withMessages(['target_team_id' => 'Pilih tim lawan dalam permainan ini.']);
            }

            $code = $card->power_up_code ?? 'blank';
            $payload = $this->applyPowerUp($session, $team, $target, $code);
            $card->update(['state' => 'resolved', 'resolved_at' => now(), 'result_payload' => $payload]);
            $this->event($session, 'power_up_applied', $team, $card, $payload, $user);
            $this->advanceOrFinish($session, $team, $user, $code === 'extra_turn');
        });
    }

    public function changeStatus(SurpriseSession $session, string $status, User $user): void
    {
        DB::transaction(function () use ($session, $status, $user) {
            $session = SurpriseSession::lockForUpdate()->findOrFail($session->id);
            if ($session->current_card_id && $status !== 'finished') {
                throw ValidationException::withMessages(['session' => 'Sahkan atau batalkan kartu yang sedang terbuka terlebih dahulu.']);
            }
            if ($status === 'paused' && $session->status !== 'active') {
                throw ValidationException::withMessages(['session' => 'Hanya permainan aktif yang dapat dijeda.']);
            }
            if ($status === 'active' && $session->status !== 'paused') {
                throw ValidationException::withMessages(['session' => 'Hanya permainan jeda yang dapat dilanjutkan.']);
            }

            $session->update(['status' => $status, 'ended_at' => $status === 'finished' ? now() : null]);
            $this->event($session, 'game_'.$status, null, null, [], $user);
        });
    }

    private function advanceOrFinish(SurpriseSession $session, SurpriseTeam $team, User $user, bool $extraTurn = false): void
    {
        $allResolved = ! SurpriseCard::where('surprise_session_id', $session->id)->where('state', 'hidden')->exists();
        if ($allResolved) {
            $session->update(['status' => 'finished', 'current_card_id' => null, 'ended_at' => now()]);
            $this->event($session, 'game_finished', $team, null, [], $user);
            return;
        }

        if ($extraTurn) {
            $session->update(['current_card_id' => null]);
            $this->event($session, 'extra_turn_granted', $team, null, [], $user);
            return;
        }

        $teams = SurpriseTeam::where('surprise_session_id', $session->id)->orderBy('turn_order')->lockForUpdate()->get();
        $currentIndex = $teams->search(fn (SurpriseTeam $candidate) => $candidate->id === $team->id);
        foreach (range(1, $teams->count()) as $offset) {
            $nextTeam = $teams[($currentIndex + $offset) % $teams->count()];
            if ($nextTeam->skip_turns > 0) {
                $nextTeam->decrement('skip_turns');
                $this->event($session, 'turn_skipped', $nextTeam, null, [], $user);
                continue;
            }
            break;
        }

        $session->update(['current_card_id' => null, 'current_team_id' => $nextTeam->id]);
        $this->event($session, 'turn_changed', $nextTeam, null, ['turn_order' => $nextTeam->turn_order], $user);
    }

    private function applyPowerUp(SurpriseSession $session, SurpriseTeam $team, ?SurpriseTeam $target, string $code): array
    {
        $legacyPoints = (int) ($session->config['power_up_points'] ?? 10);
        $payload = ['code' => $code, 'points_awarded' => 0];

        if (self::requiresTarget($code) && ! $target) {
            throw ValidationException::withMessages(['target_team_id' => 'Efek ini memerlukan satu tim lawan sebagai target.']);
        }

        switch ($code) {
            case 'gift_points':
                $points = random_int(5, 25);
                $team->increment('score', $points);
                $payload['points_awarded'] = $points;
                break;
            case 'gold_bonus':
                $team->increment('score', 50);
                $payload['points_awarded'] = 50;
                break;
            case 'double_score':
                $before = $team->score;
                $team->update(['score' => $before * 2]);
                $payload['points_awarded'] = $before;
                $payload['score_before'] = $before;
                $payload['score_after'] = $before * 2;
                break;
            case 'target_bonus':
                $points = random_int(5, 25);
                $target->increment('score', $points);
                $payload['points_given'] = $points;
                $payload['target_team_id'] = $target->id;
                break;
            case 'give_points':
                $points = min(random_int(5, 25), max(0, $team->score));
                if ($points > 0) {
                    $team->decrement('score', $points);
                    $target->increment('score', $points);
                }
                $payload['points_given'] = $points;
                $payload['target_team_id'] = $target->id;
                break;
            case 'take_points':
                $points = min(random_int(5, 25), max(0, $target->score));
                if ($points > 0) {
                    $target->decrement('score', $points);
                    $team->increment('score', $points);
                }
                $payload['points_awarded'] = $points;
                $payload['target_team_id'] = $target->id;
                break;
            case 'take_all_points':
                $points = max(0, $target->score);
                if ($points > 0) {
                    $target->decrement('score', $points);
                    $team->increment('score', $points);
                }
                $payload['points_awarded'] = $points;
                $payload['target_team_id'] = $target->id;
                break;
            case 'target_penalty':
                $points = random_int(5, 25);
                $target->decrement('score', $points);
                $payload['points_lost'] = $points;
                $payload['target_team_id'] = $target->id;
                break;
            case 'swap_scores':
                $teamScore = $team->score;
                $team->update(['score' => $target->score]);
                $target->update(['score' => $teamScore]);
                $payload['target_team_id'] = $target->id;
                break;
            case 'reset_self':
                $payload['points_lost'] = $team->score;
                $team->update(['score' => 0]);
                break;
            case 'reset_all':
                $affectedTeams = SurpriseTeam::where('surprise_session_id', $session->id)->lockForUpdate()->get();
                $payload['teams_reset'] = $affectedTeams->count();
                foreach ($affectedTeams as $affectedTeam) {
                    $affectedTeam->update(['score' => 0]);
                }
                break;
            case 'rise_to_top':
                $highestScore = (int) SurpriseTeam::where('surprise_session_id', $session->id)->max('score');
                $newScore = $highestScore + 1;
                $payload['points_awarded'] = $newScore - $team->score;
                $team->update(['score' => $newScore]);
                break;
            case 'drop_to_bottom':
                $lowestScore = (int) SurpriseTeam::where('surprise_session_id', $session->id)->min('score');
                $newScore = $lowestScore - 1;
                $payload['points_lost'] = $team->score - $newScore;
                $team->update(['score' => $newScore]);
                break;
            case 'blank':
                break;
            case 'self_penalty':
                $points = random_int(5, 25);
                $team->decrement('score', $points);
                $payload['points_lost'] = $points;
                break;
            case 'big_penalty':
                $team->decrement('score', 50);
                $payload['points_lost'] = 50;
                break;
            // Backward compatibility for sessions created before the card-set refresh.
            case 'bonus_small':
                $team->increment('score', 5);
                $payload['points_awarded'] = 5;
                break;
            case 'bonus_points':
            case 'double_points':
                $team->increment('score', $legacyPoints);
                $payload['points_awarded'] = $legacyPoints;
                break;
            case 'lucky_points':
                $points = random_int(5, 25);
                $team->increment('score', $points);
                $payload['points_awarded'] = $points;
                break;
            case 'bonus_big':
                $team->increment('score', 20);
                $payload['points_awarded'] = 20;
                break;
            case 'shield':
                $team->increment('shield_count');
                break;
            case 'shield_plus':
                $team->increment('shield_count', 2);
                break;
            case 'steal_points':
                $points = min($legacyPoints, max(0, $target->score));
                if ($points > 0) {
                    $target->decrement('score', $points);
                    $team->increment('score', $points);
                }
                $payload['points_awarded'] = $points;
                $payload['target_team_id'] = $target->id;
                break;
            case 'skip_turn':
                $target->increment('skip_turns');
                $payload['target_team_id'] = $target->id;
                break;
            case 'extra_turn':
                break;
            default:
                throw ValidationException::withMessages(['card' => 'Kode efek tidak dikenal.']);
        }

        return $payload;
    }

    private function generatePin(): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $pin = (string) random_int(100000, 999999);
            if (! SurpriseSession::where('session_pin', $pin)->exists()) {
                return $pin;
            }
        }

        throw ValidationException::withMessages(['session' => 'PIN permainan tidak dapat dibuat. Silakan coba lagi.']);
    }

    private function event(SurpriseSession $session, string $type, ?SurpriseTeam $team, ?SurpriseCard $card, array $payload, User $user): void
    {
        SurpriseEvent::create([
            'surprise_session_id' => $session->id,
            'surprise_card_id' => $card?->id,
            'actor_team_id' => $team?->id,
            'event_type' => $type,
            'payload' => $payload,
            'created_by' => $user->id,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /**
     * The table associated with the model.
     * RENAMED to avoid conflict with Q-Exam's questions table
     */
    protected $table = 'game_questions';

    protected $fillable = [
        'created_by',
        'topic_id',
        'material_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'difficulty',
        'points',
        'image_url',
        'image_scale',
        'audio_url',
        'time_limit',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'time_limit' => 'integer',
        'image_scale' => 'integer',
    ];

    /**
     * Get the user who created this question.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the material that owns this question.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the topic through material or directly.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get all game rounds that used this question.
     */
    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    /**
     * Check if given answer is correct.
     */
    public function isCorrect($answer): bool
    {
        if ($this->question_type === 'short_answer') {
            $answer = trim($answer);
            $options = $this->options ?? [];
            $acceptedAnswers = $options['answers'] ?? [$this->correct_answer];
            $isCaseSensitive = $options['case_sensitive'] ?? false;

            foreach ($acceptedAnswers as $validAnswer) {
                if ($isCaseSensitive) {
                    if ($answer === $validAnswer) return true;
                } else {
                    if (strtoupper($answer) === strtoupper($validAnswer)) return true;
                }
            }
            return false;
        }

        if ($this->question_type === 'multiple_answer') {
            $selectedAnswers = is_array($answer) ? $answer : explode(',', $answer);
            $selectedAnswers = array_map('trim', $selectedAnswers);
            $selectedAnswers = array_map('strtoupper', $selectedAnswers);
            sort($selectedAnswers);

            $correctAnswers = $this->options['correct_answers'] ?? [];
            $correctAnswers = array_map('strtoupper', $correctAnswers);
            sort($correctAnswers);

            return $selectedAnswers === $correctAnswers;
        }

        return strtoupper(trim($answer)) === strtoupper(trim($this->correct_answer));
    }

    /**
     * Get formatted options for display.
     */
    public function getFormattedOptionsAttribute(): array
    {
        if ($this->question_type === 'true_false') {
            return [
                ['key' => 'TRUE', 'text' => 'Benar'],
                ['key' => 'FALSE', 'text' => 'Salah'],
            ];
        }

        return $this->options ?? [];
    }
}

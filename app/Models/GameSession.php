<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    protected $fillable = [
        'session_pin',
        'status',
        'material_id',
        'title',
        'game_mode',
        'topic_id',
        'team_blue_name',
        'team_red_name',
        'team_blue_score',
        'team_red_score',
        'winner_team',
        'total_questions',
        'time_per_question',
        'pull_strength',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'team_blue_score' => 'integer',
        'team_red_score' => 'integer',
        'total_questions' => 'integer',
        'time_per_question' => 'integer',
        'pull_strength' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the topic for this game session.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the material for this game session.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the user who created this game session.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all rounds for this game session.
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class)->orderBy('round_number');
    }

    /**
     * Check if game is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->ended_at === null;
    }

    /**
     * Check if game has ended.
     */
    public function hasEnded(): bool
    {
        return $this->ended_at !== null;
    }

    /**
     * Get current rope position based on scores.
     * Returns value from -5 (blue winning) to +5 (red winning)
     */
    public function getRopePositionAttribute(): int
    {
        $diff = $this->team_red_score - $this->team_blue_score;
        return max(-5, min(5, $diff * $this->pull_strength));
    }

    /**
     * Get the current round number.
     */
    public function getCurrentRoundAttribute(): int
    {
        return $this->rounds()->count() + 1;
    }

    /**
     * Determine winner based on rope position.
     */
    public function determineWinner(): ?string
    {
        if ($this->team_blue_score > $this->team_red_score) {
            return 'blue';
        } elseif ($this->team_red_score > $this->team_blue_score) {
            return 'red';
        }
        return 'draw';
    }
}

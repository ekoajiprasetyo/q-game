<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRound extends Model
{
    protected $fillable = [
        'game_session_id',
        'question_id',
        'round_number',
        'team_blue_answer',
        'team_red_answer',
        'team_blue_time_ms',
        'team_red_time_ms',
        'team_blue_correct',
        'team_red_correct',
        'winner_team',
        'points_awarded',
        'rope_position_after',
    ];

    protected $casts = [
        'round_number' => 'integer',
        'team_blue_time_ms' => 'integer',
        'team_red_time_ms' => 'integer',
        'team_blue_correct' => 'boolean',
        'team_red_correct' => 'boolean',
        'points_awarded' => 'integer',
        'rope_position_after' => 'integer',
    ];

    /**
     * Get the game session this round belongs to.
     */
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * Get the question for this round.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Process answers and determine round winner.
     * In race mode: first correct answer wins
     * In turn_based mode: both teams answer, compare results
     */
    public function processAnswers(string $mode = 'race'): void
    {
        $question = $this->question;
        
        // Check if answers are correct
        if ($this->team_blue_answer) {
            $this->team_blue_correct = $question->isCorrect($this->team_blue_answer);
        }
        if ($this->team_red_answer) {
            $this->team_red_correct = $question->isCorrect($this->team_red_answer);
        }

        // Determine winner based on mode
        if ($mode === 'race') {
            $this->determineRaceWinner();
        } else {
            $this->determineTurnBasedWinner();
        }

        $this->save();
    }

    /**
     * Race mode: first correct answer wins.
     */
    protected function determineRaceWinner(): void
    {
        if ($this->team_blue_correct && $this->team_red_correct) {
            // Both correct - faster wins
            if ($this->team_blue_time_ms < $this->team_red_time_ms) {
                $this->winner_team = 'blue';
            } elseif ($this->team_red_time_ms < $this->team_blue_time_ms) {
                $this->winner_team = 'red';
            } else {
                $this->winner_team = 'draw';
            }
        } elseif ($this->team_blue_correct) {
            $this->winner_team = 'blue';
        } elseif ($this->team_red_correct) {
            $this->winner_team = 'red';
        } else {
            $this->winner_team = 'none';
        }

        // Award points
        if ($this->winner_team === 'blue' || $this->winner_team === 'red') {
            $this->points_awarded = $this->question->points;
        }
    }

    /**
     * Turn-based mode: compare both answers.
     */
    protected function determineTurnBasedWinner(): void
    {
        if ($this->team_blue_correct && !$this->team_red_correct) {
            $this->winner_team = 'blue';
            $this->points_awarded = $this->question->points;
        } elseif ($this->team_red_correct && !$this->team_blue_correct) {
            $this->winner_team = 'red';
            $this->points_awarded = $this->question->points;
        } elseif ($this->team_blue_correct && $this->team_red_correct) {
            $this->winner_team = 'draw';
            $this->points_awarded = 0;
        } else {
            $this->winner_team = 'none';
            $this->points_awarded = 0;
        }
    }
}

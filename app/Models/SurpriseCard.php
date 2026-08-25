<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurpriseCard extends Model
{
    protected $fillable = [
        'surprise_session_id', 'position', 'card_type', 'question_id', 'power_up_code',
        'state', 'selected_by_team_id', 'revealed_at', 'timer_paused_at', 'timer_remaining_seconds', 'resolved_at', 'result_payload',
    ];

    protected $casts = ['position' => 'integer', 'revealed_at' => 'datetime', 'timer_paused_at' => 'datetime', 'timer_remaining_seconds' => 'integer', 'resolved_at' => 'datetime', 'result_payload' => 'array'];

    public function session(): BelongsTo { return $this->belongsTo(SurpriseSession::class, 'surprise_session_id'); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function selectedByTeam(): BelongsTo { return $this->belongsTo(SurpriseTeam::class, 'selected_by_team_id'); }
}

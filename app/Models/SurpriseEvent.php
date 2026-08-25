<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurpriseEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['surprise_session_id', 'surprise_card_id', 'actor_team_id', 'event_type', 'payload', 'created_by'];

    protected $casts = ['payload' => 'array', 'created_at' => 'datetime'];

    public function session(): BelongsTo { return $this->belongsTo(SurpriseSession::class, 'surprise_session_id'); }
    public function card(): BelongsTo { return $this->belongsTo(SurpriseCard::class, 'surprise_card_id'); }
    public function actorTeam(): BelongsTo { return $this->belongsTo(SurpriseTeam::class, 'actor_team_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}

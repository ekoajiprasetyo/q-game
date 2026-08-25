<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurpriseSession extends Model
{
    protected $fillable = [
        'title', 'session_pin', 'status', 'mode', 'topic_id', 'material_id',
        'board_size', 'question_time_limit', 'current_team_id', 'current_card_id',
        'config', 'started_at', 'ended_at', 'created_by',
    ];

    protected $casts = [
        'board_size' => 'integer',
        'question_time_limit' => 'integer',
        'config' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }
    public function material(): BelongsTo { return $this->belongsTo(Material::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function currentTeam(): BelongsTo { return $this->belongsTo(SurpriseTeam::class, 'current_team_id'); }
    public function currentCard(): BelongsTo { return $this->belongsTo(SurpriseCard::class, 'current_card_id'); }
    public function teams(): HasMany { return $this->hasMany(SurpriseTeam::class)->orderBy('turn_order'); }
    public function cards(): HasMany { return $this->hasMany(SurpriseCard::class)->orderBy('position'); }
    public function events(): HasMany { return $this->hasMany(SurpriseEvent::class)->latest('created_at'); }
}

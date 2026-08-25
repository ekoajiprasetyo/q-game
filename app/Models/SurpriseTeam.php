<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurpriseTeam extends Model
{
    protected $fillable = ['surprise_session_id', 'name', 'color', 'turn_order', 'score', 'shield_count', 'skip_turns'];

    protected $casts = ['turn_order' => 'integer', 'score' => 'integer', 'shield_count' => 'integer', 'skip_turns' => 'integer'];

    public function session(): BelongsTo { return $this->belongsTo(SurpriseSession::class, 'surprise_session_id'); }
}

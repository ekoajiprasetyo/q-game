<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    /**
     * The table associated with the model.
     * RENAMED to avoid conflict with Q-Link
     */
    protected $table = 'game_materials';

    protected $fillable = [
        'topic_id',
        'name',
        'description',
        'icon',
        'color',
    ];

    /**
     * Get the topic that owns the material.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the questions for the material.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the game sessions for the material.
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function activeSession()
    {
        return $this->hasOne(GameSession::class)->where('status', 'waiting')->latestOfMany();
    }
}

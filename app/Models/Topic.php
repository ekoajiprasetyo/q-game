<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    /**
     * The table associated with the model.
     * RENAMED to avoid conflict with Q-Exam's topics table
     */
    protected $table = 'game_topics';

    protected $fillable = [
        'name',
        'description',
        'subject',
        'icon',
        'color',
        'created_by',
    ];

    /**
     * Get the user who created this topic.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all materials for this topic.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    /**
     * Get all questions for this topic (direct + through materials).
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the count of materials in this topic.
     */
    public function getMaterialsCountAttribute(): int
    {
        return $this->materials()->count();
    }

    /**
     * Get the count of questions in this topic.
     */
    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }
}

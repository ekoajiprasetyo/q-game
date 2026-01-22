<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
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
     * Note: Originally this project linked questions directly to topics too.
     * But now we prefer through materials.
     * If we want to support direct questions too, we might need a custom getter.
     * For now, let's keep the hasManyThrough, but also add direct hasMany since questions table still has topic_id
     */
    public function questions()
    {
        // Since questions table has topic_id, we can define direct relationship.
        return $this->hasMany(Question::class);
    }

    /*
    public function questionsThroughMaterials(): HasManyThrough
    {
        return $this->hasManyThrough(Question::class, Material::class);
    }
    */

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

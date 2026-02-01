<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TournamentTeam extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * RENAMED to avoid conflict
     */
    protected $table = 'game_tournament_teams';

    protected $fillable = [
        'tournament_id',
        'name',
        'color',
        'seed_number',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }
}

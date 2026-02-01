<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TournamentTeam;
use App\Models\TournamentMatch;

class Tournament extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * RENAMED to avoid conflict
     */
    protected $table = 'game_tournaments';

    protected $fillable = [
        'title',
        'description',
        'status',
        'pin',
        'game_config',
        'created_by',
    ];

    protected $casts = [
        'game_config' => 'array',
    ];

    public function teams()
    {
        return $this->hasMany(TournamentTeam::class, 'tournament_id');
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class, 'tournament_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

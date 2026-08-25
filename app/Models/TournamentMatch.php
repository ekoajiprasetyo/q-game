<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tournament;
use App\Models\TournamentTeam;
use App\Models\GameSession;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'round',
        'match_number',
        'team_1_id',
        'team_2_id',
        'winner_team_id',
        'game_session_id',
        'next_match_id',
        'topic_id',
        'material_id',
        'time_per_question',
        'total_questions',
        'is_ready',
    ];

    protected $casts = [
        'is_ready' => 'boolean',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }

    public function team1()
    {
        return $this->belongsTo(TournamentTeam::class, 'team_1_id');
    }

    public function team2()
    {
        return $this->belongsTo(TournamentTeam::class, 'team_2_id');
    }

    public function winner()
    {
        return $this->belongsTo(TournamentTeam::class, 'winner_team_id');
    }

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function nextMatch()
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TournamentTeam;
use App\Models\TournamentMatch;

class Tournament extends Model
{
    use HasFactory;

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
        return $this->hasMany(TournamentTeam::class);
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    // Columns that can be mass-assigned (e.g. when creating from form data)
    protected $fillable = [
        'name',
        'avatar_emoji',
        'points',
        'wins',
        'losses',
        'matches_played',
    ];

    // Matches where this participant won
    public function wonMatches()
    {
        return $this->hasMany(MatchGame::class, 'winner_id');
    }

    // Matches where this participant lost
    public function lostMatches()
    {
        return $this->hasMany(MatchGame::class, 'loser_id');
    }
}

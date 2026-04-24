<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $avatar_emoji
 * @property int $points
 * @property int $wins
 * @property int $losses
 * @property int $matches_played
 */
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
    public function wonMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'winner_id');
    }

    // Matches where this participant lost
    public function lostMatches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'loser_id');
    }
}

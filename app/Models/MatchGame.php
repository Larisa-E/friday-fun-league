<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $winner_id
 * @property int $loser_id
 * @property int $winner_score
 * @property int $loser_score
 * @property string|null $game_type
 * @property \Illuminate\Support\Carbon $played_at
 */
class MatchGame extends Model
{
    // Columns that can be mass-assigned
    protected $fillable = [
        'winner_id',
        'loser_id',
        'winner_score',
        'loser_score',
        'game_type',
        'played_at',
    ];

    // Cast played_at as a Carbon datetime object for easy formatting
    protected $casts = [
        'played_at' => 'datetime',
    ];

    // The participant who won this match
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'winner_id');
    }

    // The participant who lost this match
    public function loser(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'loser_id');
    }
}

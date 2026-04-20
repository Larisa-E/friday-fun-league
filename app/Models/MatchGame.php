<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    public function winner()
    {
        return $this->belongsTo(Participant::class, 'winner_id');
    }

    // The participant who lost this match
    public function loser()
    {
        return $this->belongsTo(Participant::class, 'loser_id');
    }
}

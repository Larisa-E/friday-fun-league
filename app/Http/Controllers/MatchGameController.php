<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\Participant;
use Illuminate\Http\Request;

use function Symfony\Component\Clock\now;

class MatchGameController extends Controller
{
    // register match form submission
    public function store(Request $request)
    {
        // inputs
        $request->validate([
            'winner_id' => 'required|exists:participants,id',
            'loser_id' => 'required|exists:participants,id|different:winner_id',
            'winner_score' => 'required|integer|min:0',
            'loser_score' => 'required|integer|min:0',
            'game_type' => 'nullable|string|max:50',
        ]);

        // match record
        MatchGame::create([
            'winner_id' => $request->winner_id,
            'loser_id' => $request->loser_id,
            'winner_score' => $request->winner_score,
            'loser_score' => $request->loser_score,
            'game_type' => $request->game_type,
            'played_at'=> now(),
        ]);

        // Update loser stats
        Participant::find($request->winner_id) ->increment('losses'); 
        Participant::find($request->loser_id) ->increment('matches_played');

        // Update winner stats
        Participant::find($request->winner_id) ->increment('points', 3); // 3 points per win
        Participant::find($request->winner_id) ->increment('wins'); 
        Participant::find($request->winner_id) ->increment('matches_played');

        return redirect()->route('dashboard')->with('success', 'Match recorded successfully!');
    }
}

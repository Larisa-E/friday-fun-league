<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\MatchGame;

class DashboardController extends Controller
{
    // dashboard view
    public function index()
    {
        $participants = Participant::orderByDesc('points')
        ->orderByRaw('IF(matches_played > 0, wins / matches_played, 0) DESC')
        ->get(); // ordered by points, then by win rate

        $matches = MatchGame::with(['winner', 'loser'])
        ->orderByDesc('played_at')
        ->limit(10)
        ->get(); // Get 10 latest matches with winner/loser names to avoid slow queries

        return view('dashboard', compact('participants', 'matches')); // see data to the dashboard view
    }

    // return JSON for auto refresh without full page reload
    public function data()
    {
        $participants = Participant::orderByDesc('points')
        ->orderByRaw('IF(matches_played > 0, wins / matches_played, 0) DESC')
        ->get(); 

        $matches = MatchGame::with(['winner', 'loser'])
        ->orderByDesc('played_at')
        ->limit(10)
        ->get(); 

        return response()->json(compact('participants', 'matches')); // Return data as JSON
    }
}

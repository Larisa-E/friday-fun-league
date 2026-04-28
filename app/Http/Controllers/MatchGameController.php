<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Services\LeagueStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MatchGameController extends Controller
{
    // Create a new match result and then recalculate the standings.
    public function store(Request $request, LeagueStatsService $leagueStats)
    {
        $validated = $request->validate([
            'winner_id' => 'required|exists:participants,id',
            'loser_id' => 'required|exists:participants,id|different:winner_id',
            'winner_score' => 'required|integer|min:0',
            'loser_score' => 'required|integer|min:0|lt:winner_score',
            'game_type' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $leagueStats): void {
            MatchGame::create([
                ...$validated,
                'played_at' => now(),
            ]);

            $leagueStats->recalculateParticipantStats();
        });

        return redirect()->route('dashboard')->with('success', 'Match recorded successfully!');
    }

    // Update one match and reopen the same edit modal if validation fails.
    public function update(Request $request, MatchGame $match, LeagueStatsService $leagueStats)
    {
        $validator = Validator::make($request->all(), [
            'winner_id' => 'required|exists:participants,id',
            'loser_id' => 'required|exists:participants,id|different:winner_id',
            'winner_score' => 'required|integer|min:0',
            'loser_score' => 'required|integer|min:0|lt:winner_score',
            'game_type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('dashboard')
                ->withErrors($validator, 'matchUpdate.' . $match->id)
                ->withInput()
                ->with('openModal', 'editMatchModal' . $match->id);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($match, $validated, $leagueStats): void {
            $match->update($validated);
            $leagueStats->recalculateParticipantStats();
        });

        return redirect()->route('dashboard')->with('success', 'Match updated successfully!');
    }

    // Delete one match result and recalculate the standings afterwards.
    public function destroy(MatchGame $match, LeagueStatsService $leagueStats)
    {
        DB::transaction(function () use ($match, $leagueStats): void {
            $match->delete();
            $leagueStats->recalculateParticipantStats();
        });

        return redirect()->route('dashboard')->with('success', 'Match deleted successfully!');
    }
}

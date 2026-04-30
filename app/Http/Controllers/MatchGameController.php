<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Services\LeagueStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $match = null;

        DB::transaction(function () use ($validated, $leagueStats, &$match): void {
            $match = MatchGame::create([
                ...$validated,
                'played_at' => now(),
            ]);

            $leagueStats->recalculateParticipantStats();
        });

        Log::channel('league')->info('Match created', [
            'match_id' => $match?->id,
            'winner_id' => $match?->winner_id,
            'loser_id' => $match?->loser_id,
            'winner_score' => $match?->winner_score,
            'loser_score' => $match?->loser_score,
            'game_type' => $match?->game_type,
        ]);

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
            Log::channel('league')->warning('Match update validation failed', [
                'match_id' => $match->id,
                'messages' => $validator->errors()->all(),
            ]);

            return redirect()
                ->route('dashboard')
                ->withErrors($validator, 'matchUpdate.' . $match->id)
                ->withInput()
                ->with('openModal', 'editMatchModal' . $match->id);
        }

        $validated = $validator->validated();
        $before = $match->only(['winner_id', 'loser_id', 'winner_score', 'loser_score', 'game_type']);

        DB::transaction(function () use ($match, $validated, $leagueStats): void {
            $match->update($validated);
            $leagueStats->recalculateParticipantStats();
        });

        Log::channel('league')->info('Match updated', [
            'match_id' => $match->id,
            'before' => $before,
            'after' => $match->only(['winner_id', 'loser_id', 'winner_score', 'loser_score', 'game_type']),
        ]);

        return redirect()->route('dashboard')->with('success', 'Match updated successfully!');
    }

    // Delete one match result and recalculate the standings afterwards.
    public function destroy(MatchGame $match, LeagueStatsService $leagueStats)
    {
        $deletedMatch = $match->only(['id', 'winner_id', 'loser_id', 'winner_score', 'loser_score', 'game_type']);

        DB::transaction(function () use ($match, $leagueStats): void {
            $match->delete();
            $leagueStats->recalculateParticipantStats();
        });

        Log::channel('league')->info('Match deleted', [
            'match_id' => $deletedMatch['id'],
            'winner_id' => $deletedMatch['winner_id'],
            'loser_id' => $deletedMatch['loser_id'],
            'winner_score' => $deletedMatch['winner_score'],
            'loser_score' => $deletedMatch['loser_score'],
            'game_type' => $deletedMatch['game_type'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Match deleted successfully!');
    }
}

<?php

namespace App\Services;

use App\Models\MatchGame;
use App\Models\Participant;

class LeagueStatsService
{
    public function recalculateParticipantStats(): void
    {
        Participant::query()->update([
            'points' => 0,
            'wins' => 0,
            'losses' => 0,
            'matches_played' => 0,
        ]);

        $winnerStats = MatchGame::query()
            ->selectRaw('winner_id, COUNT(*) as wins')
            ->groupBy('winner_id')
            ->get()
            ->keyBy('winner_id');

        $loserStats = MatchGame::query()
            ->selectRaw('loser_id, COUNT(*) as losses')
            ->groupBy('loser_id')
            ->get()
            ->keyBy('loser_id');

        Participant::query()->each(function (Participant $participant) use ($winnerStats, $loserStats): void {
            $wins = (int) ($winnerStats->get($participant->id)?->wins ?? 0);
            $losses = (int) ($loserStats->get($participant->id)?->losses ?? 0);

            $participant->forceFill([
                'points' => $wins * 3,
                'wins' => $wins,
                'losses' => $losses,
                'matches_played' => $wins + $losses,
            ])->save();
        });
    }
}
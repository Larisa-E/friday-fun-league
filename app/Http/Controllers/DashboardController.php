<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // dashboard view
    public function index(Request $request)
    {
        $participants = $this->standingsQuery()->get();

        $matchSearch = trim((string) $request->input('match_search', ''));
        $gameType = trim((string) $request->input('game_type', ''));

        $latestMatches = MatchGame::query()
            ->with(['winner', 'loser'])
            ->orderByDesc('played_at')
            ->limit(10)
            ->get();

        $matchHistoryQuery = MatchGame::query()
            ->with(['winner', 'loser'])
            ->orderByDesc('played_at');

        if ($matchSearch !== '') {
            $matchHistoryQuery->where(function (Builder $query) use ($matchSearch): void {
                $query->where('game_type', 'like', "%{$matchSearch}%")
                    ->orWhereHas('winner', function (Builder $winnerQuery) use ($matchSearch): void {
                        $winnerQuery->where('name', 'like', "%{$matchSearch}%");
                    })
                    ->orWhereHas('loser', function (Builder $loserQuery) use ($matchSearch): void {
                        $loserQuery->where('name', 'like', "%{$matchSearch}%");
                    });
            });
        }

        if ($gameType !== '') {
            $matchHistoryQuery->where('game_type', $gameType);
        }

        $matchHistory = $matchHistoryQuery
            ->limit(25)
            ->get();

        $gameTypes = MatchGame::query()
            ->whereNotNull('game_type')
            ->where('game_type', '!=', '')
            ->select('game_type')
            ->distinct()
            ->orderBy('game_type')
            ->pluck('game_type');

        return view('dashboard', compact(
            'participants',
            'latestMatches',
            'matchHistory',
            'gameTypes',
            'matchSearch',
            'gameType'
        ));
    }

    // return JSON for auto refresh without full page reload
    public function data()
    {
        $participants = $this->standingsQuery()->get();

        $matches = MatchGame::with(['winner', 'loser'])
        ->orderByDesc('played_at')
        ->limit(10)
        ->get();

        return response()->json(compact('participants', 'matches')); // Return data as JSON
    }

    public function stats()
    {
        $participants = $this->standingsQuery()->get();

        $gameBreakdown = MatchGame::query()
            ->selectRaw("COALESCE(NULLIF(game_type, ''), 'Unspecified') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $summary = [
            'participants' => $participants->count(),
            'matches' => MatchGame::count(),
            'total_points' => (int) $participants->sum('points'),
            'avg_winner_score' => round((float) MatchGame::avg('winner_score'), 1),
        ];

        $topPlayer = $participants->first();

        $bestWinRate = $participants
            ->filter(fn (Participant $participant): bool => $participant->matches_played > 0)
            ->sortByDesc(fn (Participant $participant): float => $participant->wins / max($participant->matches_played, 1))
            ->first();

        $mostPlayedGame = $gameBreakdown->first();

        return view('stats', compact(
            'participants',
            'gameBreakdown',
            'summary',
            'topPlayer',
            'bestWinRate',
            'mostPlayedGame'
        ));
    }

    private function standingsQuery()
    {
        return Participant::query()
            ->orderByDesc('points')
            ->orderByRaw('CASE WHEN matches_played > 0 THEN wins * 1.0 / matches_played ELSE 0 END DESC')
            ->orderBy('name');
    }
}

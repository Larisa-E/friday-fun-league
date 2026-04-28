<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const LEADERBOARD_LIMIT = 10;

    // Load the dashboard and show only the first history rows at the start.
    public function index(Request $request)
    {
        $participants = $this->standingsQuery()->get();
        $leaderboardParticipants = $participants->take(self::LEADERBOARD_LIMIT)->values();
        $totalLeaderboardParticipants = $participants->count();

        $matchSearch = trim((string) $request->input('match_search', ''));
        $gameType = trim((string) $request->input('game_type', ''));

        $latestMatches = MatchGame::query()
            ->with(['winner', 'loser'])
            ->orderByDesc('played_at')
            ->limit(10)
            ->get();

        $matchHistoryQuery = $this->matchHistoryQuery($matchSearch, $gameType);

        $matchHistory = $matchHistoryQuery
            ->limit(10)
            ->get();

        // If the page comes back with an error, also load that match so its Edit popup can open again.
        $matchModalTargets = collect($matchHistory->all());
        $openModal = (string) $request->session()->get('openModal', '');

        if (preg_match('/^editMatchModal(\d+)$/', $openModal, $matches)) {
            $modalMatch = MatchGame::query()
                ->with(['winner', 'loser'])
                ->find((int) $matches[1]);

            if ($modalMatch && ! $matchModalTargets->contains('id', $modalMatch->id)) {
                $matchModalTargets->push($modalMatch);
            }
        }

        $totalMatchHistory = $this->matchHistoryQuery($matchSearch, $gameType)->count();

        $gameTypes = MatchGame::query()
            ->whereNotNull('game_type')
            ->where('game_type', '!=', '')
            ->select('game_type')
            ->distinct()
            ->orderBy('game_type')
            ->pluck('game_type');

        return view('dashboard', compact(
            'participants',
            'leaderboardParticipants',
            'totalLeaderboardParticipants',
            'latestMatches',
            'matchHistory',
            'matchModalTargets',
            'totalMatchHistory',
            'gameTypes',
            'matchSearch',
            'gameType'
        ));
    }

    // Send fresh rank-list data and latest matches for the Refresh button.
    public function data(Request $request)
    {
        $participantLimit = $request->has('participant_limit')
            ? max((int) $request->input('participant_limit', 0), 0)
            : self::LEADERBOARD_LIMIT;

        $participants = $this->standingsQuery()
            ->limit($participantLimit)
            ->get();

        $matches = MatchGame::query()
            ->with(['winner', 'loser'])
            ->orderByDesc('played_at')
            ->limit(10)
            ->get();

        $participantTotal = Participant::count();

        return response()->json(compact('participants', 'matches', 'participantTotal'));
    }

    // Prepare the numbers and lists used on the statistics page.
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

    // The Load more button uses this to get the next history rows.
    public function history(Request $request)
    {
        $matchSearch = trim((string) $request->input('match_search', ''));
        $gameType = trim((string) $request->input('game_type', ''));
        $offset = max((int) $request->input('offset', 0), 0);
        // Send only a small group of rows each time.
        $limit = 10;

        $query = $this->matchHistoryQuery($matchSearch, $gameType);

        $matches = $query
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = $this->matchHistoryQuery($matchSearch, $gameType)->count();
        $nextOffset = $offset + $matches->count();

        return response()->json([
            'matches' => $matches,
            'nextOffset' => $nextOffset,
            'hasMore' => $nextOffset < $total,
        ]);
    }

    // The rank-list Load more button uses this to get the next players.
    public function leaderboard(Request $request)
    {
        $offset = max((int) $request->input('offset', 0), 0);
        // Send only a small group of players each time.
        $limit = self::LEADERBOARD_LIMIT;

        $participants = $this->standingsQuery()
            ->skip($offset)
            ->limit($limit)
            ->get();

        $total = Participant::count();
        $nextOffset = $offset + $participants->count();

        return response()->json([
            'participants' => $participants,
            'nextOffset' => $nextOffset,
            'hasMore' => $nextOffset < $total,
            'total' => $total,
        ]);
    }

    // Use the same search and filter rules for the first load and for Load more.
    private function matchHistoryQuery(string $matchSearch, string $gameType)
    {
        $query = MatchGame::query()
            ->with(['winner', 'loser'])
            ->orderByDesc('played_at');

        if ($matchSearch !== '') {
            $query->where(function (Builder $builder) use ($matchSearch): void {
                $builder->where('game_type', 'like', "%{$matchSearch}%")
                    ->orWhereHas('winner', function (Builder $winnerQuery) use ($matchSearch): void {
                        $winnerQuery->where('name', 'like', "%{$matchSearch}%");
                    })
                    ->orWhereHas('loser', function (Builder $loserQuery) use ($matchSearch): void {
                        $loserQuery->where('name', 'like', "%{$matchSearch}%");
                    });
            });
        }

        if ($gameType !== '') {
            $query->where('game_type', $gameType);
        }

        return $query;
    }

    // Keep the rank order in one place so both pages use the same rules.
    private function standingsQuery()
    {
        return Participant::query()
            ->orderByDesc('points')
            ->orderByRaw('CASE WHEN matches_played > 0 THEN wins * 1.0 / matches_played ELSE 0 END DESC')
            ->orderBy('name');
    }
}

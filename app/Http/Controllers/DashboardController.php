<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\Participant;
use App\Support\StatsPageCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ViewErrorBag;

class DashboardController extends Controller
{
    private const LEADERBOARD_LIMIT = 10;
    private const SHELL_CACHE_KEY = 'dashboard.page.shell.v1';
    private const RESPONSE_CACHE_KEY = 'dashboard.page.response.v1';

    // Load the dashboard and show only the first history rows at the start.
    public function index(Request $request)
    {
        $matchSearch = trim((string) $request->input('match_search', ''));
        $gameType = trim((string) $request->input('game_type', ''));
        $openModal = (string) $request->session()->get('openModal', '');
        $hasCreateErrors = $this->hasCreateWorkspaceErrors($request);
        $activeWorkspaceTab = $this->resolveActiveWorkspaceTab($request, $openModal, $matchSearch, $gameType);
        $lazyLoadDashboardCollections = $this->shouldServeCachedDashboardResponse($request, $activeWorkspaceTab, $hasCreateErrors, $matchSearch, $gameType, $openModal);

        if ($lazyLoadDashboardCollections) {
            $html = Cache::remember(
                $this->dashboardResponseCacheKey(),
                now()->addMinutes(10),
                fn (): string => $this->buildDashboardView(
                    $request,
                    $lazyLoadDashboardCollections,
                    $activeWorkspaceTab,
                    $hasCreateErrors,
                    $matchSearch,
                    $gameType,
                    $openModal,
                )->render(),
            );

            return response($html);
        }

        return $this->buildDashboardView(
            $request,
            $lazyLoadDashboardCollections,
            $activeWorkspaceTab,
            $hasCreateErrors,
            $matchSearch,
            $gameType,
            $openModal,
        );
    }

    private function buildDashboardView(
        Request $request,
        bool $lazyLoadDashboardCollections,
        string $activeWorkspaceTab,
        bool $hasCreateErrors,
        string $matchSearch,
        string $gameType,
        string $openModal,
    ) {

        $shellData = Cache::remember($this->dashboardShellCacheKey(), now()->addMinutes(10), function (): array {
            $leaderboardParticipants = $this->standingsQuery()
                ->limit(self::LEADERBOARD_LIMIT)
                ->get();

            return [
                'leaderboardParticipants' => $leaderboardParticipants,
                'totalLeaderboardParticipants' => Participant::count(),
                'latestMatches' => MatchGame::query()
                    ->with(['winner', 'loser'])
                    ->orderByDesc('played_at')
                    ->limit(10)
                    ->get(),
                'summaryMatchCount' => MatchGame::count(),
                'trackedGameTypes' => $this->trackedGameTypesCount(),
            ];
        });

        $loadAddWorkspaceInline = $activeWorkspaceTab === 'add' && $hasCreateErrors;
        $workspaceParticipants = ($loadAddWorkspaceInline || $activeWorkspaceTab === 'manage')
            ? $this->standingsQuery()->get()
            : collect();

        $manageWorkspaceData = $activeWorkspaceTab === 'manage'
            ? $this->manageWorkspaceViewData($request, $workspaceParticipants, $matchSearch, $gameType, $openModal)
            : [];

        return view('dashboard', array_merge(
            $shellData,
            compact('activeWorkspaceTab', 'loadAddWorkspaceInline', 'workspaceParticipants', 'lazyLoadDashboardCollections'),
            $manageWorkspaceData,
        ));
    }

    public function addWorkspace()
    {
        return view('dashboard.partials.add-workspace', [
            'participants' => $this->standingsQuery()->get(),
        ]);
    }

    public function manageWorkspace(Request $request)
    {
        $participants = $this->standingsQuery()->get();
        $matchSearch = trim((string) $request->input('match_search', ''));
        $gameType = trim((string) $request->input('game_type', ''));
        $openModal = (string) $request->session()->get('openModal', '');

        return view('dashboard.partials.manage-workspace', $this->manageWorkspaceViewData(
            $request,
            $participants,
            $matchSearch,
            $gameType,
            $openModal
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
        return view('stats', StatsPageCache::remember(function (): array {
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

            return compact(
                'participants',
                'gameBreakdown',
                'summary',
                'topPlayer',
                'bestWinRate',
                'mostPlayedGame'
            );
        }));
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

    private function manageWorkspaceViewData(Request $request, Collection $participants, string $matchSearch, string $gameType, string $openModal): array
    {
        $matchHistory = $this->matchHistoryQuery($matchSearch, $gameType)
            ->limit(10)
            ->get();

        $matchModalTargets = collect($matchHistory->all());

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

        $openParticipantId = preg_match('/^editParticipantModal(\d+)$/', $openModal, $participantModalMatches)
            ? (int) $participantModalMatches[1]
            : null;
        $openMatchId = preg_match('/^editMatchModal(\d+)$/', $openModal, $matchModalMatches)
            ? (int) $matchModalMatches[1]
            : null;
        $activeParticipant = $openParticipantId ? $participants->firstWhere('id', $openParticipantId) : null;
        $sessionErrors = $request->session()->get('errors');
        $activeParticipantErrorBag = $activeParticipant && $sessionErrors instanceof ViewErrorBag
            ? $sessionErrors->getBag('participantUpdate.' . $activeParticipant->id)
            : null;
        $activeMatch = $openMatchId ? $matchModalTargets->firstWhere('id', $openMatchId) : null;
        $activeMatchErrorBag = $activeMatch && $sessionErrors instanceof ViewErrorBag
            ? $sessionErrors->getBag('matchUpdate.' . $activeMatch->id)
            : null;

        return compact(
            'participants',
            'matchHistory',
            'matchModalTargets',
            'totalMatchHistory',
            'gameTypes',
            'matchSearch',
            'gameType',
            'activeParticipant',
            'activeParticipantErrorBag',
            'activeMatch',
            'activeMatchErrorBag'
        );
    }

    private function resolveActiveWorkspaceTab(Request $request, string $openModal, string $matchSearch, string $gameType): string
    {
        $hasCreateErrors = $this->hasCreateWorkspaceErrors($request);

        if ($hasCreateErrors) {
            return 'add';
        }

        $requestedWorkspaceTab = (string) $request->session()->get('activeWorkspaceTab', '');

        if (in_array($requestedWorkspaceTab, ['add', 'manage'], true)) {
            return $requestedWorkspaceTab;
        }

        return ($openModal !== '' || $matchSearch !== '' || $gameType !== '') ? 'manage' : 'add';
    }

    private function trackedGameTypesCount(): int
    {
        return (int) MatchGame::query()
            ->whereNotNull('game_type')
            ->where('game_type', '!=', '')
            ->distinct()
            ->count('game_type');
    }

    private function shouldServeCachedDashboardResponse(
        Request $request,
        string $activeWorkspaceTab,
        bool $hasCreateErrors,
        string $matchSearch,
        string $gameType,
        string $openModal,
    ): bool {
        $sessionErrors = $request->session()->get('errors');

        return $activeWorkspaceTab === 'add'
            && ! app()->runningUnitTests()
            && ! $hasCreateErrors
            && $matchSearch === ''
            && $gameType === ''
            && $openModal === ''
            && ! $request->session()->has('success')
            && ! ($sessionErrors instanceof ViewErrorBag && $sessionErrors->any());
    }

    private function hasCreateWorkspaceErrors(Request $request): bool
    {
        $sessionErrors = $request->session()->get('errors');

        return $sessionErrors instanceof ViewErrorBag
            && (
                $sessionErrors->has('name')
                || $sessionErrors->has('avatar_emoji')
                || $sessionErrors->has('winner_id')
                || $sessionErrors->has('loser_id')
                || $sessionErrors->has('winner_score')
                || $sessionErrors->has('loser_score')
                || $sessionErrors->has('game_type')
            );
    }

    private function dashboardShellCacheKey(): string
    {
        return self::SHELL_CACHE_KEY . '.' . app()->environment();
    }

    private function dashboardResponseCacheKey(): string
    {
        return self::RESPONSE_CACHE_KEY . '.' . app()->environment();
    }
}

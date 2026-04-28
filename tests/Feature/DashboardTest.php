<?php

namespace Tests\Feature;

use App\Models\MatchGame;
use App\Models\Participant;
use App\Services\LeagueStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // This proves the async Refresh button gets the JSON data it needs.
    public function test_dashboard_data_endpoint_returns_json_for_async_refresh(): void
    {
        $winner = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $loser = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        MatchGame::create([
            'winner_id' => $winner->id,
            'loser_id' => $loser->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO',
            'played_at' => now(),
        ]);

        app(LeagueStatsService::class)->recalculateParticipantStats();

        $response = $this->get(route('dashboard.data'));

        $response->assertOk();
        $response->assertJsonPath('participants.0.name', 'Alice');
        $response->assertJsonPath('participants.0.points', 3);
        $response->assertJsonPath('participantTotal', 2);
        $response->assertJsonPath('matches.0.game_type', 'UNO');
        $response->assertJsonPath('matches.0.winner.name', 'Alice');
        $response->assertJsonPath('matches.0.loser.name', 'Bob');
    }

    // This proves the rank list starts small and can load more rows later.
    public function test_dashboard_leaderboard_starts_small_and_can_load_more_rows(): void
    {
        for ($index = 1; $index <= 12; $index++) {
            Participant::create([
                'name' => 'Player ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'avatar_emoji' => null,
                'points' => 20 - $index,
                'wins' => 12 - $index,
                'losses' => $index - 1,
                'matches_played' => 12,
            ]);
        }

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('leaderboardParticipants', function ($leaderboardParticipants) {
            return $leaderboardParticipants->count() === 10
                && $leaderboardParticipants->first()->name === 'Player 01'
                && $leaderboardParticipants->last()->name === 'Player 10';
        });
        $response->assertViewHas('totalLeaderboardParticipants', 12);

        $loadMoreResponse = $this->get(route('dashboard.leaderboard', [
            'offset' => 10,
        ]));

        $loadMoreResponse->assertOk();
        $loadMoreResponse->assertJsonPath('participants.0.name', 'Player 11');
        $loadMoreResponse->assertJsonPath('participants.1.name', 'Player 12');
        $loadMoreResponse->assertJsonPath('nextOffset', 12);
        $loadMoreResponse->assertJsonPath('hasMore', false);
        $loadMoreResponse->assertJsonPath('total', 12);
    }

    // This proves the dashboard filters return only the matching history rows.
    public function test_dashboard_filters_match_history_by_search_and_game_type(): void
    {
        $alice = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $bob = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        $cara = Participant::create([
            'name' => 'Cara',
            'avatar_emoji' => ':D',
        ]);

        $unoMatch = MatchGame::create([
            'winner_id' => $alice->id,
            'loser_id' => $bob->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO Master',
            'played_at' => now(),
        ]);

        MatchGame::create([
            'winner_id' => $cara->id,
            'loser_id' => $alice->id,
            'winner_score' => 9,
            'loser_score' => 4,
            'game_type' => 'Chess',
            'played_at' => now()->subMinute(),
        ]);

        $response = $this->get(route('dashboard', [
            'match_search' => 'UNO',
            'game_type' => 'UNO Master',
        ]));

        $response->assertOk();
        $response->assertViewHas('matchHistory', function ($matchHistory) use ($unoMatch) {
            return $matchHistory->pluck('id')->all() === [$unoMatch->id];
        });
    }

    // This proves the statistics page still gets the summary data it needs.
    public function test_stats_page_returns_summary_data(): void
    {
        $alice = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $bob = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        MatchGame::create([
            'winner_id' => $alice->id,
            'loser_id' => $bob->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO',
            'played_at' => now(),
        ]);

        app(LeagueStatsService::class)->recalculateParticipantStats();

        $response = $this->get(route('stats'));

        $response->assertOk();
        $response->assertSeeText('Statistics');
        $response->assertSeeText('Matches Played');
        $response->assertSeeText('Top Player');
        $response->assertSeeText('Alice');
        $response->assertViewHas('summary', function (array $summary) {
            return $summary['participants'] === 2
                && $summary['matches'] === 1
                && $summary['total_points'] === 3;
        });
    }
}
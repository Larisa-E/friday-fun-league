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
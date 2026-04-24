<?php

namespace Tests\Unit;

use App\Models\MatchGame;
use App\Models\Participant;
use App\Services\LeagueStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculate_participant_stats_updates_points_and_match_totals(): void
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

        $winner->refresh();
        $loser->refresh();

        $this->assertSame(3, $winner->points);
        $this->assertSame(1, $winner->wins);
        $this->assertSame(0, $winner->losses);
        $this->assertSame(1, $winner->matches_played);

        $this->assertSame(0, $loser->points);
        $this->assertSame(0, $loser->wins);
        $this->assertSame(1, $loser->losses);
        $this->assertSame(1, $loser->matches_played);
    }
}

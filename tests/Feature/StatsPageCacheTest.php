<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatsPageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_changes_refresh_the_cached_stats_page(): void
    {
        config()->set('cache.default', 'file');
        Cache::flush();

        $this->get(route('stats'))
            ->assertOk()
            ->assertDontSee('Charlie');

        $this->post(route('participants.store'), [
            'name' => 'Charlie',
            'avatar_emoji' => '',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('stats'))
            ->assertOk()
            ->assertSee('Charlie');
    }

    public function test_match_changes_refresh_the_cached_stats_page(): void
    {
        config()->set('cache.default', 'file');
        Cache::flush();

        $winner = Participant::create([
            'name' => 'Ada',
            'avatar_emoji' => null,
        ]);

        $loser = Participant::create([
            'name' => 'Ben',
            'avatar_emoji' => null,
        ]);

        $this->get(route('stats'))
            ->assertOk()
            ->assertDontSee('Chess');

        $this->post(route('matches.store'), [
            'winner_id' => $winner->id,
            'loser_id' => $loser->id,
            'winner_score' => 5,
            'loser_score' => 3,
            'game_type' => 'Chess',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('stats'))
            ->assertOk()
            ->assertSee('Chess');
    }
}
<?php

namespace Tests\Feature;

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
}
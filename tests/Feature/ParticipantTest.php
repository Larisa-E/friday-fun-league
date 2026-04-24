<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_participant_can_be_created(): void
    {
        $response = $this->post('/participants', [
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('participants', [
            'name' => 'Alice',
            'avatar_emoji' => ':)',
            'points' => 0,
            'wins' => 0,
            'losses' => 0,
            'matches_played' => 0,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_validation_rejects_the_same_participant_as_winner_and_loser(): void
    {
        $participant = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $response = $this->from(route('dashboard'))->post('/matches', [
            'winner_id' => $participant->id,
            'loser_id' => $participant->id,
            'winner_score' => 10,
            'loser_score' => 5,
            'game_type' => 'UNO',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrors('loser_id');
        $this->assertDatabaseCount('match_games', 0);
    }
}
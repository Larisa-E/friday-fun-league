<?php

namespace Tests\Feature;

use App\Models\MatchGame;
use App\Models\Participant;
use App\Services\LeagueStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    use RefreshDatabase;

    // This proves the add participant form creates a new participant row.
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

    // This proves the edit participant flow updates the saved data.
    public function test_a_participant_can_be_updated(): void
    {
        $participant = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $response = $this->put(route('participants.update', $participant), [
            'name' => 'Alicia',
            'avatar_emoji' => ':D',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'name' => 'Alicia',
            'avatar_emoji' => ':D',
        ]);
    }

    // This proves validation errors reopen the same participant edit modal.
    public function test_failed_participant_update_reopens_the_matching_modal_with_errors(): void
    {
        $participant = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        $response = $this->from(route('dashboard'))->put(route('participants.update', $participant), [
            'name' => 'Bob',
            'avatar_emoji' => ':D',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrorsIn('participantUpdate.' . $participant->id, ['name']);
        $response->assertSessionHas('openModal', 'editParticipantModal' . $participant->id);

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'name' => 'Alice',
        ]);
    }

    // This proves deleting a participant also removes related matches and fixes the standings.
    public function test_a_participant_can_be_deleted_and_related_matches_are_removed(): void
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

        $response = $this->delete(route('participants.destroy', $winner));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('participants', [
            'id' => $winner->id,
        ]);
        $this->assertDatabaseCount('match_games', 0);

        $loser->refresh();

        $this->assertSame(0, $loser->points);
        $this->assertSame(0, $loser->wins);
        $this->assertSame(0, $loser->losses);
        $this->assertSame(0, $loser->matches_played);
    }
}

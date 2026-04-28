<?php

namespace Tests\Feature;

use App\Models\MatchGame;
use App\Models\Participant;
use App\Services\LeagueStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchGameTest extends TestCase
{
    use RefreshDatabase;

    // This checks the main validation rule that winner and loser cannot be the same person.
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

    // This proves a new match is saved and the standings are recalculated.
    public function test_a_match_can_be_recorded_and_updates_standings(): void
    {
        $winner = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $loser = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        $response = $this->post('/matches', [
            'winner_id' => $winner->id,
            'loser_id' => $loser->id,
            'winner_score' => 10,
            'loser_score' => 6,
            'game_type' => 'UNO',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('match_games', [
            'winner_id' => $winner->id,
            'loser_id' => $loser->id,
            'winner_score' => 10,
            'loser_score' => 6,
            'game_type' => 'UNO',
        ]);

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

    // This proves editing a match also updates the standings correctly.
    public function test_a_match_can_be_updated_and_recalculates_standings(): void
    {
        $alice = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $bob = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        $match = MatchGame::create([
            'winner_id' => $alice->id,
            'loser_id' => $bob->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO',
            'played_at' => now()->subMinute(),
        ]);

        app(LeagueStatsService::class)->recalculateParticipantStats();

        $response = $this->put(route('matches.update', $match), [
            'winner_id' => $bob->id,
            'loser_id' => $alice->id,
            'winner_score' => 11,
            'loser_score' => 9,
            'game_type' => 'Chess',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('match_games', [
            'id' => $match->id,
            'winner_id' => $bob->id,
            'loser_id' => $alice->id,
            'winner_score' => 11,
            'loser_score' => 9,
            'game_type' => 'Chess',
        ]);

        $alice->refresh();
        $bob->refresh();

        $this->assertSame(0, $alice->points);
        $this->assertSame(0, $alice->wins);
        $this->assertSame(1, $alice->losses);
        $this->assertSame(1, $alice->matches_played);

        $this->assertSame(3, $bob->points);
        $this->assertSame(1, $bob->wins);
        $this->assertSame(0, $bob->losses);
        $this->assertSame(1, $bob->matches_played);
    }

    // This proves validation errors reopen the correct edit modal after redirect.
    public function test_failed_match_update_reopens_the_matching_modal_with_errors(): void
    {
        $alice = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $bob = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        $match = MatchGame::create([
            'winner_id' => $alice->id,
            'loser_id' => $bob->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO',
            'played_at' => now(),
        ]);

        $response = $this->from(route('dashboard'))->put(route('matches.update', $match), [
            'winner_id' => $alice->id,
            'loser_id' => $alice->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrorsIn('matchUpdate.' . $match->id, ['loser_id']);
        $response->assertSessionHas('openModal', 'editMatchModal' . $match->id);
    }

    // This proves deleting a match removes it and recalculates the standings.
    public function test_a_match_can_be_deleted_and_recalculates_standings(): void
    {
        $winner = Participant::create([
            'name' => 'Alice',
            'avatar_emoji' => ':)',
        ]);

        $loser = Participant::create([
            'name' => 'Bob',
            'avatar_emoji' => ':(',
        ]);

        $match = MatchGame::create([
            'winner_id' => $winner->id,
            'loser_id' => $loser->id,
            'winner_score' => 10,
            'loser_score' => 7,
            'game_type' => 'UNO',
            'played_at' => now(),
        ]);

        app(LeagueStatsService::class)->recalculateParticipantStats();

        $response = $this->delete(route('matches.destroy', $match));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('match_games', [
            'id' => $match->id,
        ]);

        $winner->refresh();
        $loser->refresh();

        $this->assertSame(0, $winner->points);
        $this->assertSame(0, $winner->wins);
        $this->assertSame(0, $winner->losses);
        $this->assertSame(0, $winner->matches_played);

        $this->assertSame(0, $loser->points);
        $this->assertSame(0, $loser->wins);
        $this->assertSame(0, $loser->losses);
        $this->assertSame(0, $loser->matches_played);
    }
}
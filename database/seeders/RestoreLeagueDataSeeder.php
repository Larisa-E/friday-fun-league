<?php

namespace Database\Seeders;

use App\Services\LeagueStatsService;
use App\Support\StatsPageCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RestoreLeagueDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('match_games')->truncate();
        DB::table('participants')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('participants')->insert([
            ['id' => 1, 'name' => 'lale', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:45:32', 'updated_at' => '2026-05-06 15:45:32'],
            ['id' => 2, 'name' => 'ida', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:45:37', 'updated_at' => '2026-05-06 15:45:37'],
            ['id' => 3, 'name' => 'nina', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:45:46', 'updated_at' => '2026-05-06 15:45:46'],
            ['id' => 4, 'name' => 'isa', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:04', 'updated_at' => '2026-05-06 15:46:04'],
            ['id' => 5, 'name' => 'elise', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:10', 'updated_at' => '2026-05-06 15:46:10'],
            ['id' => 6, 'name' => 'ren', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:16', 'updated_at' => '2026-05-06 15:46:16'],
            ['id' => 7, 'name' => 'edu', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:24', 'updated_at' => '2026-05-06 15:46:24'],
            ['id' => 8, 'name' => 'manu', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:28', 'updated_at' => '2026-05-06 15:46:28'],
            ['id' => 9, 'name' => 'lola', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:33', 'updated_at' => '2026-05-06 15:46:33'],
            ['id' => 10, 'name' => 'siera', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:39', 'updated_at' => '2026-05-06 15:46:39'],
            ['id' => 11, 'name' => 'ana', 'avatar_emoji' => null, 'points' => 0, 'wins' => 0, 'losses' => 0, 'matches_played' => 0, 'created_at' => '2026-05-06 15:46:53', 'updated_at' => '2026-05-06 15:46:53'],
        ]);

        DB::table('match_games')->insert([
            ['id' => 1, 'winner_id' => 10, 'loser_id' => 6, 'winner_score' => 4, 'loser_score' => 2, 'game_type' => 'uno', 'played_at' => '2026-05-06 15:47:18', 'created_at' => '2026-05-06 15:47:18', 'updated_at' => '2026-05-06 15:47:18'],
            ['id' => 2, 'winner_id' => 3, 'loser_id' => 8, 'winner_score' => 10, 'loser_score' => 2, 'game_type' => 'joking', 'played_at' => '2026-05-06 15:48:09', 'created_at' => '2026-05-06 15:48:09', 'updated_at' => '2026-05-06 15:48:09'],
            ['id' => 3, 'winner_id' => 7, 'loser_id' => 5, 'winner_score' => 7, 'loser_score' => 6, 'game_type' => 'Catan', 'played_at' => '2026-05-06 15:49:51', 'created_at' => '2026-05-06 15:49:51', 'updated_at' => '2026-05-06 15:49:51'],
            ['id' => 4, 'winner_id' => 1, 'loser_id' => 2, 'winner_score' => 4, 'loser_score' => 1, 'game_type' => 'Payday', 'played_at' => '2026-05-06 15:50:08', 'created_at' => '2026-05-06 15:50:08', 'updated_at' => '2026-05-06 15:50:08'],
            ['id' => 5, 'winner_id' => 5, 'loser_id' => 11, 'winner_score' => 8, 'loser_score' => 1, 'game_type' => 'Mastermind', 'played_at' => '2026-05-06 15:50:29', 'created_at' => '2026-05-06 15:50:29', 'updated_at' => '2026-05-06 15:50:29'],
            ['id' => 6, 'winner_id' => 9, 'loser_id' => 11, 'winner_score' => 3, 'loser_score' => 1, 'game_type' => 'uno', 'played_at' => '2026-05-06 15:51:08', 'created_at' => '2026-05-06 15:51:08', 'updated_at' => '2026-05-06 15:51:08'],
            ['id' => 7, 'winner_id' => 4, 'loser_id' => 7, 'winner_score' => 2, 'loser_score' => 0, 'game_type' => 'Mastermind', 'played_at' => '2026-05-06 15:51:30', 'created_at' => '2026-05-06 15:51:30', 'updated_at' => '2026-05-06 15:51:30'],
            ['id' => 8, 'winner_id' => 10, 'loser_id' => 9, 'winner_score' => 4, 'loser_score' => 2, 'game_type' => 'Payday', 'played_at' => '2026-05-06 15:52:05', 'created_at' => '2026-05-06 15:52:05', 'updated_at' => '2026-05-06 15:52:05'],
            ['id' => 9, 'winner_id' => 2, 'loser_id' => 4, 'winner_score' => 2, 'loser_score' => 1, 'game_type' => 'Mastermind', 'played_at' => '2026-05-06 15:52:26', 'created_at' => '2026-05-06 15:52:26', 'updated_at' => '2026-05-06 15:52:26'],
            ['id' => 10, 'winner_id' => 4, 'loser_id' => 1, 'winner_score' => 7, 'loser_score' => 0, 'game_type' => 'uno', 'played_at' => '2026-05-06 15:52:45', 'created_at' => '2026-05-06 15:52:45', 'updated_at' => '2026-05-06 15:52:45'],
            ['id' => 11, 'winner_id' => 7, 'loser_id' => 5, 'winner_score' => 2, 'loser_score' => 1, 'game_type' => 'uno', 'played_at' => '2026-05-06 15:53:03', 'created_at' => '2026-05-06 15:53:03', 'updated_at' => '2026-05-06 15:53:03'],
            ['id' => 12, 'winner_id' => 5, 'loser_id' => 10, 'winner_score' => 8, 'loser_score' => 7, 'game_type' => 'Mastermind', 'played_at' => '2026-05-06 15:53:32', 'created_at' => '2026-05-06 15:53:32', 'updated_at' => '2026-05-06 15:53:32'],
            ['id' => 13, 'winner_id' => 9, 'loser_id' => 6, 'winner_score' => 4, 'loser_score' => 0, 'game_type' => 'Catan', 'played_at' => '2026-05-06 15:54:19', 'created_at' => '2026-05-06 15:54:19', 'updated_at' => '2026-05-06 15:54:19'],
        ]);

        app(LeagueStatsService::class)->recalculateParticipantStats();

        Cache::forget('dashboard.page.shell.v1.' . app()->environment());
        Cache::forget('dashboard.page.response.v1.' . app()->environment());
        StatsPageCache::forget();
    }
}
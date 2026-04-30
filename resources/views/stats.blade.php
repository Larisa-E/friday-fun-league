@extends('layouts.app')

@section('content')

@php
    // Save the PHP statistics as JSON so the chart script can use them later.
    $statsPayload = json_encode([
        'participantLabels' => $participants->pluck('name')->values(),
        'participantPoints' => $participants->pluck('points')->values(),
        'participantWins' => $participants->pluck('wins')->values(),
        'gameLabels' => $gameBreakdown->pluck('label')->values(),
        'gameTotals' => $gameBreakdown->pluck('total')->values(),
    ]);

    $standingsSnapshot = $participants->take(5);
    $bestWinRatePercentage = $bestWinRate
        ? number_format(($bestWinRate->wins / max($bestWinRate->matches_played, 1)) * 100, 1)
        : null;
@endphp

{{-- This page shows statistics, charts, and simple facts about the tournament. --}}
<div class="stats-page">
    <section class="stats-header">
        <div>
            <h1 class="section-heading page-heading mb-2">Statistics</h1>
            <p class="stats-header-copy mb-0">A simpler overview of the current tournament numbers, trends, and standings.</p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <span class="button-icon" aria-hidden="true">
                <x-icon name="arrow-left" />
            </span>
            <span>Back to Dashboard</span>
        </a>
    </section>

    {{-- These top boxes show the main numbers before the charts load. --}}
    <div class="row g-3">
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-simple tile-blue">
                <div class="stat-label">Participants</div>
                <div class="stat-value">{{ $summary['participants'] }}</div>
                <div class="stat-footnote">Players currently listed</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-simple tile-navy">
                <div class="stat-label">Matches Played</div>
                <div class="stat-value">{{ $summary['matches'] }}</div>
                <div class="stat-footnote">Saved tournament results</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-simple tile-light">
                <div class="stat-label">Total Points</div>
                <div class="stat-value">{{ $summary['total_points'] }}</div>
                <div class="stat-footnote">Points awarded overall</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-simple tile-gray">
                <div class="stat-label">Average Winner Score</div>
                <div class="stat-value">{{ number_format($summary['avg_winner_score'], 1) }}</div>
                <div class="stat-footnote">Typical winning result</div>
            </div>
        </div>
    </div>

    {{-- The charts below use lazy loading, so the text shows first and the charts load later. --}}
    <div class="row g-4">
    <div class="col-xl-8">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Points by Player</h2>
                <p class="muted-note mb-0">Current points and wins for each player.</p>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="pointsChart" role="img" aria-label="Bar chart showing points and wins by player"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Game Type Breakdown</h2>
                <p class="muted-note mb-0">How often each game type appears in the recorded match history.</p>
            </div>
            <div class="card-body">
                <div class="chart-wrap chart-wrap-compact">
                    <canvas id="gameTypeChart" role="img" aria-label="Doughnut chart showing match totals by game type"></canvas>
                </div>
                <div class="game-breakdown-list">
                    @forelse($gameBreakdown as $game)
                        <article class="game-breakdown-item">
                            <div>
                                <span class="game-breakdown-name">{{ $game->label }}</span>
                                <span class="game-breakdown-note">Registered match type</span>
                            </div>
                            <strong>{{ $game->total }}</strong>
                        </article>
                    @empty
                        <div class="empty-state">No game data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Standings Snapshot</h2>
                <p class="muted-note mb-0">Top five players in a compact ranking table, including current points and win percentage.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 stats-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>Points</th>
                                <th>Win%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($standingsSnapshot as $participant)
                            @php
                                $winPct = $participant->matches_played > 0
                                    ? round($participant->wins / $participant->matches_played * 100, 1)
                                    : 0;
                            @endphp
                            <tr class="{{ $loop->iteration <= 3 ? 'standings-row-top' : '' }}">
                                <td>
                                    <span class="rank-pill">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <span class="player-line">
                                        <span class="player-avatar">{{ $participant->avatar_emoji ?: strtoupper(substr($participant->name, 0, 1)) }}</span>
                                        <span class="fw-semibold">{{ $participant->name }}</span>
                                    </span>
                                </td>
                                <td>{{ $participant->points }}</td>
                                <td>{{ $winPct }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-state">No standings to show yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div
        id="stats-data"
        hidden
        data-payload="{{ $statsPayload }}"
    ></div>
</div>

@endsection
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
    $leader = $participants->first();
    $mostActiveParticipant = $participants->sortByDesc('matches_played')->first();
    $topGame = $gameBreakdown->sortByDesc('total')->first();
    $bestWinRatePercentage = $bestWinRate
        ? number_format(($bestWinRate->wins / max($bestWinRate->matches_played, 1)) * 100, 1)
        : null;
@endphp

{{-- This page shows statistics, charts, and simple facts about the tournament. --}}
<div class="stats-page">
    <section class="stats-header stats-hero">
        <div class="stats-hero-copy">
            <span class="stats-kicker">League Insights</span>
            <h1 class="section-heading page-heading mb-2">Statistics</h1>
            <p class="stats-header-copy mb-0">A clearer read on tournament momentum, scoring trends, and the players shaping the leaderboard.</p>
            <div class="stats-hero-pills" aria-hidden="true">
                <span class="stats-hero-pill">Live charts</span>
                <span class="stats-hero-pill">Standings snapshot</span>
                <span class="stats-hero-pill">Game trends</span>
            </div>
        </div>

        <div class="stats-hero-side">
            <div class="stats-highlight-card">
                <span class="stats-highlight-label">Current Leader</span>
                <strong class="stats-highlight-value">{{ $leader?->name ?? 'No leader yet' }}</strong>
                <span class="stats-highlight-note">
                    @if($leader)
                        {{ $leader->points }} points across {{ $leader->matches_played }} matches.
                    @else
                        Add results to start building the leaderboard.
                    @endif
                </span>
            </div>
            <div class="stats-mini-grid">
                <article class="stats-mini-panel">
                    <span class="stats-mini-label">Best Win Rate</span>
                    <strong>{{ $bestWinRatePercentage ? $bestWinRatePercentage . '%' : 'N/A' }}</strong>
                    <span>{{ $bestWinRate?->name ?? 'No matches yet' }}</span>
                </article>
                <article class="stats-mini-panel">
                    <span class="stats-mini-label">Most Played Game</span>
                    <strong>{{ $topGame?->label ?? 'N/A' }}</strong>
                    <span>
                        @if($topGame)
                            {{ $topGame->total }} logged matches
                        @else
                            No game data yet
                        @endif
                    </span>
                </article>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary stats-back-button">
                <span class="button-icon" aria-hidden="true">
                    <x-icon name="arrow-left" />
                </span>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </section>

    <div class="stats-summary-strip">
        <article class="stats-summary-card stats-summary-card-primary">
            <span class="stats-summary-label">Participants</span>
            <strong class="stats-summary-value">{{ $summary['participants'] }}</strong>
            <span class="stats-summary-meta">Players currently listed</span>
        </article>
        <article class="stats-summary-card stats-summary-card-deep">
            <span class="stats-summary-label">Matches Played</span>
            <strong class="stats-summary-value">{{ $summary['matches'] }}</strong>
            <span class="stats-summary-meta">Saved tournament results</span>
        </article>
        <article class="stats-summary-card stats-summary-card-light">
            <span class="stats-summary-label">Total Points</span>
            <strong class="stats-summary-value">{{ $summary['total_points'] }}</strong>
            <span class="stats-summary-meta">Points awarded across all results</span>
        </article>
        <article class="stats-summary-card stats-summary-card-muted">
            <span class="stats-summary-label">Average Winner Score</span>
            <strong class="stats-summary-value">{{ number_format($summary['avg_winner_score'], 1) }}</strong>
            <span class="stats-summary-meta">Typical winning result</span>
        </article>
    </div>

    <div class="stats-chart-grid">
        <div class="card chart-card stats-chart-card stats-chart-card-wide">
            <div class="card-header stats-section-header">
                <div>
                    <span class="stats-card-kicker">Scoring View</span>
                    <h2 class="section-heading mb-1">Points by Player</h2>
                    <p class="muted-note mb-0">Current points and wins for each player.</p>
                </div>
                <div class="stats-section-badge">
                    <span class="stats-section-badge-label">Most Active</span>
                    <strong>{{ $mostActiveParticipant?->name ?? 'N/A' }}</strong>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-wrap stats-chart-wrap">
                    <canvas id="pointsChart" role="img" aria-label="Bar chart showing points and wins by player"></canvas>
                </div>
                <div class="chart-summary-grid">
                    <article class="chart-summary-tile">
                        <span class="chart-summary-label">Leader</span>
                        <strong>{{ $leader?->name ?? 'No leader yet' }}</strong>
                        <span class="chart-summary-note">
                            @if($leader)
                                {{ $leader->points }} points total
                            @else
                                Waiting for results
                            @endif
                        </span>
                    </article>
                    <article class="chart-summary-tile">
                        <span class="chart-summary-label">Best Win Rate</span>
                        <strong>{{ $bestWinRatePercentage ? $bestWinRatePercentage . '%' : 'N/A' }}</strong>
                        <span class="chart-summary-note">{{ $bestWinRate?->name ?? 'No matches yet' }}</span>
                    </article>
                    <article class="chart-summary-tile">
                        <span class="chart-summary-label">Most Matches</span>
                        <strong>{{ $mostActiveParticipant?->matches_played ?? 0 }}</strong>
                        <span class="chart-summary-note">{{ $mostActiveParticipant?->name ?? 'No participant yet' }}</span>
                    </article>
                </div>
            </div>
        </div>

        <div class="card chart-card stats-chart-card stats-chart-card-side">
            <div class="card-header stats-section-header">
                <div>
                    <span class="stats-card-kicker">Distribution</span>
                    <h2 class="section-heading mb-1">Game Type Breakdown</h2>
                    <p class="muted-note mb-0">How often each game type appears in the recorded match history.</p>
                </div>
            </div>
            <div class="card-body stats-side-card-body">
                <div class="chart-wrap chart-wrap-compact stats-donut-wrap">
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

    <div class="card chart-card standings-snapshot-card">
        <div class="card-header stats-section-header">
            <div>
                <span class="stats-card-kicker">Rankings</span>
                <h2 class="section-heading mb-1">Standings Snapshot</h2>
                <p class="muted-note mb-0">Top five players in a compact ranking view, including current points, matches, and win percentage.</p>
            </div>
            @if($leader)
                <div class="stats-section-badge stats-section-badge-compact">
                    <span class="stats-section-badge-label">Leader</span>
                    <strong>{{ $leader->name }}</strong>
                </div>
            @endif
        </div>
        <div class="card-body standings-snapshot-body">
            <div class="standings-snapshot-grid" role="list">
                @forelse($standingsSnapshot as $participant)
                @php
                    $winPct = $participant->matches_played > 0
                        ? round($participant->wins / $participant->matches_played * 100, 1)
                        : 0;
                @endphp
                <article class="standings-snapshot-item {{ $loop->first ? 'standings-snapshot-item-featured' : '' }}" role="listitem">
                    <div class="standings-snapshot-top">
                        <span class="rank-pill">{{ $loop->iteration }}</span>
                        <span class="standings-points-pill">{{ $participant->points }} pts</span>
                    </div>
                    <div class="standings-snapshot-player">
                        <span class="player-line">
                            <span class="player-avatar">{{ $participant->avatar_emoji ?: strtoupper(substr($participant->name, 0, 1)) }}</span>
                            <span class="fw-semibold">{{ $participant->name }}</span>
                        </span>
                    </div>
                    <div class="standings-metric-grid">
                        <div class="standings-metric">
                            <span class="standings-metric-label">Win%</span>
                            <strong>{{ $winPct }}%</strong>
                        </div>
                        <div class="standings-metric">
                            <span class="standings-metric-label">Matches</span>
                            <strong>{{ $participant->matches_played }}</strong>
                        </div>
                    </div>
                </article>
                @empty
                <div class="empty-state">No standings to show yet.</div>
                @endforelse
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
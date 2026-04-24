@extends('layouts.app')

@section('content')

<div class="feature-header">
    <div>
        <h1 class="section-heading mb-1">Statistics</h1>
        <p class="muted-note mb-0">Summary metrics and charts for the current tournament data.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-tile tile-blue">
            <div class="stat-label">Participants</div>
            <div class="stat-value">{{ $summary['participants'] }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-tile tile-navy">
            <div class="stat-label">Matches Played</div>
            <div class="stat-value">{{ $summary['matches'] }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-tile tile-light">
            <div class="stat-label">Total Points Awarded</div>
            <div class="stat-value">{{ $summary['total_points'] }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-tile tile-gray">
            <div class="stat-label">Average Winner Score</div>
            <div class="stat-value">{{ number_format($summary['avg_winner_score'], 1) }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Points by Player</h2>
                <p class="muted-note mb-0">Bar chart of points and wins for each participant.</p>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="pointsChart" role="img" aria-label="Bar chart showing points and wins by player"></canvas>
                </div>
                <div class="chart-summary">
                    @forelse($participants as $participant)
                        <div>{{ $participant->name }}: {{ $participant->points }} points, {{ $participant->wins }} wins.</div>
                    @empty
                        <div>No participant data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Game Type Breakdown</h2>
                <p class="muted-note mb-0">Distribution of registered match types.</p>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="gameTypeChart" role="img" aria-label="Doughnut chart showing match totals by game type"></canvas>
                </div>
                <div class="chart-summary">
                    @forelse($gameBreakdown as $game)
                        <div>{{ $game->label }}: {{ $game->total }} matches.</div>
                    @empty
                        <div>No game data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Highlights</h2>
                <p class="muted-note mb-0">Simple facts you can mention in a presentation.</p>
            </div>
            <div class="card-body">
                <div class="vstack gap-3">
                    <div>
                        <div class="stat-label">Top Player</div>
                        <div class="fw-bold fs-5">{{ $topPlayer?->name ?? 'No matches yet' }}</div>
                    </div>
                    <div>
                        <div class="stat-label">Best Win Rate</div>
                        <div class="fw-bold fs-5">
                            @if($bestWinRate)
                                {{ $bestWinRate->name }}
                                ({{ number_format(($bestWinRate->wins / max($bestWinRate->matches_played, 1)) * 100, 1) }}%)
                            @else
                                No completed matches yet
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">Most Played Game</div>
                        <div class="fw-bold fs-5">
                            {{ $mostPlayedGame?->label ?? 'Unspecified' }}
                            <span class="muted-note">({{ $mostPlayedGame?->total ?? 0 }} matches)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Standings Snapshot</h2>
                <p class="muted-note mb-0">Compact overview of current positions.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Points</th>
                                <th>Win%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($participants as $participant)
                            @php
                                $winPct = $participant->matches_played > 0
                                    ? round($participant->wins / $participant->matches_played * 100, 1)
                                    : 0;
                            @endphp
                            <tr>
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
                                <td colspan="3" class="empty-state">No standings to show yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@php
    $statsPayload = json_encode([
        'participantLabels' => $participants->pluck('name')->values(),
        'participantPoints' => $participants->pluck('points')->values(),
        'participantWins' => $participants->pluck('wins')->values(),
        'gameLabels' => $gameBreakdown->pluck('label')->values(),
        'gameTotals' => $gameBreakdown->pluck('total')->values(),
    ]);
@endphp

@push('scripts')
<div
    id="stats-data"
    hidden
    data-payload="{{ $statsPayload }}"
></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/stats.js') }}"></script>
@endpush
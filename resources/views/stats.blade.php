@extends('layouts.app')

@section('content')

{{-- This page shows statistics, charts, and simple facts about the tournament. --}}
<div class="feature-header">
    <div>
        <h1 class="section-heading mb-1">Statistics</h1>
        <p class="muted-note mb-0">Summary metrics and charts for the current tournament data.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

{{-- These top boxes show the main numbers before the charts load. --}}
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

{{-- The charts below use lazy loading, so the text shows first and the charts load later. --}}
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
    // Save the PHP statistics as JSON so the chart script can use them later.
    $statsPayload = json_encode([
        'participantLabels' => $participants->pluck('name')->values(),
        'participantPoints' => $participants->pluck('points')->values(),
        'participantWins' => $participants->pluck('wins')->values(),
        'gameLabels' => $gameBreakdown->pluck('label')->values(),
        'gameTotals' => $gameBreakdown->pluck('total')->values(),
    ]);
@endphp

@push('scripts')
{{-- Save the chart data and script links in the page, but do not load the chart library yet. --}}
<div
    id="stats-data"
    hidden
    data-payload="{{ $statsPayload }}"
    data-chart-js-url="https://cdn.jsdelivr.net/npm/chart.js"
    data-stats-js-url="{{ asset('js/stats.js') }}"
></div>
<script>
    (() => {
        // This loader waits until the chart area is near, then it loads Chart.js and the chart code.
        const statsNode = document.getElementById('stats-data');
        const chartAnchor = document.getElementById('pointsChart');

        if (!statsNode || !chartAnchor) {
            return;
        }

        const chartJsUrl = statsNode.dataset.chartJsUrl ?? '';
        const statsScriptUrl = statsNode.dataset.statsJsUrl ?? '';
        let chartsRequested = false;

        // Reuse the same script tag if it was already loaded before.
        const loadScript = (src) => new Promise((resolve, reject) => {
            if (!src) {
                reject(new Error('Missing script URL.'));
                return;
            }

            const existingScript = document.querySelector(`script[src="${src}"]`);

            if (existingScript) {
                if (existingScript.dataset.loaded === 'true' || (src === statsScriptUrl && window.initStatsCharts)) {
                    resolve();
                    return;
                }

                existingScript.addEventListener('load', () => resolve(), { once: true });
                existingScript.addEventListener('error', () => reject(new Error(`Could not load ${src}`)), { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.addEventListener('load', () => {
                script.dataset.loaded = 'true';
                resolve();
            }, { once: true });
            script.addEventListener('error', () => reject(new Error(`Could not load ${src}`)), { once: true });
            document.body.appendChild(script);
        });

        const loadCharts = async () => {
            if (chartsRequested) {
                return;
            }

            chartsRequested = true;

            try {
                if (!window.Chart) {
                    await loadScript(chartJsUrl);
                }

                if (!window.initStatsCharts) {
                    await loadScript(statsScriptUrl);
                }

                window.initStatsCharts?.();
            } catch (error) {
                console.error('Could not lazy load the statistics charts.', error);
            }
        };

        // Only load the chart files when the chart area gets close to the screen.
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    void loadCharts();
                }
            }, { rootMargin: '120px 0px' });

            observer.observe(chartAnchor);
            return;
        }

        void loadCharts();
    })();
</script>
@endpush
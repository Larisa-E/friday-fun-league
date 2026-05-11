@extends('layouts.app')

@push('styles')
    <link rel="preload" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/css/dashboard-workspace.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/css/dashboard-workspace.css') }}">
    </noscript>
@endpush

@section('content')

@php
    $topParticipant = $leaderboardParticipants->first();
@endphp

<div class="dashboard-page">
    <section class="dashboard-header">
        <div>
            <h1 class="section-heading page-heading mb-2">Tournament Dashboard</h1>
            <p class="dashboard-header-copy mb-0">Manage participants, matches, rankings and history.</p>
        </div>

        <div class="dashboard-header-actions">
            <a href="{{ route('stats') }}" class="btn btn-outline-secondary">
                <span class="button-icon" aria-hidden="true">
                    <x-icon name="chart" />
                </span>
                <span>Statistics</span>
            </a>
            <button id="refresh-dashboard" type="button" class="btn btn-primary" aria-controls="leaderboard-body latest-matches-body">
                <span class="button-icon" aria-hidden="true">
                    <x-icon name="refresh" />
                </span>
                <span>Refresh</span>
            </button>
        </div>
    </section>

    <section class="dashboard-summary-strip">
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-label">Leader</span>
            <strong class="dashboard-summary-value">{{ $topParticipant?->name ?? 'No leader yet' }}</strong>
            <span class="dashboard-summary-meta">
                @if($topParticipant)
                    {{ $topParticipant->points }} points
                @else
                    Add results to build the rank list
                @endif
            </span>
        </article>
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-label">Matches Logged</span>
            <strong class="dashboard-summary-value">{{ $summaryMatchCount }}</strong>
            <span class="dashboard-summary-meta">Stored results in the tournament history.</span>
        </article>
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-label">Game Types</span>
            <strong class="dashboard-summary-value">{{ $trackedGameTypes }}</strong>
            <span class="dashboard-summary-meta">Different games available in filters and statistics.</span>
        </article>
    </section>

    <section class="dashboard-zone">
        <div class="row g-4">
    {{-- This top area shows the rank list and the latest matches first. --}}
    <div class="col-12 col-lg-6">
        <div class="card leaderboard-card">
            <div class="card-header">
                <div>
                    <h2 class="section-heading card-heading mb-1">Rank List</h2>
                    <p class="muted-note mb-0">Sorted by total points and win percentage.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 dashboard-table dashboard-table-leaderboard">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Player</th>
                                <th>Points</th>
                                <th>Wins</th>
                                <th>Losses</th>
                                <th>Matches</th>
                                <th>Win%</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboard-body">
                            @if($lazyLoadDashboardCollections)
                                <tr>
                                    <td colspan="7" class="empty-state">Loading leaderboard…</td>
                                </tr>
                            @else
                                @forelse($leaderboardParticipants as $i => $participant)
                                @php
                                    $winPct = $participant->matches_played > 0
                                        ? round($participant->wins / $participant->matches_played * 100, 1)
                                        : 0;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="fw-semibold">
                                        <span class="player-line">
                                            <span class="player-avatar">{{ $participant->avatar_emoji ?: strtoupper(substr($participant->name, 0, 1)) }}</span>
                                            <span class="player-name">{{ $participant->name }}</span>
                                        </span>
                                    </td>
                                    <td>{{ $participant->points }}</td>
                                    <td>{{ $participant->wins }}</td>
                                    <td>{{ $participant->losses }}</td>
                                    <td>{{ $participant->matches_played }}</td>
                                    <td>{{ $winPct }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="empty-state">No participants yet.</td>
                                </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Start with only the first players, then load more when needed. --}}
                <div class="p-3 d-flex flex-column align-items-center gap-2 dashboard-card-footer">
                    <button
                        id="load-more-leaderboard"
                        type="button"
                        class="btn btn-outline-primary"
                        aria-controls="leaderboard-body"
                        @if($lazyLoadDashboardCollections || $leaderboardParticipants->count() >= $totalLeaderboardParticipants) disabled @endif
                    >
                        @if($lazyLoadDashboardCollections)
                            Loading players…
                        @elseif($leaderboardParticipants->count() >= $totalLeaderboardParticipants)
                            All players loaded
                        @else
                            <span class="button-icon" aria-hidden="true">
                                <x-icon name="arrow-down" />
                            </span>
                            <span>Load more</span>
                        @endif
                    </button>

                    <div id="leaderboard-load-more-status" class="text-muted small" role="status" aria-live="polite" aria-atomic="true">
                        @if($lazyLoadDashboardCollections)
                            Loading leaderboard rows.
                        @elseif($totalLeaderboardParticipants === 0)
                            No players yet.
                        @elseif($leaderboardParticipants->count() >= $totalLeaderboardParticipants)
                            All rank-list rows are already shown.
                        @else
                            Showing {{ $leaderboardParticipants->count() }} of {{ $totalLeaderboardParticipants }} players.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 deferred-panel">
        <div class="card matches-card">
            <div class="card-header">
                <h2 class="section-heading card-heading mb-1">Latest Matches</h2>
                <p class="muted-note mb-0">Newest results are shown first.</p>
            </div>
            <div class="card-body p-0">
                <div id="latest-matches-body" class="match-stack latest-match-stack" role="list">
                    @if($lazyLoadDashboardCollections)
                        <div class="empty-state">Loading latest matches…</div>
                    @else
                        @forelse($latestMatches as $match)
                        <article class="match-tile match-tile-latest" role="listitem">
                            <div class="match-latest-header">
                                <span class="match-latest-game">{{ $match->game_type ?? 'Unspecified' }}</span>
                                <span class="match-latest-time">{{ $match->played_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="match-tile-main match-latest-scoreline">
                                <span class="match-player match-player-winner">{{ $match->winner->name }}</span>
                                <span class="score-badge match-score-badge">{{ $match->winner_score }} - {{ $match->loser_score }}</span>
                                <span class="match-player match-player-loser">{{ $match->loser->name }}</span>
                            </div>
                        </article>
                        @empty
                        <div class="empty-state">No matches yet.</div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>

        </div>
    </section>

    <section class="dashboard-zone dashboard-workspace">
        <div class="dashboard-section-intro">
            <h2 class="dashboard-section-title">League Workspace</h2>
            <p class="dashboard-section-copy mb-0">Keep the leaderboard first, then switch between adding new records and managing saved ones.</p>
        </div>

        <div class="dashboard-workspace-shell">
            <div class="nav nav-pills dashboard-workspace-nav" id="dashboard-workspace-tabs" role="tablist" aria-label="Dashboard workspace sections">
                <button
                    class="nav-link dashboard-workspace-tab {{ $activeWorkspaceTab === 'add' ? 'active' : '' }}"
                    id="workspace-add-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#workspace-add"
                    type="button"
                    role="tab"
                    aria-controls="workspace-add"
                    aria-selected="{{ $activeWorkspaceTab === 'add' ? 'true' : 'false' }}"
                >
                    Add
                </button>
                <button
                    class="nav-link dashboard-workspace-tab {{ $activeWorkspaceTab === 'manage' ? 'active' : '' }}"
                    id="workspace-manage-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#workspace-manage"
                    type="button"
                    role="tab"
                    aria-controls="workspace-manage"
                    aria-selected="{{ $activeWorkspaceTab === 'manage' ? 'true' : 'false' }}"
                >
                    Manage
                </button>
            </div>

            <div class="tab-content dashboard-workspace-content">
                <div
                    class="tab-pane fade {{ $activeWorkspaceTab === 'add' ? 'show active' : '' }}"
                    id="workspace-add"
                    role="tabpanel"
                    aria-labelledby="workspace-add-tab"
                    tabindex="0"
                    data-add-workspace-url="{{ route('dashboard.workspace.add') }}"
                    data-add-workspace-loaded="{{ $loadAddWorkspaceInline ? 'true' : 'false' }}"
                >
                    @if($loadAddWorkspaceInline)
                        @include('dashboard.partials.add-workspace', ['participants' => $workspaceParticipants])
                    @else
                        <div class="workspace-lazy-state" data-add-workspace-placeholder="true">
                            <div class="workspace-lazy-card">
                                <h3 class="workspace-lazy-title">Add tools load in the background.</h3>
                                <p class="workspace-lazy-copy mb-0">This keeps the first dashboard response lighter on mobile.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div
                    class="tab-pane fade {{ $activeWorkspaceTab === 'manage' ? 'show active' : '' }}"
                    id="workspace-manage"
                    role="tabpanel"
                    aria-labelledby="workspace-manage-tab"
                    tabindex="0"
                    data-manage-workspace-url="{{ route('dashboard.workspace.manage') }}"
                    data-manage-workspace-loaded="{{ $activeWorkspaceTab === 'manage' ? 'true' : 'false' }}"
                >
                    @if($activeWorkspaceTab === 'manage')
                        @include('dashboard.partials.manage-workspace')
                    @else
                        <div class="workspace-lazy-state" data-manage-workspace-placeholder="true">
                            <div class="workspace-lazy-card">
                                <h3 class="workspace-lazy-title">Manage tools load when you open this tab.</h3>
                                <p class="workspace-lazy-copy mb-0">This keeps the first dashboard view lighter on mobile.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div id="dashboard-announcer" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

{{-- Save the next leaderboard position so JavaScript can ask for the next players. --}}
<div
    id="leaderboard-state"
    hidden
    data-leaderboard-url="{{ route('dashboard.leaderboard') }}"
    data-offset="{{ $lazyLoadDashboardCollections ? 0 : $leaderboardParticipants->count() }}"
    data-total="{{ $totalLeaderboardParticipants }}"
></div>

{{-- Save the next row position and the active filters so JavaScript can ask for the correct next rows. --}}
<div
    id="dashboard-state"
    hidden
    data-open-modal="{{ session('openModal') }}"
    data-dashboard-url="{{ route('dashboard.data') }}"
    data-hydrate-dashboard="{{ $lazyLoadDashboardCollections ? 'true' : 'false' }}"
></div>

<div class="toast-container position-fixed top-0 end-0 p-3 app-toast-stack">
    <div id="app-toast" class="toast app-toast border-0" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex align-items-center">
            <div class="toast-body d-flex align-items-center gap-2">
                <span class="status-icon" aria-hidden="true">
                    <x-icon name="check" />
                </span>
                <span id="app-toast-message">Action completed.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

</div>

@endsection


@extends('layouts.app')

@section('content')

@php
    // Read the popup name from the session so the same Edit popup can open again after an error.
    $openModalName = (string) session('openModal', '');
    // Find which participant should be shown inside the shared participant popup.
    $openParticipantId = preg_match('/^editParticipantModal(\d+)$/', $openModalName, $participantModalMatches)
        ? (int) $participantModalMatches[1]
        : null;
    // Find which match should be shown inside the shared match popup.
    $openMatchId = preg_match('/^editMatchModal(\d+)$/', $openModalName, $matchModalMatches)
        ? (int) $matchModalMatches[1]
        : null;
    $activeParticipant = $openParticipantId ? $participants->firstWhere('id', $openParticipantId) : null;
    $activeParticipantErrorBag = $activeParticipant
        ? $errors->getBag('participantUpdate.' . $activeParticipant->id)
        : null;
    $activeMatch = $openMatchId ? $matchModalTargets->firstWhere('id', $openMatchId) : null;
    $activeMatchErrorBag = $activeMatch
        ? $errors->getBag('matchUpdate.' . $activeMatch->id)
        : null;
    $topParticipant = $leaderboardParticipants->first();
    $latestMatch = $latestMatches->first();
    $trackedGameTypes = collect($gameTypes)->filter()->count();
    $hasCreateErrors = $errors->has('name')
        || $errors->has('avatar_emoji')
        || $errors->has('winner_id')
        || $errors->has('loser_id')
        || $errors->has('winner_score')
        || $errors->has('loser_score')
        || $errors->has('game_type');
    $activeWorkspaceTab = $hasCreateErrors
        ? 'add'
        : (($activeParticipant || $activeMatch || $matchSearch !== '' || $gameType !== '') ? 'manage' : 'add');
@endphp

<div class="dashboard-page">
    <section class="dashboard-header">
        <div>
            <h1 class="section-heading page-heading mb-2">Tournament Dashboard</h1>
            <p class="dashboard-header-copy mb-0">Manage participants, matches, rankings, and history from one clear workspace.</p>
        </div>

        <div class="dashboard-header-actions">
            <a href="{{ route('stats') }}" class="btn btn-outline-secondary">
                <span class="button-icon" aria-hidden="true">
                    <x-icon name="chart" />
                </span>
                <span>Statistics</span>
            </a>
            <button id="refresh-dashboard" type="button" class="btn btn-primary">
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
            <strong class="dashboard-summary-value">{{ $totalMatchHistory }}</strong>
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
                        </tbody>
                    </table>
                </div>

                {{-- Start with only the first players, then load more when needed. --}}
                <div class="p-3 d-flex flex-column align-items-center gap-2 dashboard-card-footer">
                    <button
                        id="load-more-leaderboard"
                        type="button"
                        class="btn btn-outline-primary"
                        @if($leaderboardParticipants->count() >= $totalLeaderboardParticipants) disabled @endif
                    >
                        @if($leaderboardParticipants->count() >= $totalLeaderboardParticipants)
                            All players loaded
                        @else
                            <span class="button-icon" aria-hidden="true">
                                <x-icon name="arrow-down" />
                            </span>
                            <span>Load more</span>
                        @endif
                    </button>

                    <div id="leaderboard-load-more-status" class="text-muted small">
                        @if($totalLeaderboardParticipants === 0)
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
                <div class="table-responsive">
                    <table class="table table-hover mb-0 dashboard-table dashboard-table-matches">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Result</th>
                                <th>Game</th>
                            </tr>
                        </thead>
                        <tbody id="latest-matches-body">
                            @forelse($latestMatches as $match)
                            <tr>
                                <td class="text-muted small">{{ $match->played_at->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="match-result-line">
                                        <span class="fw-bold text-dark">{{ $match->winner->name }}</span>
                                        <span class="score-badge mx-1">{{ $match->winner_score }} - {{ $match->loser_score }}</span>
                                        <span class="text-muted">{{ $match->loser->name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $match->game_type ?? 'Unspecified' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="empty-state">No matches yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                >
                    <div class="row g-4">

    {{-- These forms are used to add new participants and new match results. --}}
    <div class="col-12 col-lg-6 deferred-panel">
        <div class="card participant-card">
            <div class="card-header">
                <h2 class="section-heading card-heading mb-1">Add Participant</h2>
                <p class="muted-note mb-0">Create a new player for the league.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('participants.store') }}" method="POST" class="dashboard-form" novalidate>
                    @csrf
                    <div class="dashboard-form-group">
                        <label for="participant_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input id="participant_name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter participant name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="dashboard-form-group">
                        <label for="participant_avatar" class="form-label">Avatar Emoji</label>
                        <input id="participant_avatar" type="text" name="avatar_emoji" class="form-control @error('avatar_emoji') is-invalid @enderror" placeholder="Optional" value="{{ old('avatar_emoji') }}">
                        @error('avatar_emoji')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <span class="button-icon" aria-hidden="true">
                            <x-icon name="user-plus" />
                        </span>
                        <span>Add Participant</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 deferred-panel">
        <div class="card match-form-card">
            <div class="card-header">
                <h2 class="section-heading card-heading mb-1">Register Match</h2>
                <p class="muted-note mb-0">Record the winner, loser, score, and game type.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('matches.store') }}" method="POST" class="dashboard-form" novalidate>
                    @csrf
                    <div class="dashboard-form-group">
                        <label for="match_winner" class="form-label">Winner <span class="text-danger">*</span></label>
                        <select id="match_winner" name="winner_id" class="form-select @error('winner_id') is-invalid @enderror" required>
                            <option value="">Select winner</option>
                            @foreach($participants as $participant)
                            <option value="{{ $participant->id }}" @selected(old('winner_id') == $participant->id)>{{ $participant->name }}</option>
                            @endforeach
                        </select>
                        @error('winner_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="dashboard-form-group">
                        <label for="match_loser" class="form-label">Loser <span class="text-danger">*</span></label>
                        <select id="match_loser" name="loser_id" class="form-select @error('loser_id') is-invalid @enderror" required>
                            <option value="">Select loser</option>
                            @foreach($participants as $participant)
                            <option value="{{ $participant->id }}" @selected(old('loser_id') == $participant->id)>{{ $participant->name }}</option>
                            @endforeach
                        </select>
                        @error('loser_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row g-3 dashboard-score-row">
                        <div class="col-sm-6">
                            <div class="dashboard-form-group mb-0">
                                <label for="winner_score" class="form-label">Winner Score <span class="text-danger">*</span></label>
                                <input id="winner_score" type="number" name="winner_score" class="form-control @error('winner_score') is-invalid @enderror" min="0" value="{{ old('winner_score') }}" required>
                                @error('winner_score')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="dashboard-form-group mb-0">
                                <label for="loser_score" class="form-label">Loser Score <span class="text-danger">*</span></label>
                                <input id="loser_score" type="number" name="loser_score" class="form-control @error('loser_score') is-invalid @enderror" min="0" value="{{ old('loser_score') }}" required>
                                @error('loser_score')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-form-group">
                        <label for="game_type" class="form-label">Game Type</label>
                        <input id="game_type" type="text" name="game_type" class="form-control @error('game_type') is-invalid @enderror" placeholder="Example: UNO" value="{{ old('game_type') }}">
                        @error('game_type')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <span class="button-icon" aria-hidden="true">
                            <x-icon name="clipboard-plus" />
                        </span>
                        <span>Register Match</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

                    </div>
                </div>

                <div
                    class="tab-pane fade {{ $activeWorkspaceTab === 'manage' ? 'show active' : '' }}"
                    id="workspace-manage"
                    role="tabpanel"
                    aria-labelledby="workspace-manage-tab"
                    tabindex="0"
                >
                    <div class="row g-4">

    {{-- These sections let the user edit or delete saved data. --}}
    <div class="col-12 deferred-panel">
        <div class="card participant-card">
            <div class="card-header">
                <h2 class="section-heading card-heading mb-1">Manage Participants</h2>
                <p class="muted-note mb-0">Edit names or delete participants when needed.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 dashboard-table dashboard-table-manage">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Points</th>
                                <th>Matches</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($participants as $participant)
                            <tr>
                                <td>
                                    <span class="player-line">
                                        <span class="player-avatar">{{ $participant->avatar_emoji ?: strtoupper(substr($participant->name, 0, 1)) }}</span>
                                        <span class="fw-semibold player-name">{{ $participant->name }}</span>
                                    </span>
                                </td>
                                <td>{{ $participant->points }}</td>
                                <td>{{ $participant->matches_played }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary edit-participant-button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editParticipantModal"
                                            data-participant-id="{{ $participant->id }}"
                                            data-participant-name="{{ $participant->name }}"
                                            data-participant-avatar="{{ $participant->avatar_emoji ?? '' }}"
                                        >
                                            <span class="button-icon" aria-hidden="true">
                                                <x-icon name="pencil" />
                                            </span>
                                            <span>Edit</span>
                                        </button>
                                        <form action="{{ route('participants.destroy', $participant) }}" method="POST" onsubmit="return confirm('Delete this participant? Their matches will also be removed.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <span class="button-icon" aria-hidden="true">
                                                    <x-icon name="trash" />
                                                </span>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-state">No participants to manage yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 deferred-panel">
        <div class="card matches-card">
            <div class="card-header">
                <h2 class="section-heading card-heading mb-1">Search and Manage Matches</h2>
                <p class="muted-note mb-0">Search by player or game type, then edit or delete results.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard') }}" method="GET" class="filter-bar dashboard-filter-bar mb-4">
                    <div class="filter-field">
                        <label for="match_search" class="filter-label">Search</label>
                        <input id="match_search" type="text" name="match_search" class="form-control" placeholder="Search by player or game type" value="{{ $matchSearch }}">
                    </div>
                    <div class="filter-field">
                        <label for="match_game_type_filter" class="filter-label">Game Type</label>
                        <select id="match_game_type_filter" name="game_type" class="form-select">
                            <option value="">All game types</option>
                            @foreach($gameTypes as $type)
                            <option value="{{ $type }}" @selected($gameType === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <span class="button-icon" aria-hidden="true">
                            <x-icon name="filter" />
                        </span>
                        <span>Apply Filters</span>
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <span class="button-icon" aria-hidden="true">
                            <x-icon name="x" />
                        </span>
                        <span>Clear</span>
                    </a>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 dashboard-table dashboard-table-history">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Result</th>
                                <th>Game</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="match-history-body">
                            @forelse($matchHistory as $match)
                            <tr>
                                <td class="text-muted small">{{ $match->played_at->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="match-result-line">
                                        <span class="fw-bold text-dark">{{ $match->winner->name }}</span>
                                        <span class="score-badge mx-1">{{ $match->winner_score }} - {{ $match->loser_score }}</span>
                                        <span class="text-muted">{{ $match->loser->name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $match->game_type ?? 'Unspecified' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary edit-match-button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editMatchModal"
                                            data-match-id="{{ $match->id }}"
                                            data-winner-id="{{ $match->winner_id }}"
                                            data-loser-id="{{ $match->loser_id }}"
                                            data-winner-score="{{ $match->winner_score }}"
                                            data-loser-score="{{ $match->loser_score }}"
                                            data-game-type="{{ $match->game_type ?? '' }}"
                                        >
                                            <span class="button-icon" aria-hidden="true">
                                                <x-icon name="pencil" />
                                            </span>
                                            <span>Edit</span>
                                        </button>
                                        <form action="{{ route('matches.destroy', $match) }}" method="POST" onsubmit="return confirm('Delete this match result?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <span class="button-icon" aria-hidden="true">
                                                    <x-icon name="trash" />
                                                </span>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-state">No matches matched your filter yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Keep the button inside the card so it stays easy to find under the table. --}}
                <div class="mt-3 d-flex flex-column align-items-center gap-2 dashboard-card-footer">
                    <button
                        id="load-more-matches"
                        type="button"
                        class="btn btn-outline-primary"
                        @if($matchHistory->count() >= $totalMatchHistory) disabled @endif
                    >
                        @if($matchHistory->count() >= $totalMatchHistory)
                            All matches loaded
                        @else
                            <span class="button-icon" aria-hidden="true">
                                <x-icon name="arrow-down" />
                            </span>
                            <span>Load more</span>
                        @endif
                    </button>

                    <div id="load-more-status" class="text-muted small">
                        @if($matchHistory->count() >= $totalMatchHistory)
                            All matching history rows are already shown.
                        @else
                            Showing {{ $matchHistory->count() }} of {{ $totalMatchHistory }} matching rows.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

{{-- Save the next leaderboard position so JavaScript can ask for the next players. --}}
<div
    id="leaderboard-state"
    hidden
    data-leaderboard-url="{{ route('dashboard.leaderboard') }}"
    data-offset="{{ $leaderboardParticipants->count() }}"
    data-total="{{ $totalLeaderboardParticipants }}"
></div>

{{-- Save the next row position and the active filters so JavaScript can ask for the correct next rows. --}}
<div
    id="match-history-state"
    hidden
    data-history-url="{{ route('dashboard.history') }}"
    data-offset="{{ $matchHistory->count() }}"
    data-match-search="{{ $matchSearch }}"
    data-game-type="{{ $gameType }}"
    data-total="{{ $totalMatchHistory }}"
    data-participants-base-url="{{ url('/participants') }}"
    data-matches-base-url="{{ url('/matches') }}"
    data-csrf-token="{{ csrf_token() }}"
></div>

{{-- One shared participant popup. It is filled when Edit is clicked, instead of making one popup for every row. --}}
<div class="modal fade" id="editParticipantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="edit-participant-form" action="{{ $activeParticipant ? route('participants.update', $activeParticipant) : url('/participants/0') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Edit Participant</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_participant_name" class="form-label">Name</label>
                        <input id="edit_participant_name" type="text" name="name" class="form-control @if($activeParticipantErrorBag && $activeParticipantErrorBag->has('name')) is-invalid @endif" value="{{ $activeParticipant ? old('name', $activeParticipant->name) : '' }}" required>
                        @if($activeParticipantErrorBag && $activeParticipantErrorBag->has('name'))
                            <div class="field-error">{{ $activeParticipantErrorBag->first('name') }}</div>
                        @endif
                    </div>
                    <div class="mb-0">
                        <label for="edit_participant_avatar" class="form-label">Avatar Emoji</label>
                        <input id="edit_participant_avatar" type="text" name="avatar_emoji" class="form-control @if($activeParticipantErrorBag && $activeParticipantErrorBag->has('avatar_emoji')) is-invalid @endif" value="{{ $activeParticipant ? old('avatar_emoji', $activeParticipant->avatar_emoji) : '' }}" placeholder="Optional">
                        @if($activeParticipantErrorBag && $activeParticipantErrorBag->has('avatar_emoji'))
                            <div class="field-error">{{ $activeParticipantErrorBag->first('avatar_emoji') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- One shared match popup. It is filled when Edit is clicked, instead of making one popup for every row. --}}
<div class="modal fade" id="editMatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="edit-match-form" action="{{ $activeMatch ? route('matches.update', $activeMatch) : url('/matches/0') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Edit Match</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_match_winner" class="form-label">Winner</label>
                            <select id="edit_match_winner" name="winner_id" class="form-select @if($activeMatchErrorBag && $activeMatchErrorBag->has('winner_id')) is-invalid @endif" required>
                                @foreach($participants as $participant)
                                <option value="{{ $participant->id }}" @selected(($activeMatch ? (int) old('winner_id', $activeMatch->winner_id) : (int) old('winner_id', 0)) === $participant->id)>{{ $participant->name }}</option>
                                @endforeach
                            </select>
                            @if($activeMatchErrorBag && $activeMatchErrorBag->has('winner_id'))
                                <div class="field-error">{{ $activeMatchErrorBag->first('winner_id') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="edit_match_loser" class="form-label">Loser</label>
                            <select id="edit_match_loser" name="loser_id" class="form-select @if($activeMatchErrorBag && $activeMatchErrorBag->has('loser_id')) is-invalid @endif" required>
                                @foreach($participants as $participant)
                                <option value="{{ $participant->id }}" @selected(($activeMatch ? (int) old('loser_id', $activeMatch->loser_id) : (int) old('loser_id', 0)) === $participant->id)>{{ $participant->name }}</option>
                                @endforeach
                            </select>
                            @if($activeMatchErrorBag && $activeMatchErrorBag->has('loser_id'))
                                <div class="field-error">{{ $activeMatchErrorBag->first('loser_id') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="edit_winner_score" class="form-label">Winner Score</label>
                            <input id="edit_winner_score" type="number" name="winner_score" class="form-control @if($activeMatchErrorBag && $activeMatchErrorBag->has('winner_score')) is-invalid @endif" min="0" value="{{ $activeMatch ? old('winner_score', $activeMatch->winner_score) : old('winner_score') }}" required>
                            @if($activeMatchErrorBag && $activeMatchErrorBag->has('winner_score'))
                                <div class="field-error">{{ $activeMatchErrorBag->first('winner_score') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="edit_loser_score" class="form-label">Loser Score</label>
                            <input id="edit_loser_score" type="number" name="loser_score" class="form-control @if($activeMatchErrorBag && $activeMatchErrorBag->has('loser_score')) is-invalid @endif" min="0" value="{{ $activeMatch ? old('loser_score', $activeMatch->loser_score) : old('loser_score') }}" required>
                            @if($activeMatchErrorBag && $activeMatchErrorBag->has('loser_score'))
                                <div class="field-error">{{ $activeMatchErrorBag->first('loser_score') }}</div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label for="edit_game_type" class="form-label">Game Type</label>
                            <input id="edit_game_type" type="text" name="game_type" class="form-control @if($activeMatchErrorBag && $activeMatchErrorBag->has('game_type')) is-invalid @endif" value="{{ $activeMatch ? old('game_type', $activeMatch->game_type) : old('game_type') }}" placeholder="Example: UNO">
                            @if($activeMatchErrorBag && $activeMatchErrorBag->has('game_type'))
                                <div class="field-error">{{ $activeMatchErrorBag->first('game_type') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Match</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    id="dashboard-state"
    hidden
    data-open-modal="{{ session('openModal') }}"
    data-dashboard-url="{{ route('dashboard.data') }}"
></div>

</div>

@endsection


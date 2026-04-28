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
@endphp

{{-- Page title and short text about what this dashboard does. --}}
<div class="feature-header">
    <div>
        <h1 class="section-heading mb-1">Tournament Dashboard</h1>
        <p class="muted-note mb-0">Manage participants, match results, rankings, and match history.</p>
    </div>
</div>

<div class="row g-4">
    {{-- This top area shows the rank list and the latest matches first. --}}
    <div class="col-12 col-lg-6">
        <div class="card leaderboard-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="section-heading mb-1">Rank List</h2>
                    <p class="muted-note mb-0">Sorted by total points and win percentage.</p>
                </div>
                <div class="table-actions">
                    <a href="{{ route('stats') }}" class="btn btn-sm btn-outline-secondary">Statistics</a>
                    <button id="refresh-dashboard" type="button" class="btn btn-sm btn-outline-secondary">Refresh</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
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
                                        <span>{{ $participant->name }}</span>
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
                <div class="p-3 d-flex flex-column align-items-center gap-2">
                    <button
                        id="load-more-leaderboard"
                        type="button"
                        class="btn btn-outline-primary"
                        @if($leaderboardParticipants->count() >= $totalLeaderboardParticipants) disabled @endif
                    >
                        @if($leaderboardParticipants->count() >= $totalLeaderboardParticipants)
                            All players loaded
                        @else
                            Load more
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

    {{-- These forms are used to add new participants and new match results. --}}
    <div class="col-12 col-lg-6">
        <div class="card matches-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Latest Matches</h2>
                <p class="muted-note mb-0">Newest results are shown first.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                    <span class="fw-bold text-dark">{{ $match->winner->name }}</span>
                                    <span class="score-badge mx-1">{{ $match->winner_score }} - {{ $match->loser_score }}</span>
                                    <span class="text-muted">{{ $match->loser->name }}</span>
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

    <div class="col-12 col-lg-6">
        <div class="card participant-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Add Participant</h2>
                <p class="muted-note mb-0">Create a new player for the league.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('participants.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="participant_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input id="participant_name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter participant name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="participant_avatar" class="form-label">Avatar Emoji</label>
                        <input id="participant_avatar" type="text" name="avatar_emoji" class="form-control @error('avatar_emoji') is-invalid @enderror" placeholder="Optional" value="{{ old('avatar_emoji') }}">
                        @error('avatar_emoji')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Participant</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card match-form-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Register Match</h2>
                <p class="muted-note mb-0">Record the winner, loser, score, and game type.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('matches.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-3">
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
                    <div class="mb-3">
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
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="winner_score" class="form-label">Winner Score <span class="text-danger">*</span></label>
                            <input id="winner_score" type="number" name="winner_score" class="form-control @error('winner_score') is-invalid @enderror" min="0" value="{{ old('winner_score') }}" required>
                            @error('winner_score')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="loser_score" class="form-label">Loser Score <span class="text-danger">*</span></label>
                            <input id="loser_score" type="number" name="loser_score" class="form-control @error('loser_score') is-invalid @enderror" min="0" value="{{ old('loser_score') }}" required>
                            @error('loser_score')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="game_type" class="form-label">Game Type</label>
                        <input id="game_type" type="text" name="game_type" class="form-control @error('game_type') is-invalid @enderror" placeholder="Example: UNO" value="{{ old('game_type') }}">
                        @error('game_type')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success w-100">Register Match</button>
                </form>
            </div>
        </div>
    </div>

    {{-- These sections let the user edit or delete saved data. --}}
    <div class="col-12">
        <div class="card participant-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Manage Participants</h2>
                <p class="muted-note mb-0">Edit names or delete participants when needed.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                        <span class="fw-semibold">{{ $participant->name }}</span>
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
                                            Edit
                                        </button>
                                        <form action="{{ route('participants.destroy', $participant) }}" method="POST" onsubmit="return confirm('Delete this participant? Their matches will also be removed.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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

    <div class="col-12">
        <div class="card matches-card">
            <div class="card-header">
                <h2 class="section-heading mb-1">Search and Manage Matches</h2>
                <p class="muted-note mb-0">Search by player or game type, then edit or delete results.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard') }}" method="GET" class="filter-bar mb-4">
                    <div>
                        <label for="match_search" class="visually-hidden">Search matches</label>
                        <input id="match_search" type="text" name="match_search" class="form-control" placeholder="Search by player or game type" value="{{ $matchSearch }}">
                    </div>
                    <div>
                        <label for="match_game_type_filter" class="visually-hidden">Filter by game type</label>
                        <select id="match_game_type_filter" name="game_type" class="form-select">
                            <option value="">All game types</option>
                            @foreach($gameTypes as $type)
                            <option value="{{ $type }}" @selected($gameType === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Clear</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                    <span class="fw-bold text-dark">{{ $match->winner->name }}</span>
                                    <span class="score-badge mx-1">{{ $match->winner_score }} - {{ $match->loser_score }}</span>
                                    <span class="text-muted">{{ $match->loser->name }}</span>
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
                                            Edit
                                        </button>
                                        <form action="{{ route('matches.destroy', $match) }}" method="POST" onsubmit="return confirm('Delete this match result?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
                <div class="mt-3 d-flex flex-column align-items-center gap-2">
                    <button
                        id="load-more-matches"
                        type="button"
                        class="btn btn-outline-primary"
                        @if($matchHistory->count() >= $totalMatchHistory) disabled @endif
                    >
                        @if($matchHistory->count() >= $totalMatchHistory)
                            All matches loaded
                        @else
                            Load more
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

@endsection

@push('scripts')
<script>
    // These values connect the buttons to the parts of the page they change.
    const refreshButton = document.getElementById('refresh-dashboard');
    const leaderboardBody = document.getElementById('leaderboard-body');
    const leaderboardLoadMoreButton = document.getElementById('load-more-leaderboard');
    const leaderboardLoadMoreStatus = document.getElementById('leaderboard-load-more-status');
    const leaderboardState = document.getElementById('leaderboard-state');
    const latestMatchesBody = document.getElementById('latest-matches-body');
    const dashboardState = document.getElementById('dashboard-state');
    const dashboardDataUrl = dashboardState?.dataset.dashboardUrl ?? '';
    const leaderboardUrl = leaderboardState?.dataset.leaderboardUrl ?? '';
    const modalToReopen = dashboardState?.dataset.openModal ?? '';
    const dashboardScrollKey = 'dashboard-scroll-position';
    let leaderboardOffset = Number(leaderboardState?.dataset.offset ?? 0);
    let leaderboardTotal = Number(leaderboardState?.dataset.total ?? 0);
    // Old popup names may still come back after an error, so change them to the two shared popup names.
    const resolvedModalToReopen = modalToReopen.startsWith('editParticipantModal')
        ? 'editParticipantModal'
        : modalToReopen.startsWith('editMatchModal')
            ? 'editMatchModal'
            : modalToReopen;
    const defaultRefreshButtonHtml = refreshButton?.innerHTML ?? 'Refresh';

    // Save where the user is on the page before a normal form submit.
    const saveDashboardScroll = () => {
        try {
            window.sessionStorage.setItem(dashboardScrollKey, String(window.scrollY));
        } catch (error) {
            // Ignore storage problems and keep the normal page behavior.
        }
    };

    // After the page reloads, go back to the same place on the dashboard.
    const restoreDashboardScroll = () => {
        try {
            const savedScroll = window.sessionStorage.getItem(dashboardScrollKey);

            if (savedScroll === null) {
                return;
            }

            window.sessionStorage.removeItem(dashboardScrollKey);
            window.requestAnimationFrame(() => {
                window.scrollTo(0, Number(savedScroll) || 0);
            });
        } catch (error) {
            // Ignore storage problems and keep the normal page behavior.
        }
    };

    restoreDashboardScroll();

    // Small helper functions keep the page update code simple.
    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const participantAvatar = (participant) => {
        if (participant.avatar_emoji) {
            return escapeHtml(participant.avatar_emoji);
        }

        const initial = String(participant.name ?? '').trim().charAt(0).toUpperCase();

        return escapeHtml(initial || '?');
    };

    const winPercentage = (participant) => {
        const matchesPlayed = Number(participant.matches_played ?? 0);
        const wins = Number(participant.wins ?? 0);

        if (matchesPlayed <= 0) {
            return '0%';
        }

        return `${((wins / matchesPlayed) * 100).toFixed(1).replace(/\.0$/, '')}%`;
    };

    const formatPlayedAt = (playedAt) => {
        const parsedDate = new Date(playedAt);

        if (Number.isNaN(parsedDate.getTime())) {
            return '';
        }

        return new Intl.DateTimeFormat('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(parsedDate).replace(',', '');
    };

    const renderLeaderboardRows = (participants, startRank = 1) => {
        return participants.map((participant, index) => `
            <tr>
                <td>${startRank + index}</td>
                <td class="fw-semibold">
                    <span class="player-line">
                        <span class="player-avatar">${participantAvatar(participant)}</span>
                        <span>${escapeHtml(participant.name)}</span>
                    </span>
                </td>
                <td>${Number(participant.points ?? 0)}</td>
                <td>${Number(participant.wins ?? 0)}</td>
                <td>${Number(participant.losses ?? 0)}</td>
                <td>${Number(participant.matches_played ?? 0)}</td>
                <td>${winPercentage(participant)}</td>
            </tr>
        `).join('');
    };

    const updateLeaderboardLoadState = () => {
        if (!leaderboardLoadMoreButton || !leaderboardLoadMoreStatus) {
            return;
        }

        if (leaderboardTotal === 0) {
            leaderboardLoadMoreButton.disabled = true;
            leaderboardLoadMoreButton.innerHTML = 'All players loaded';
            leaderboardLoadMoreStatus.textContent = 'No players yet.';
            return;
        }

        if (leaderboardOffset >= leaderboardTotal) {
            leaderboardLoadMoreButton.disabled = true;
            leaderboardLoadMoreButton.innerHTML = 'All players loaded';
            leaderboardLoadMoreStatus.textContent = 'All rank-list rows are already shown.';
            return;
        }

        leaderboardLoadMoreButton.disabled = false;
        leaderboardLoadMoreButton.innerHTML = 'Load more';
        leaderboardLoadMoreStatus.textContent = `Showing ${leaderboardOffset} of ${leaderboardTotal} players.`;
    };

    const renderLeaderboard = (participants) => {
        if (!leaderboardBody) {
            return;
        }

        if (!participants.length) {
            leaderboardBody.innerHTML = '<tr><td colspan="7" class="empty-state">No participants yet.</td></tr>';
            return;
        }

        leaderboardBody.innerHTML = renderLeaderboardRows(participants, 1);
    };

    updateLeaderboardLoadState();

    const renderLatestMatches = (matches) => {
        if (!latestMatchesBody) {
            return;
        }

        if (!matches.length) {
            latestMatchesBody.innerHTML = '<tr><td colspan="3" class="empty-state">No matches yet.</td></tr>';
            return;
        }

        latestMatchesBody.innerHTML = matches.map((match) => `
            <tr>
                <td class="text-muted small">${escapeHtml(formatPlayedAt(match.played_at))}</td>
                <td>
                    <span class="fw-bold text-dark">${escapeHtml(match.winner?.name ?? 'Unknown')}</span>
                    <span class="score-badge mx-1">${Number(match.winner_score ?? 0)} - ${Number(match.loser_score ?? 0)}</span>
                    <span class="text-muted">${escapeHtml(match.loser?.name ?? 'Unknown')}</span>
                </td>
                <td class="text-muted small">${escapeHtml(match.game_type || 'Unspecified')}</td>
            </tr>
        `).join('');
    };

    // Ask the server for the next players and add them to the rank list.
    leaderboardLoadMoreButton?.addEventListener('click', async () => {
        if (!leaderboardUrl || leaderboardLoadMoreButton.disabled) {
            return;
        }

        leaderboardLoadMoreButton.disabled = true;
        leaderboardLoadMoreButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Loading';

        try {
            const params = new URLSearchParams({
                offset: String(leaderboardOffset),
            });

            const response = await fetch(`${leaderboardUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Leaderboard load failed: ${response.status}`);
            }

            const data = await response.json();
            const nextParticipants = Array.isArray(data.participants) ? data.participants : [];

            if (nextParticipants.length > 0 && leaderboardBody) {
                leaderboardBody.insertAdjacentHTML('beforeend', renderLeaderboardRows(nextParticipants, leaderboardOffset + 1));
            }

            leaderboardOffset = Number(data.nextOffset ?? leaderboardOffset);
            leaderboardTotal = Number(data.total ?? leaderboardTotal);

            if (leaderboardState) {
                leaderboardState.dataset.offset = String(leaderboardOffset);
                leaderboardState.dataset.total = String(leaderboardTotal);
            }

            updateLeaderboardLoadState();
        } catch (error) {
            alert('Could not load more players right now.');
            updateLeaderboardLoadState();
        }
    });

    const setRefreshIdle = () => {
        if (!refreshButton) {
            return;
        }

        refreshButton.disabled = false;
        refreshButton.innerHTML = defaultRefreshButtonHtml;
    };

    // When a dashboard form is submitted, remember the current scroll position.
    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) {
            return;
        }

        saveDashboardScroll();
    });

    refreshButton?.addEventListener('click', async () => {
        refreshButton.disabled = true;
        refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Updating';

        if (!dashboardDataUrl) {
            window.location.reload();
            return;
        }

        try {
            const params = new URLSearchParams({
                participant_limit: String(leaderboardOffset),
            });

            const response = await fetch(`${dashboardDataUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Refresh failed with status ${response.status}`);
            }

            const data = await response.json();

            const refreshedParticipants = Array.isArray(data.participants) ? data.participants : [];

            renderLeaderboard(refreshedParticipants);
            renderLatestMatches(Array.isArray(data.matches) ? data.matches : []);

            leaderboardOffset = refreshedParticipants.length;
            leaderboardTotal = Number(data.participantTotal ?? refreshedParticipants.length);

            if (leaderboardState) {
                leaderboardState.dataset.offset = String(leaderboardOffset);
                leaderboardState.dataset.total = String(leaderboardTotal);
            }

            updateLeaderboardLoadState();
        } catch (error) {
            window.location.reload();
            return;
        } finally {
            setRefreshIdle();
        }
    });

    // Open the correct shared popup again after an error so the user can see the message right away.
    if (resolvedModalToReopen && window.bootstrap) {
        const modalElement = document.getElementById(resolvedModalToReopen);

        if (modalElement) {
            const firstInvalidField = modalElement.querySelector('.is-invalid');

            if (firstInvalidField) {
                modalElement.addEventListener('shown.bs.modal', () => {
                    firstInvalidField.focus();
                }, { once: true });
            }

            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    }

    // Load the next history rows in the background and add them to the current table.
    const loadMoreButton = document.getElementById('load-more-matches');
    const loadMoreStatus = document.getElementById('load-more-status');
    const matchHistoryBody = document.getElementById('match-history-body');
    const matchHistoryState = document.getElementById('match-history-state');
    const editParticipantForm = document.getElementById('edit-participant-form');
    const editParticipantNameInput = document.getElementById('edit_participant_name');
    const editParticipantAvatarInput = document.getElementById('edit_participant_avatar');
    const editMatchForm = document.getElementById('edit-match-form');
    const editMatchWinnerSelect = document.getElementById('edit_match_winner');
    const editMatchLoserSelect = document.getElementById('edit_match_loser');
    const editWinnerScoreInput = document.getElementById('edit_winner_score');
    const editLoserScoreInput = document.getElementById('edit_loser_score');
    const editGameTypeInput = document.getElementById('edit_game_type');
    const historyUrl = matchHistoryState?.dataset.historyUrl ?? '';
    const matchesBaseUrl = matchHistoryState?.dataset.matchesBaseUrl ?? '';
    const participantsBaseUrl = matchHistoryState?.dataset.participantsBaseUrl ?? '';
    const csrfToken = matchHistoryState?.dataset.csrfToken ?? '';
    const totalMatchHistory = Number(matchHistoryState?.dataset.total ?? 0);
    let matchOffset = Number(matchHistoryState?.dataset.offset ?? 0);

    // Put the clicked participant data into the shared participant popup.
    const populateParticipantEditModal = (button) => {
        if (!editParticipantForm || !participantsBaseUrl) {
            return;
        }

        const participantId = button.dataset.participantId ?? '';
        editParticipantForm.action = `${participantsBaseUrl}/${participantId}`;

        if (editParticipantNameInput) {
            editParticipantNameInput.value = button.dataset.participantName ?? '';
        }

        if (editParticipantAvatarInput) {
            editParticipantAvatarInput.value = button.dataset.participantAvatar ?? '';
        }
    };

    // Put the clicked match data into the shared match popup.
    const populateMatchEditModal = (button) => {
        if (!editMatchForm || !matchesBaseUrl) {
            return;
        }

        const matchId = button.dataset.matchId ?? '';
        editMatchForm.action = `${matchesBaseUrl}/${matchId}`;

        if (editMatchWinnerSelect) {
            editMatchWinnerSelect.value = button.dataset.winnerId ?? '';
        }

        if (editMatchLoserSelect) {
            editMatchLoserSelect.value = button.dataset.loserId ?? '';
        }

        if (editWinnerScoreInput) {
            editWinnerScoreInput.value = button.dataset.winnerScore ?? '0';
        }

        if (editLoserScoreInput) {
            editLoserScoreInput.value = button.dataset.loserScore ?? '0';
        }

        if (editGameTypeInput) {
            editGameTypeInput.value = button.dataset.gameType ?? '';
        }
    };

    // Use one click listener so Edit works for the first rows and also for rows added later.
    document.addEventListener('click', (event) => {
        const participantButton = event.target.closest('.edit-participant-button');

        if (participantButton) {
            populateParticipantEditModal(participantButton);
            return;
        }

        const matchButton = event.target.closest('.edit-match-button');

        if (matchButton) {
            populateMatchEditModal(matchButton);
        }
    });

    // Rows added later do not have Blade forms, so JavaScript builds the Delete form for them.
    const renderDeleteMatchForm = (matchId) => {
        if (!matchesBaseUrl || !csrfToken) {
            return '';
        }

        return `
            <form action="${matchesBaseUrl}/${matchId}" method="POST" onsubmit="return confirm('Delete this match result?')">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
        `;
    };

    // Build the same row layout for the next group of history rows.
    const renderMatchHistoryRows = (matches) => {
        return matches.map((match) => `
            <tr>
                <td class="text-muted small">${escapeHtml(formatPlayedAt(match.played_at))}</td>
                <td>
                    <span class="fw-bold text-dark">${escapeHtml(match.winner?.name ?? 'Unknown')}</span>
                    <span class="score-badge mx-1">${Number(match.winner_score ?? 0)} - ${Number(match.loser_score ?? 0)}</span>
                    <span class="text-muted">${escapeHtml(match.loser?.name ?? 'Unknown')}</span>
                </td>
                <td class="text-muted small">${escapeHtml(match.game_type || 'Unspecified')}</td>
                <td>
                    <div class="table-actions">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary edit-match-button"
                            data-bs-toggle="modal"
                            data-bs-target="#editMatchModal"
                            data-match-id="${Number(match.id)}"
                            data-winner-id="${Number(match.winner_id ?? 0)}"
                            data-loser-id="${Number(match.loser_id ?? 0)}"
                            data-winner-score="${Number(match.winner_score ?? 0)}"
                            data-loser-score="${Number(match.loser_score ?? 0)}"
                            data-game-type="${escapeHtml(match.game_type || '')}"
                        >Edit</button>
                        ${renderDeleteMatchForm(match.id)}
                    </div>
                </td>
            </tr>
        `).join('');
    };

    // Ask the server for the next rows and add them without reloading the page.
    loadMoreButton?.addEventListener('click', async () => {
        if (!historyUrl || loadMoreButton.disabled) {
            return;
        }

        loadMoreButton.disabled = true;
        loadMoreButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Loading';

        try {
            const params = new URLSearchParams({
                offset: String(matchOffset),
                match_search: matchHistoryState?.dataset.matchSearch ?? '',
                game_type: matchHistoryState?.dataset.gameType ?? '',
            });

            const response = await fetch(`${historyUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`History load failed: ${response.status}`);
            }

            const data = await response.json();

            if (Array.isArray(data.matches) && data.matches.length > 0) {
                matchHistoryBody.insertAdjacentHTML('beforeend', renderMatchHistoryRows(data.matches));
            }

            matchOffset = Number(data.nextOffset ?? matchOffset);

            if (!data.hasMore) {
                loadMoreButton.disabled = true;
                loadMoreButton.innerHTML = 'All matches loaded';
                if (loadMoreStatus) {
                    loadMoreStatus.textContent = 'All matching history rows are already shown.';
                }
                return;
            }

            if (loadMoreStatus) {
                const shownCount = totalMatchHistory > 0 ? Math.min(matchOffset, totalMatchHistory) : matchOffset;
                loadMoreStatus.textContent = `Showing ${shownCount} of ${totalMatchHistory || shownCount} matching rows.`;
            }
        } catch (error) {
            alert('Could not load more matches right now.');
        } finally {
            if (loadMoreButton.innerHTML !== 'All matches loaded') {
                loadMoreButton.disabled = false;
                loadMoreButton.innerHTML = 'Load more';
            }
        }
    });
</script>
@endpush
@extends('layouts.app')

@section('content')

<div class="feature-header">
    <div>
        <h1 class="section-heading mb-1">Tournament Dashboard</h1>
        <p class="muted-note mb-0">Manage participants, match results, rankings, and match history.</p>
    </div>
</div>

<div class="row g-4">
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
                        <tbody>
                            @forelse($participants as $i => $participant)
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
            </div>
        </div>
    </div>

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
                        <tbody>
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
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editParticipantModal{{ $participant->id }}">Edit</button>
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
                        <tbody>
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
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMatchModal{{ $match->id }}">Edit</button>
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
            </div>
        </div>
    </div>
</div>

@foreach($participants as $participant)
@php
    $participantModalId = 'editParticipantModal' . $participant->id;
    $participantErrorBag = $errors->getBag('participantUpdate.' . $participant->id);
    $isParticipantModalOpen = session('openModal') === $participantModalId;
@endphp
<div class="modal fade" id="editParticipantModal{{ $participant->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('participants.update', $participant) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Edit Participant</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_participant_name_{{ $participant->id }}" class="form-label">Name</label>
                        <input id="edit_participant_name_{{ $participant->id }}" type="text" name="name" class="form-control @if($participantErrorBag->has('name')) is-invalid @endif" value="{{ $isParticipantModalOpen ? old('name', $participant->name) : $participant->name }}" required>
                        @if($participantErrorBag->has('name'))
                            <div class="field-error">{{ $participantErrorBag->first('name') }}</div>
                        @endif
                    </div>
                    <div class="mb-0">
                        <label for="edit_participant_avatar_{{ $participant->id }}" class="form-label">Avatar Emoji</label>
                        <input id="edit_participant_avatar_{{ $participant->id }}" type="text" name="avatar_emoji" class="form-control @if($participantErrorBag->has('avatar_emoji')) is-invalid @endif" value="{{ $isParticipantModalOpen ? old('avatar_emoji', $participant->avatar_emoji) : $participant->avatar_emoji }}" placeholder="Optional">
                        @if($participantErrorBag->has('avatar_emoji'))
                            <div class="field-error">{{ $participantErrorBag->first('avatar_emoji') }}</div>
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
@endforeach

@foreach($matchHistory as $match)
@php
    $matchModalId = 'editMatchModal' . $match->id;
    $matchErrorBag = $errors->getBag('matchUpdate.' . $match->id);
    $isMatchModalOpen = session('openModal') === $matchModalId;
@endphp
<div class="modal fade" id="editMatchModal{{ $match->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('matches.update', $match) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Edit Match</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_match_winner_{{ $match->id }}" class="form-label">Winner</label>
                            <select id="edit_match_winner_{{ $match->id }}" name="winner_id" class="form-select @if($matchErrorBag->has('winner_id')) is-invalid @endif" required>
                                @foreach($participants as $participant)
                                <option value="{{ $participant->id }}" @selected(($isMatchModalOpen ? (int) old('winner_id', $match->winner_id) : $match->winner_id) === $participant->id)>{{ $participant->name }}</option>
                                @endforeach
                            </select>
                            @if($matchErrorBag->has('winner_id'))
                                <div class="field-error">{{ $matchErrorBag->first('winner_id') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="edit_match_loser_{{ $match->id }}" class="form-label">Loser</label>
                            <select id="edit_match_loser_{{ $match->id }}" name="loser_id" class="form-select @if($matchErrorBag->has('loser_id')) is-invalid @endif" required>
                                @foreach($participants as $participant)
                                <option value="{{ $participant->id }}" @selected(($isMatchModalOpen ? (int) old('loser_id', $match->loser_id) : $match->loser_id) === $participant->id)>{{ $participant->name }}</option>
                                @endforeach
                            </select>
                            @if($matchErrorBag->has('loser_id'))
                                <div class="field-error">{{ $matchErrorBag->first('loser_id') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="edit_winner_score_{{ $match->id }}" class="form-label">Winner Score</label>
                            <input id="edit_winner_score_{{ $match->id }}" type="number" name="winner_score" class="form-control @if($matchErrorBag->has('winner_score')) is-invalid @endif" min="0" value="{{ $isMatchModalOpen ? old('winner_score', $match->winner_score) : $match->winner_score }}" required>
                            @if($matchErrorBag->has('winner_score'))
                                <div class="field-error">{{ $matchErrorBag->first('winner_score') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="edit_loser_score_{{ $match->id }}" class="form-label">Loser Score</label>
                            <input id="edit_loser_score_{{ $match->id }}" type="number" name="loser_score" class="form-control @if($matchErrorBag->has('loser_score')) is-invalid @endif" min="0" value="{{ $isMatchModalOpen ? old('loser_score', $match->loser_score) : $match->loser_score }}" required>
                            @if($matchErrorBag->has('loser_score'))
                                <div class="field-error">{{ $matchErrorBag->first('loser_score') }}</div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label for="edit_game_type_{{ $match->id }}" class="form-label">Game Type</label>
                            <input id="edit_game_type_{{ $match->id }}" type="text" name="game_type" class="form-control @if($matchErrorBag->has('game_type')) is-invalid @endif" value="{{ $isMatchModalOpen ? old('game_type', $match->game_type) : $match->game_type }}" placeholder="Example: UNO">
                            @if($matchErrorBag->has('game_type'))
                                <div class="field-error">{{ $matchErrorBag->first('game_type') }}</div>
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
@endforeach

@endsection

@push('scripts')
<script>
    const refreshButton = document.getElementById('refresh-dashboard');
    const modalToReopen = @json(session('openModal'));

    refreshButton?.addEventListener('click', () => {
        refreshButton.disabled = true;
        refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Updating';
        window.location.reload();
    });

    if (modalToReopen && window.bootstrap) {
        const modalElement = document.getElementById(modalToReopen);

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
</script>
@endpush
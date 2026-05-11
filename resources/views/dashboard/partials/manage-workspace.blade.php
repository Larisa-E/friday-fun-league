<div class="row g-4">
    <div class="col-12 deferred-panel">
        <div class="card participant-card participant-roster-card">
            <div class="card-header">
                <h2 class="section-heading card-heading mb-1">Manage Participants</h2>
                <p class="muted-note mb-0">Edit names or delete participants when needed.</p>
            </div>
            <div class="card-body participant-roster-body">
                <div class="participant-roster" role="list">
                    @forelse($participants as $participant)
                    <article class="participant-tile" role="listitem">
                        <div class="participant-tile-head">
                            <div class="participant-tile-player-wrap">
                                <span class="player-line participant-tile-player">
                                    <span class="player-avatar">{{ $participant->avatar_emoji ?: strtoupper(substr($participant->name, 0, 1)) }}</span>
                                    <span class="fw-semibold player-name">{{ $participant->name }}</span>
                                </span>
                                <span class="participant-secondary-note">Ready for editing or removal.</span>
                            </div>
                            <span class="participant-points-pill">{{ $participant->points }} pts</span>
                        </div>
                        <div class="participant-tile-stats">
                            <div class="participant-stat">
                                <span class="participant-stat-label">Points</span>
                                <strong>{{ $participant->points }}</strong>
                            </div>
                            <div class="participant-stat">
                                <span class="participant-stat-label">Matches</span>
                                <strong>{{ $participant->matches_played }}</strong>
                            </div>
                        </div>
                        <div class="table-actions participant-tile-actions">
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
                    </article>
                    @empty
                    <div class="empty-state">No participants to manage yet.</div>
                    @endforelse
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
                    <div class="filter-actions">
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
                    </div>
                </form>

                <div id="match-history-body" class="match-stack history-match-stack" role="list">
                    @forelse($matchHistory as $match)
                    <article class="match-tile match-tile-manage" role="listitem">
                        <div class="match-tile-meta">
                            <span class="match-meta-pill">{{ $match->played_at->format('d M Y H:i') }}</span>
                            <span class="match-meta-pill">{{ $match->game_type ?? 'Unspecified' }}</span>
                        </div>
                        <div class="match-tile-main">
                            <span class="match-player match-player-winner">{{ $match->winner->name }}</span>
                            <span class="score-badge match-score-badge">{{ $match->winner_score }} - {{ $match->loser_score }}</span>
                            <span class="match-player match-player-loser">{{ $match->loser->name }}</span>
                        </div>
                        <div class="table-actions match-card-actions">
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
                    </article>
                    @empty
                    <div class="empty-state">No matches matched your filter yet.</div>
                    @endforelse
                </div>

                <div class="mt-3 d-flex flex-column align-items-center gap-2 dashboard-card-footer">
                    <button
                        id="load-more-matches"
                        type="button"
                        class="btn btn-outline-primary"
                        aria-controls="match-history-body"
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

                    <div id="load-more-status" class="text-muted small" role="status" aria-live="polite" aria-atomic="true">
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
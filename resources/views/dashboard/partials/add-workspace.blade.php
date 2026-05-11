<div class="row g-4">
    <div class="col-12 col-lg-6 deferred-panel">
        <div class="card participant-card workspace-panel-card workspace-panel-card-add">
            <div class="card-header workspace-panel-header">
                <span class="panel-kicker">Quick Setup</span>
                <h2 class="section-heading card-heading mb-1">Add Participant</h2>
                <p class="muted-note mb-0">Create a new player for the league.</p>
            </div>
            <div class="card-body">
                <div class="panel-guidance" aria-hidden="true">
                    <span class="panel-guidance-chip">Name required</span>
                    <span class="panel-guidance-chip">Emoji optional</span>
                </div>
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
                    <p class="form-submit-note mb-0">New players appear in the leaderboard immediately.</p>
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
        <div class="card match-form-card workspace-panel-card workspace-panel-card-match">
            <div class="card-header workspace-panel-header">
                <span class="panel-kicker">Record Result</span>
                <h2 class="section-heading card-heading mb-1">Register Match</h2>
                <p class="muted-note mb-0">Record the winner, loser, score, and game type.</p>
            </div>
            <div class="card-body">
                <div class="panel-guidance" aria-hidden="true">
                    <span class="panel-guidance-chip">Winner and loser</span>
                    <span class="panel-guidance-chip">Scores required</span>
                </div>
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
                    <p class="form-submit-note mb-0">Each saved result updates points and history automatically.</p>
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
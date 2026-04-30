
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
    const toastElement = document.getElementById('app-toast');
    const toastMessage = document.getElementById('app-toast-message');
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

    const showToast = (message) => {
        if (!toastElement || !toastMessage || !window.bootstrap?.Toast) {
            return;
        }

        toastMessage.textContent = message;
        window.bootstrap.Toast.getOrCreateInstance(toastElement, {
            delay: 2400,
        }).show();
    };

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
            showToast('Dashboard updated successfully.');
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
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <span class="button-icon" aria-hidden="true">
                        <svg viewBox="0 0 16 16" focusable="false">
                            <path d="M6 1h4a1 1 0 0 1 1 1v1h3v1H2V3h3V2a1 1 0 0 1 1-1Zm1 2h2V2H7v1Zm-3 2h8l-.5 9a1 1 0 0 1-1 .944h-5a1 1 0 0 1-1-.944L4 5Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span>Delete</span>
                </button>
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
                        >
                            <span class="button-icon" aria-hidden="true">
                                <svg viewBox="0 0 16 16" focusable="false">
                                    <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-8.5 8.5L4 13l.646-2.646 8.5-8.5ZM3.5 13.5l2.793-.707L2.707 9.207 2 12a1 1 0 0 0 1.5 1.5Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span>Edit</span>
                        </button>
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
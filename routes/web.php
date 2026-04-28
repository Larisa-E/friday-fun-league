<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchGameController;
use App\Http\Controllers\ParticipantController;

// Main page routes.
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');

// Participant actions from the dashboard forms and modals.
Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');
Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');

// Match actions from the dashboard forms and modals.
Route::post('/matches', [MatchGameController::class, 'store'])->name('matches.store');
Route::put('/matches/{match}', [MatchGameController::class, 'update'])->name('matches.update');
Route::delete('/matches/{match}', [MatchGameController::class, 'destroy'])->name('matches.destroy');

// Returns fresh top-of-dashboard data for the Refresh button.
Route::get('/dashboard-data', [DashboardController::class, 'data'])->name('dashboard.data');
// Returns the next rank-list rows for the leaderboard Load more button.
Route::get('/dashboard-leaderboard', [DashboardController::class, 'leaderboard'])->name('dashboard.leaderboard');
// Returns the next history rows for the Load more button.
Route::get('/dashboard-history', [DashboardController::class, 'history'])->name('dashboard.history');
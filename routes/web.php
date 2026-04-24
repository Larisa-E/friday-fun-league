<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchGameController;
use App\Http\Controllers\ParticipantController;

// dashboard (rank list + latest matches)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');

// Add a new participant (form POST)
Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');
Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');

// Register a match result (form POST)
Route::post('/matches', [MatchGameController::class, 'store'])->name('matches.store');
Route::put('/matches/{match}', [MatchGameController::class, 'update'])->name('matches.update');
Route::delete('/matches/{match}', [MatchGameController::class, 'destroy'])->name('matches.destroy');

// returns JSON for async page updates without full reload
Route::get('/dashboard-data', [DashboardController::class, 'data'])->name('dashboard.data'); 
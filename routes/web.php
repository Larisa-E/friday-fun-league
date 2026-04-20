<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchGameController;
use App\Http\Controllers\ParticipantController;

// dashboard (rank list + latest matches)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Add a new participant (form POST)
Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');

// Register a match result (form POST)
Route::post('/matches', [MatchGameController::class, 'store'])->name('matches.store');

// returns JSON for async page updates without full reload
Route::get('/dashboard-data', [DashboardController::class, 'data'])->name('dashboard.data');
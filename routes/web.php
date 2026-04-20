<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchGameController;
use App\Http\Controllers\ParticipantController;

Route::get('/', DashboardController::class)->name('dashboard'); 

Route::get('/matches', MatchGameController::class)->name('matches');

Route::get('/participants', ParticipantController::class)->name('participants');

// API route to fetch dashboard data (participants + recent matches) without full reload
Route::get('/dashboard-data', [DashboardController::class, 'data'])->name('data');
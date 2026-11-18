<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController
    };

// AUTH
Route::get('/', [AuthController::class, 'loginView'])->name('login');
Route::get('/register', [AuthController::class, 'registerView'])->name('register');

// ADMIN
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
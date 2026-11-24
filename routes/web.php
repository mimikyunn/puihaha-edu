<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WeatherController;
use App\Http\Middleware\EnsureAdministrator;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
    Route::get('/', [AuthController::class, 'loginView'])->name('login');
    Route::get('/register', [AuthController::class, 'registerView'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware(['auth', EnsureAdministrator::class])->group(function (): void {
    Route::get('/weather', [WeatherController::class, 'show'])->middleware('throttle:30,1')->name('weather.show');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/members/{member}/record', [MemberController::class, 'record'])->name('members.record');
    Route::patch('/members/{member}/reactivate', [MemberController::class, 'reactivate'])->name('members.reactivate');
    Route::resource('members', MemberController::class)->names(['index' => 'members']);
    Route::resource('member-categories', CategoryController::class)->parameters(['member-categories' => 'category'])->except('show')->names(['index' => 'member-categories']);
    Route::resource('member-levels', LevelController::class)->parameters(['member-levels' => 'level'])->except('show')->names(['index' => 'member-levels']);
    Route::patch('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuoteController::class, 'index'])->name('home');
Route::get('/network', [QuoteController::class, 'network'])->name('network');

Route::middleware('guest')->group(function () {
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/signup', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::post('/quotes/{quote}/like', [QuoteController::class, 'toggleLike'])->name('quotes.like');
    Route::post('/quotes/{quote}/repost', [QuoteController::class, 'toggleRepost'])->name('quotes.repost');
});

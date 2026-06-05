<?php

use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
| Register issues an email OTP; the account must verify it before the first
| sign-in. "Keep me signed in" uses Laravel's remember-me cookie.
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// OTP step: the account exists but is half-signed-in (tracked via session), so
// these are reachable without the full `auth` guard.
Route::get('/verify', [AuthController::class, 'showVerify'])->name('verify');
Route::post('/verify', [AuthController::class, 'verify']);
Route::post('/verify/resend', [AuthController::class, 'resend'])->name('verify.resend');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| App + JSON API (signed-in only)
|--------------------------------------------------------------------------
| The single-page shell loads once; everything else is AJAX. Reorder routes are
| declared before the {list}/{task} wildcards so "reorder" is never a model id.
*/
Route::middleware('auth')->group(function () {
    Route::view('/', 'app')->name('app');

    Route::prefix('api')->group(function () {
        // Lists
        Route::patch('lists/reorder', [ListController::class, 'reorder']);
        Route::get('lists/{type}', [ListController::class, 'index']);
        Route::post('lists', [ListController::class, 'store']);
        Route::post('lists/{list}/duplicate', [ListController::class, 'duplicate']);
        Route::patch('lists/{list}', [ListController::class, 'update']);
        Route::delete('lists/{list}', [ListController::class, 'destroy']);

        // Tasks within a list
        Route::get('lists/{list}/tasks', [TaskController::class, 'index']);
        Route::patch('lists/{list}/tasks/reorder', [TaskController::class, 'reorder']);

        // Tasks
        Route::post('tasks', [TaskController::class, 'store']);
        Route::patch('tasks/{task}', [TaskController::class, 'update']);
        Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
    });
});

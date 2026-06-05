<?php

use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Single-page app shell — loaded once, everything else is AJAX.
Route::view('/', 'app')->name('app');

/*
|--------------------------------------------------------------------------
| JSON API (web middleware → session + CSRF via <meta> token)
|--------------------------------------------------------------------------
| Reorder routes are declared before the {list}/{task} wildcard routes so the
| literal "reorder" segment is never treated as a model id.
*/
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

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZenjiController;
use App\Http\Controllers\MbrController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('home');
});

Route::get('/smits', function () {
    return view('smits');
});

Route::get('/rates/retail', function () {
    return view('rates.retail');
});

Route::get('/rates/nam', function () {
    return view('rates.nam');
});

Route::get('/rates/int', function () {
    return view('rates.int');
});

Route::get('/zenji', function () {
    return view('zenji');
});

Route::get('/mbr', function () {
    return view('mbr');
});
Route::post('/mbr/process', [MbrController::class, 'process']);

Route::group(['prefix' => 'budlist'], function () {
    Route::get('/', function () {
        return view('budlist.index');
    });

    Route::get('/budget', 'App\Http\Controllers\ListController@getBudgetLists');
    Route::get('/budget/archived', 'App\Http\Controllers\ListController@getArchivedLists')->defaults('type', 'budget');
    Route::post('/budget', 'App\Http\Controllers\ListController@store');
    Route::put('/budget/{id}', 'App\Http\Controllers\ListController@update');
    Route::get('/budget/{id}', 'App\Http\Controllers\ListController@show');
    Route::get('/{type}/duplicate/{id}', 'App\Http\Controllers\ListController@duplicate')
        ->where('type', 'budget|loan|shopping');
    Route::put('/{type}/archive/{id}', 'App\Http\Controllers\ListController@archive')
        ->where('type', 'budget|loan|shopping');
    Route::put('/{type}/unarchive/{id}', 'App\Http\Controllers\ListController@unarchive')
        ->where('type', 'budget|loan|shopping');
    Route::delete('/budget/{id}', 'App\Http\Controllers\ListController@destroy');

    Route::post('/budget/list/{id}', 'App\Http\Controllers\ListItemController@store');
    Route::put('/budget/list/{id}', 'App\Http\Controllers\ListItemController@update');
    Route::delete('/budget/list/{id}', 'App\Http\Controllers\ListItemController@destroy');

    Route::get('/loan', 'App\Http\Controllers\ListController@getLoanLists');
    Route::get('/loan/archived', 'App\Http\Controllers\ListController@getArchivedLists')->defaults('type', 'loan');
    Route::post('/loan', 'App\Http\Controllers\ListController@store');
    Route::put('/loan/{id}', 'App\Http\Controllers\ListController@update');
    Route::get('/loan/{id}', 'App\Http\Controllers\ListController@show');
    Route::delete('/loan/{id}', 'App\Http\Controllers\ListController@destroy');

    Route::post('/loan/list/{id}', 'App\Http\Controllers\ListItemController@store');
    Route::put('/loan/list/{id}', 'App\Http\Controllers\ListItemController@update');
    Route::delete('/loan/list/{id}', 'App\Http\Controllers\ListItemController@destroy');

    Route::get('/shopping', 'App\Http\Controllers\ListController@getShoppingLists');
    Route::get('/shopping/archived', 'App\Http\Controllers\ListController@getArchivedLists')->defaults('type', 'shopping');
    Route::post('/shopping', 'App\Http\Controllers\ListController@store');
    Route::put('/shopping/{id}', 'App\Http\Controllers\ListController@update');
    Route::get('/shopping/{id}', 'App\Http\Controllers\ListController@show');
    Route::delete('/shopping/{id}', 'App\Http\Controllers\ListController@destroy');

    Route::post('/shopping/list/{id}', 'App\Http\Controllers\ListItemController@store');
    Route::put('/shopping/list/{id}', 'App\Http\Controllers\ListItemController@update');
    Route::delete('/shopping/list/{id}', 'App\Http\Controllers\ListItemController@destroy');
});

Route::get('/sms-blast', [App\Http\Controllers\SmsBlastDemoController::class, 'index']);
Route::post('/sms-blast/send', [App\Http\Controllers\SmsBlastDemoController::class, 'send']);

Route::any('/proxy/retail/{endpoint}', [App\Http\Controllers\ProxyController::class, 'fetchDataRetail'])->where('endpoint', '.*');
Route::any('/proxy/nam/{endpoint}', [App\Http\Controllers\ProxyController::class, 'fetchDataNam'])->where('endpoint', '.*');
Route::any('/proxy/int/{endpoint}', [App\Http\Controllers\ProxyController::class, 'fetchDataInt'])->where('endpoint', '.*');

Route::get('/events', [ZenjiController::class, 'getEvents']);
Route::post('/events', [ZenjiController::class, 'saveEvent']);
Route::delete('/events/{id}', [ZenjiController::class, 'deleteEvent']);
Route::put('/events/{id}', [ZenjiController::class, 'update']);

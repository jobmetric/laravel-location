<?php

use Illuminate\Support\Facades\Route;
use JobMetric\Location\Http\Controllers\LocationCountryController;
use JobMetric\Panelio\Facades\Middleware;

/*
|--------------------------------------------------------------------------
| Laravel Location Routes
|--------------------------------------------------------------------------
|
| All Route in Laravel Location package
|
*/

// route location in panel
Route::prefix('p/{panel}/{section}')->group(function () {
    Route::middleware(Middleware::getMiddlewares())->name('location.')->group(function () {
        // country
        Route::options('location_country', [LocationCountryController::class, 'options'])->name('location_country.options');
        Route::resource('location_country', LocationCountryController::class)->except(['show', 'destroy']);
    });
});

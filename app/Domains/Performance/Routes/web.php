<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('hcm/performance')->name('performance.')->group(function () {
    Route::get('/dashboard', function () {
        return view('performance.dashboard');
    })->name('dashboard');

    Route::get('/goals', function () {
        return view('performance.goals');
    })->name('goals');

    Route::get('/reviews', function () {
        return view('performance.reviews');
    })->name('reviews');

    Route::get('/calibration', function () {
        return view('performance.calibration');
    })->name('calibration');
});
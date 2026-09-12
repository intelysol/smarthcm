<?php

use Illuminate\Support\Facades\Route;

Route::prefix('hcm/productivity')->group(function () {
    Route::get('/executive', fn () => view('productivity.executive'))->name('hcm.productivity.executive');
    Route::get('/manager', fn () => view('productivity.manager'))->name('hcm.productivity.manager');
    Route::get('/hr', fn () => view('productivity.hr'))->name('hcm.productivity.hr');
    Route::get('/finance', fn () => view('productivity.finance'))->name('hcm.productivity.finance');
    Route::get('/explorer', fn () => view('productivity.explorer'))->name('hcm.productivity.explorer');
});

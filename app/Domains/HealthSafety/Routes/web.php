<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('hcm/health')->name('health.')->group(function () {
    Route::get('/dashboard', function () {
        return view('health_safety.dashboard');
    })->name('dashboard');

    Route::get('/incidents', function () {
        return view('health_safety.incidents');
    })->name('incidents');

    Route::get('/return-to-work', function () {
        return view('health_safety.return_to_work');
    })->name('return_to_work');

    Route::get('/employee/{employeeId}', function ($employeeId) {
        return view('health_safety.employee_health', ['employeeId' => $employeeId]);
    })->name('employee');
});

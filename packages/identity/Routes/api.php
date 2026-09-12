<?php

use Flow\Identity\Presentation\API\IdentityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/identity')->name('api.v1.identity.')->group(function (): void {
    Route::post('login', [IdentityController::class, 'login'])->middleware(['web', 'throttle:login']);
    Route::post('forgot-password', [IdentityController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('reset-password', [IdentityController::class, 'resetPassword'])->middleware('throttle:password-reset');
    Route::middleware(['web', 'auth', 'tenant'])->group(function (): void {
        Route::post('logout', [IdentityController::class, 'logout']);
        Route::post('verify-email', [IdentityController::class, 'verifyEmail']);
        Route::post('change-password', [IdentityController::class, 'changePassword']);
        Route::post('enable-mfa', [IdentityController::class, 'enableMfa']);
        Route::post('disable-mfa', [IdentityController::class, 'disableMfa']);
        Route::get('sessions', [IdentityController::class, 'sessions']);
        Route::delete('sessions/{id}', [IdentityController::class, 'revokeSession']);
        Route::get('profile', [IdentityController::class, 'profile']);
        Route::patch('profile', [IdentityController::class, 'updateProfile']);
    });
});

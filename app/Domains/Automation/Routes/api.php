<?php
use App\Domains\Automation\Http\Controllers\AutomationController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1/automations')->name('api.v1.automations.')->middleware(['web', 'auth', 'tenant'])->group(function (): void { Route::get('/', [AutomationController::class, 'index']); Route::post('/', [AutomationController::class, 'store']); Route::post('{automation}/transition', [AutomationController::class, 'transition']); Route::post('{automation}/run', [AutomationController::class, 'run']); Route::get('{automation}/executions', [AutomationController::class, 'executions']); });

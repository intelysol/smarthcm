<?php
use App\Domains\Api\Http\Controllers\ApiGovernanceController; use Illuminate\Support\Facades\Route;
Route::prefix('v1/platform/api')->middleware(['web','auth','tenant'])->group(function():void{Route::get('catalog',[ApiGovernanceController::class,'catalog']);Route::get('clients',[ApiGovernanceController::class,'clients']);Route::post('clients',[ApiGovernanceController::class,'createClient']);Route::post('clients/{client}/revoke',[ApiGovernanceController::class,'revoke']);});

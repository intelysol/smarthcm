<?php
use App\Domains\Marketplace\Http\Controllers\MarketplaceController; use Illuminate\Support\Facades\Route;
Route::prefix('v1/marketplace')->middleware(['web','auth','tenant'])->group(function():void{Route::get('extensions',[MarketplaceController::class,'index']);Route::post('extensions',[MarketplaceController::class,'publish']);Route::post('extensions/{extension}/versions',[MarketplaceController::class,'version']);Route::post('extensions/{extension}/versions/{version}/install',[MarketplaceController::class,'install']);Route::get('installed',[MarketplaceController::class,'installed']);});

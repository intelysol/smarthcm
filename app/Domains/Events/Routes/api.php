<?php
use App\Domains\Events\Http\Controllers\EventController; use Illuminate\Support\Facades\Route;
Route::prefix('v1/events')->middleware(['web','auth','tenant'])->group(function():void{Route::get('/',[EventController::class,'index']);Route::post('/',[EventController::class,'publish']);Route::post('{event}/replay',[EventController::class,'replay']);});

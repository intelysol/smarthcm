<?php
use App\Domains\Search\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1/search')->name('api.v1.search.')->middleware(['web', 'auth', 'tenant'])->group(function (): void { Route::get('/', [SearchController::class, 'search']); Route::get('suggestions', [SearchController::class, 'suggest']); Route::get('history', [SearchController::class, 'history']); Route::post('saved', [SearchController::class, 'save']); });

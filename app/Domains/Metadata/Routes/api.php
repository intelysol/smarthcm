<?php

use App\Domains\Metadata\Http\Controllers\MetadataDesignerController;
use App\Domains\Metadata\Http\Controllers\MetadataEntityController;
use App\Domains\Metadata\Http\Controllers\DataGovernanceController;
use App\Domains\Metadata\Http\Controllers\MetadataResolutionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/metadata')->name('api.v1.metadata.')->middleware(['web', 'auth', 'tenant'])->group(function (): void {
    Route::get('entities', [MetadataEntityController::class, 'index']);
    Route::post('entities', [MetadataEntityController::class, 'store']);
    Route::get('entities/{entity}', [MetadataEntityController::class, 'show']);
    Route::get('entities/{entity}/definition', [MetadataEntityController::class, 'definition']);
    Route::post('entities/{entity}/fields', [MetadataEntityController::class, 'addField']);
    Route::post('entities/{entity}/records', [MetadataEntityController::class, 'storeRecord']);
    Route::get('artifacts', [MetadataDesignerController::class, 'artifacts']);
    Route::post('artifacts', [MetadataDesignerController::class, 'storeArtifact']);
    Route::post('artifacts/{artifact}/transition', [MetadataDesignerController::class, 'transitionArtifact']);
    Route::get('relationships', [MetadataDesignerController::class, 'relationships']);
    Route::post('relationships', [MetadataDesignerController::class, 'storeRelationship']);
    Route::get('governance/dictionary', [DataGovernanceController::class, 'dictionary']);
    Route::post('governance/dictionary', [DataGovernanceController::class, 'storeDictionary']);
    Route::get('governance/quality-rules', [DataGovernanceController::class, 'rules']);
    Route::get('governance/retention', [DataGovernanceController::class, 'retention']);
    Route::get('resolve/{type}/{key}', [MetadataResolutionController::class, 'artifact']);
});

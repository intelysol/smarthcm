<?php

use App\Domains\PersonalData\Http\Controllers\BankChangeRequestController;
use App\Domains\PersonalData\Http\Controllers\DuplicateDetectionController;
use App\Domains\PersonalData\Http\Controllers\EmployeeDataQualityController;
use App\Domains\PersonalData\Http\Controllers\PersonalDataBulkController;
use App\Domains\PersonalData\Http\Controllers\PersonalDataChangeRequestController;
use App\Domains\PersonalData\Http\Controllers\PersonalDataController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1/hcm')->group(function () {
    // 1. Personal Data Aggregate & Update
    Route::get('employees/{id}/personal-data', [PersonalDataController::class, 'show']);
    Route::put('employees/{id}/personal-data', [PersonalDataController::class, 'update']);

    // 2. Addresses
    Route::get('employees/{id}/addresses', [PersonalDataController::class, 'listAddresses']);
    Route::post('employees/{id}/addresses', [PersonalDataController::class, 'storeAddress']);
    Route::put('personal-data/addresses/{addressId}', [PersonalDataController::class, 'updateAddress']);
    Route::delete('personal-data/addresses/{addressId}', [PersonalDataController::class, 'destroyAddress']);

    // 3. Emergency Contacts
    Route::get('employees/{id}/emergency-contacts', [PersonalDataController::class, 'listEmergencyContacts']);
    Route::post('employees/{id}/emergency-contacts', [PersonalDataController::class, 'storeEmergencyContact']);
    Route::put('personal-data/emergency-contacts/{contactId}', [PersonalDataController::class, 'updateEmergencyContact']);
    Route::delete('personal-data/emergency-contacts/{contactId}', [PersonalDataController::class, 'destroyEmergencyContact']);

    // 4. Dependents
    Route::get('employees/{id}/dependents', [PersonalDataController::class, 'listDependents']);
    Route::post('employees/{id}/dependents', [PersonalDataController::class, 'storeDependent']);
    Route::put('personal-data/dependents/{dependentId}', [PersonalDataController::class, 'updateDependent']);
    Route::delete('personal-data/dependents/{dependentId}', [PersonalDataController::class, 'destroyDependent']);

    // 5. Identifiers
    Route::get('employees/{id}/identifiers', [PersonalDataController::class, 'listIdentifiers']);
    Route::post('employees/{id}/identifiers', [PersonalDataController::class, 'storeIdentifier']);
    Route::put('personal-data/identifiers/{identifierId}', [PersonalDataController::class, 'updateIdentifier']);
    Route::delete('personal-data/identifiers/{identifierId}', [PersonalDataController::class, 'destroyIdentifier']);

    // 6. Bank Change Requests (Governed by Payroll)
    Route::get('bank-change-requests', [BankChangeRequestController::class, 'index']);
    Route::get('employees/{id}/bank-change-requests', [BankChangeRequestController::class, 'employeeRequests']);
    Route::post('employees/{id}/bank-change-requests', [BankChangeRequestController::class, 'store']);
    Route::post('bank-change-requests/{id}/review', [BankChangeRequestController::class, 'review']);

    // 7. General Personal Data Change Requests
    Route::get('personal-data-change-requests', [PersonalDataChangeRequestController::class, 'index']);
    Route::get('employees/{id}/personal-data-change-requests', [PersonalDataChangeRequestController::class, 'employeeRequests']);
    Route::post('employees/{id}/personal-data-change-requests', [PersonalDataChangeRequestController::class, 'store']);
    Route::post('personal-data-change-requests/{id}/review', [PersonalDataChangeRequestController::class, 'review']);

    // 8. Data Quality & Audit
    Route::get('employees/{id}/data-quality', [EmployeeDataQualityController::class, 'show']);
    Route::post('employees/{id}/data-quality/recalculate', [EmployeeDataQualityController::class, 'recalculate']);
    Route::get('data-quality/tenant-summary', [EmployeeDataQualityController::class, 'tenantSummary']);

    // 9. Advisory Duplicate Detection
    Route::get('personal-data/duplicates', [DuplicateDetectionController::class, 'index']);

    // 10. Bulk Operations & Dry-Run Preview
    Route::get('personal-data/bulk/batches', [PersonalDataBulkController::class, 'batches']);
    Route::get('personal-data/bulk/batches/{batchId}', [PersonalDataBulkController::class, 'batch']);
    Route::post('personal-data/bulk/upload', [PersonalDataBulkController::class, 'upload']);
    Route::post('personal-data/bulk/batches/{batchId}/process', [PersonalDataBulkController::class, 'process']);
});

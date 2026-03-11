<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{job}', [JobController::class, 'show']);
    Route::post('/jobs', [JobController::class, 'store']);
    Route::put('/jobs/{job}', [JobController::class, 'update']);
    Route::delete('/jobs/{job}', [JobController::class, 'destroy']);
});

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // time entries
    Route::get('/time-entries', [\App\Http\Controllers\TimeEntryController::class, 'index']);
    Route::post('/time-entries', [\App\Http\Controllers\TimeEntryController::class, 'store']);
    Route::post('/time-entries/{id}/approve', [\App\Http\Controllers\TimeEntryController::class, 'approve']);

    // invoice generation
    Route::post('/invoices/generate', [\App\Http\Controllers\InvoiceController::class, 'generate']);

    // analytics data
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index']);

    // skill management
    Route::get('/skills', [\App\Http\Controllers\SkillController::class, 'index']);
    Route::get('/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'show']);
    Route::post('/skills', [\App\Http\Controllers\SkillController::class, 'store']);
    Route::put('/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'update']);
    Route::delete('/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'destroy']);

    Route::post('/users/{user}/skills', [\App\Http\Controllers\SkillController::class, 'attachToUser']);
    Route::delete('/users/{user}/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'detachFromUser']);

    Route::post('/jobs/{job}/skills', [\App\Http\Controllers\SkillController::class, 'attachToJob']);
    Route::delete('/jobs/{job}/skills/{skill}', [\App\Http\Controllers\SkillController::class, 'detachFromJob']);

    Route::middleware([\App\Http\Middleware\EnsureRole::class . ':admin'])->group(function () {
        Route::get('/availabilities', [\App\Http\Controllers\WorkerAvailabilityController::class, 'index']);
        Route::post('/availabilities', [\App\Http\Controllers\WorkerAvailabilityController::class, 'store']);
        Route::get('/availabilities/{workerAvailability}', [\App\Http\Controllers\WorkerAvailabilityController::class, 'show']);
        Route::put('/availabilities/{workerAvailability}', [\App\Http\Controllers\WorkerAvailabilityController::class, 'update']);
        Route::delete('/availabilities/{workerAvailability}', [\App\Http\Controllers\WorkerAvailabilityController::class, 'destroy']);
    });

    Route::middleware([\App\Http\Middleware\EnsureRole::class . ':admin'])->group(function () {
        Route::get('/assignments', [\App\Http\Controllers\AssignmentController::class, 'index']);
        Route::get('/assignments/{assignment}', [\App\Http\Controllers\AssignmentController::class, 'show']);
        Route::post('/assignments', [\App\Http\Controllers\AssignmentController::class, 'store']);
        Route::put('/assignments/{assignment}', [\App\Http\Controllers\AssignmentController::class, 'update']);
        Route::delete('/assignments/{assignment}', [\App\Http\Controllers\AssignmentController::class, 'destroy']);
    });
});

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FollowUpController;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AdminJobController;

// existing public routes...
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);



// Protected routes (JWT guard configured as auth:api)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Clients (existing)
    Route::get('clients', [ClientController::class,'index']);
    Route::post('clients', [ClientController::class,'store']);
    Route::get('clients/{client}', [ClientController::class,'show']);
    Route::put('clients/{client}', [ClientController::class,'update']);
    Route::delete('clients/{client}', [ClientController::class,'destroy']);
    Route::post('clients/{client}/restore', [ClientController::class,'restore']);

    // Bulk operations
    Route::post('/clients/assign-bulk', [ClientController::class,'assignBulk']);
    Route::post('/clients/restore-bulk', [ClientController::class,'restoreBulk']);

    // Communications (extended)
    Route::get('clients/{client}/communications', [CommunicationController::class, 'index']);
    Route::post('clients/{client}/communications', [CommunicationController::class, 'store']);
    Route::get('clients/{client}/communications/{communication}', [CommunicationController::class, 'show']);
    Route::put('clients/{client}/communications/{communication}', [CommunicationController::class, 'update']);
    Route::delete('clients/{client}/communications/{communication}', [CommunicationController::class, 'destroy']);
    Route::post('clients/{client}/communications/{communication}/restore', [CommunicationController::class, 'restore']);

    // Follow-ups (extended)
    Route::get('follow-ups', [FollowUpController::class,'index']);
    Route::post('follow-ups', [FollowUpController::class,'store']);
    Route::get('follow-ups/{follow_up}', [FollowUpController::class,'show']);
    Route::put('follow-ups/{follow_up}', [FollowUpController::class,'update']);
    Route::delete('follow-ups/{follow_up}', [FollowUpController::class,'destroy']);
    Route::post('follow-ups/{follow_up}/restore', [FollowUpController::class,'restore']);
    Route::post('follow-ups/{follow_up}/complete', [FollowUpController::class,'markComplete']);
    Route::post('follow-ups/{follow_up}/cancel', [FollowUpController::class,'markCancelled']);
   //Route::get('follow-ups/my-tasks', [FollowUpController::class,'myTasks']);

    // Export
    Route::post('/export/clients', [ExportController::class,'exportClients']);
    Route::get('/export/{id}/status', [ExportController::class,'status']);
    Route::get('/export/{id}/download', [ExportController::class,'download']);

    // Notifications
    Route::get('/notifications', [NotificationController::class,'index']);
    Route::post('/notifications/mark-read', [NotificationController::class,'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class,'markAllRead']);

    // Dashboard additional endpoints
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);

    // Settings
    Route::get('/settings', [SettingsController::class,'index']);
    Route::put('/settings', [SettingsController::class,'update']);
    Route::get('/settings/{key}', [SettingsController::class,'show']);
});

// Admin-only routes
Route::middleware(['auth:api','role:admin'])->group(function () {

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class,'index']);
    Route::get('/audit-logs/{id}', [AuditLogController::class,'show']);

    Route::post('/users', [AuthController::class, 'store']); // existing
    Route::get('/users', [UserController::class,'index']);
    Route::get('/users/{user}', [UserController::class,'show']);
    Route::put('/users/{user}', [UserController::class,'update']);
    Route::delete('/users/{user}', [UserController::class,'destroy']);
    Route::post('/users/{user}/roles', [UserController::class,'assignRole']);

    // Admin job trigger
    /*Route::post('/jobs/run-client-status-update', [AdminJobController::class,'runClientStatusUpdate']);
    Route::post('/users', [AuthController::class, 'store']); // existing
    Route::get('/users', [UserController::class,'index']);
    Route::get('/users/{user}', [UserController::class,'show']);
    Route::put('/users/{user}', [UserController::class,'update']);
    Route::delete('/users/{user}', [UserController::class,'destroy']);
    Route::post('/users/{user}/roles', [UserController::class,'assignRole']);
*/
});

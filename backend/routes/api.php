<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\DevFolderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectShareController;
use Illuminate\Support\Facades\Route;

// ── Authentication (Google OAuth) ──────────────────────────────────────────
Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('auth/me', [AuthController::class, 'me'])->middleware('api.auth:optional');
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('api.auth');

// ── Project sharing (requires authentication) ──────────────────────────────
Route::middleware('api.auth')->group(function () {
    Route::get('projects/{project}/shares', [ProjectShareController::class, 'index']);
    Route::post('projects/{project}/shares', [ProjectShareController::class, 'store']);
    Route::put('projects/{project}/shares/{share}', [ProjectShareController::class, 'update']);
    Route::delete('projects/{project}/shares/{share}', [ProjectShareController::class, 'destroy']);
});

// ── Core resources (auth optional — sets owner when logged in) ─────────────
Route::middleware('api.auth:optional')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('bugs', BugController::class);
    Route::get('bugs-summary', [BugController::class, 'summary']);

    // Assign developer & send ticket
    Route::patch('bugs/{bug}/assign', [BugController::class, 'assign']);
    Route::post('bugs/{bug}/send-ticket', [BugController::class, 'sendTicket']);
    Route::post('bugs/{bug}/resend-ticket', [BugController::class, 'resendTicket']);

    // Dev status
    Route::patch('bugs/{bug}/dev-status', [BugController::class, 'updateDevStatus']);

    // Date to accomplish
    Route::patch('bugs/{bug}/date-to-accomplish', [BugController::class, 'updateDateToAccomplish']);

    // Team members list
    Route::get('team-members', [BugController::class, 'teamMembers']);
});

// ── Ticket detail (public — accessible via email link) ────────────────────
Route::get('bugs/{bug}/ticket', [BugController::class, 'ticket']);
Route::post('bugs/{bug}/comments', [BugController::class, 'addComment']);
Route::patch('bugs/{bug}/status', [BugController::class, 'updateStatus']);
Route::post('bugs/{bug}/resolve', [BugController::class, 'resolve']);

// ── Dev Folders (share link feature) ─────────────────────────────────────
// ── Notifications ─────────────────────────────────────────────────────────
Route::get('notifications', [NotificationController::class, 'index']);
Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead']);
Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);

Route::get('dev-folders', [DevFolderController::class, 'index']);
Route::post('dev-folders', [DevFolderController::class, 'store']);
Route::patch('dev-folders/{token}', [DevFolderController::class, 'update']);
Route::delete('dev-folders/{token}', [DevFolderController::class, 'destroy']);
Route::get('dev-folders/{token}/bugs', [DevFolderController::class, 'bugs']);

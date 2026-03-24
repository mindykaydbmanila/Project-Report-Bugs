<?php

use App\Http\Controllers\BugController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::apiResource('projects', ProjectController::class);
Route::apiResource('bugs', BugController::class);
Route::get('bugs-summary', [BugController::class, 'summary']);

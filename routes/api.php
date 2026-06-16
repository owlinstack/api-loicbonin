<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    // Public routes (read-only)
    Route::apiResource('articles', V1\ArticleController::class)->only(['index', 'show']);
    Route::apiResource('categories', V1\CategoryController::class)->only(['index']);
    Route::get('tags', [V1\TagController::class, 'index']);
    Route::apiResource('projects', V1\ProjectController::class)->only(['index', 'show']);
    Route::get('code/tree', [V1\CodeController::class, 'tree']);
    Route::get('code/files/{path}', [V1\CodeController::class, 'show'])->where('path', '.*');
    Route::get('profile', [V1\ProfileController::class, 'show']);
});

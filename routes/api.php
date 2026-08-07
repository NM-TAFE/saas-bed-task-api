<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\TaskController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request): ?Authenticatable {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function (): void {
    Route::apiResource('tasks', TaskController::class);
});

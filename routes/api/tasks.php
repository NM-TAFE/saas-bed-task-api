<?php

declare(strict_types=1);

use App\Http\Api\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/', TaskController::class)
    ->parameters(['' => 'task']);

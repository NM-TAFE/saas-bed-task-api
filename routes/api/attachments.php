<?php

declare(strict_types=1);

use App\Http\Api\Controllers\Attachments\DeleteController;
use App\Http\Api\Controllers\Attachments\ShowController;
use App\Http\Api\Controllers\Attachments\StoreController;
use App\Http\Api\Controllers\Attachments\UpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/', StoreController::class);
Route::get('/{attachment}', ShowController::class);
Route::match(['put', 'patch'], '/{attachment}', UpdateController::class);
Route::delete('/{attachment}', DeleteController::class);

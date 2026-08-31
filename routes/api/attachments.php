<?php

declare(strict_types=1);

use App\Http\Api\Controllers\Attachments\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/', AttachmentController::class)
    ->parameters(['' => 'attachment']);

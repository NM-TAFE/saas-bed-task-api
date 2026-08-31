<?php

declare(strict_types=1);

use App\Http\Api\Controllers\Tasks\DeleteController;
use App\Http\Api\Controllers\Tasks\ShowController;
use App\Http\Api\Controllers\Tasks\StoreController;
use App\Http\Api\Controllers\Tasks\SyncTagsController;
use App\Http\Api\Controllers\Tasks\UpdateController;
use App\Http\Api\Resources\TaskResource;
use App\Http\Api\Responses\PaginatedCollectionResponse;
use App\Models\Task;
use App\Support\Pagination;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $tasks = Pagination::simple(
        Task::query()->with(['project', 'user']),
    )->through(function (Task $task): Task {
        return $task->loadTagsRelation();
    });

    return new PaginatedCollectionResponse(
        data: TaskResource::collection($tasks),
    );
})->name('index');

Route::post('/', StoreController::class)->name('store');
Route::put('/{task}/tags', SyncTagsController::class)->whereUlid('task')->name('tags.sync');
Route::put('/{task}', UpdateController::class)->whereUlid('task')->name('update');
Route::get('/{task}', ShowController::class)->whereUlid('task')->name('show');
Route::delete('/{task}', DeleteController::class)->whereUlid('task')->name('delete');


// Route::get('/projects/{project}', function (string $project) {
//     $tasks = Pagination::simple(
//         Task::query()
//             ->where('project_id', $project)
//             ->with(['project', 'user']),
//     )->through(function (Task $task): Task {
//         return $task->loadTagsRelation();
//     });

//     return new PaginatedCollectionResponse(
//         data: TaskResource::collection($tasks),
//     );
// })->name('project-tasks.index');

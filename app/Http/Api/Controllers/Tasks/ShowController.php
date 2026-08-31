<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Tasks;

use App\Http\Api\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class ShowController
{
    public function __invoke(Task $task): JsonResource
    {
        $task->load(['user']);

        return new TaskResource($task);
    }
}

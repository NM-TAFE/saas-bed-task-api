<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Tasks;

use App\Http\Api\Controllers\Controller;
use App\Http\Api\Requests\Tasks\StoreTaskRequest;
use App\Http\Api\Requests\Tasks\UpdateTaskRequest;
use App\Http\Api\Resources\TaskResource;
use App\Http\Api\Responses\ModelResponse;
use App\Http\Api\Responses\MessageResponse;
use App\Http\Api\Responses\PaginatedCollectionResponse;
use App\Jobs\Tasks\CreateNewTask;
use App\Jobs\Tasks\UpdateTask;
use App\Jobs\Tasks\DeleteTask;
use App\Models\Task;
use Illuminate\Http\Response;

final class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): PaginatedCollectionResponse
    {
        $tasks = Task::query()
            ->with('assignedTo')
            ->latest()
            ->paginate(25);

        return new PaginatedCollectionResponse(
            TaskResource::collection($tasks),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): ModelResponse
    {
        $task = app(CreateNewTask::class, [
            'payload' => $request->payload(),
        ])->handle(app('db'));

        return new ModelResponse(
            data: new TaskResource($task->load('assignedTo')),
            status: Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): ModelResponse
    {
        return new ModelResponse(
            new TaskResource($task->load('assignedTo')),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): ModelResponse
    {
        $task = app(UpdateTask::class, [
            'task' => $task,
            'payload' => $request->payload($task),
        ])->handle(app('db'));

        return new ModelResponse(
            new TaskResource($task->load('assignedTo')),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): MessageResponse
    {
        app(DeleteTask::class, [
            'task' => $task,
        ])->handle(app('db'));

        return new MessageResponse(
            message: 'Task deleted successfully.',
        );
    }
}

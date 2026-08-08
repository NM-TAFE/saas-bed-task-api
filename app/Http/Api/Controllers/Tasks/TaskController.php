<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Tasks;

use App\Http\Api\Controllers\Controller;
use App\Http\Api\Requests\Tasks\StoreTaskRequest;
use App\Http\Api\Requests\Tasks\UpdateTaskRequest;
use App\Jobs\Tasks\CreateNewTask;
use App\Models\Task;

use App\Http\Responses\ModelResponse;

use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $tasks = Task::query()
            ->with('assignedTo')
            ->latest()
            ->paginate(25);

        return TaskResource::collection($tasks);
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
    public function show(Task $task): TaskResource
    {
        return new TaskResource($task->load('assignedTo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        return new TaskResource($task->load('assignedTo'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): Response
    {
        $task->delete();

        return response()->noContent();
    }
}

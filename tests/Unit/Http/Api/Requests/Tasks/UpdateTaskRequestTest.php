<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Api\Requests\Tasks;

use App\Http\Api\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use Tests\TestCase;

final class UpdateTaskRequestTest extends TestCase
{
    public function test_omitted_nullable_fields_retain_the_task_values(): void
    {
        $task = $this->task();

        $payload = $this->request(['name' => 'Renamed task'])->payload($task);

        self::assertSame('Renamed task', $payload->name);
        self::assertSame('Existing description', $payload->description);
        self::assertSame('01-05-2026', $payload->dueDate);
        self::assertSame('existing-user', $payload->user);
    }

    public function test_explicit_null_clears_nullable_fields(): void
    {
        $task = $this->task();

        $payload = $this->request([
            'description' => null,
            'due_date' => null,
            'assigned_to' => null,
        ])->payload($task);

        self::assertNull($payload->description);
        self::assertNull($payload->dueDate);
        self::assertNull($payload->user);
    }

    /** @param array<string, mixed> $data */
    private function request(array $data): UpdateTaskRequest
    {
        $request = UpdateTaskRequest::create('/', 'PUT', $data);
        $request->setContainer($this->app);
        $request->validateResolved();

        return $request;
    }

    private function task(): Task
    {
        return new Task([
            'name' => 'Existing task',
            'description' => 'Existing description',
            'status' => 'todo',
            'due_date' => '2026-05-01',
            'assigned_to' => 'existing-user',
            'tag_ids' => [],
        ]);
    }
}

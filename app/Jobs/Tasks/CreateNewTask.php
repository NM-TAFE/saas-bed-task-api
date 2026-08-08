<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Api\Payloads\NewTask;
use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class CreateNewTask
{
    public function __construct(public NewTask $payload) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): Task
    {
        return $database->transaction(
            callback: fn(): Task => Task::query()->create($this->payload->toArray()),
            attempts: 3,
        );
    }
}

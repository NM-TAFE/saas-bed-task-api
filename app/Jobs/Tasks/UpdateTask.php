<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Api\Payloads\NewTask;
use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class UpdateTask
{
    public function __construct(
        public Task $task,
        public NewTask $payload,
    ) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): Task
    {
        return $database->transaction(
            callback: function (): Task {
                $this->task->update($this->payload->toArray());

                return $this->task->refresh();
            },
            attempts: 3,
        );
    }
}

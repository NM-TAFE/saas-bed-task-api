<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class CreateNewTask
{
    public function __construct(public NewTask $payload) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): Task
    {
        // dd($this->payload);

        return $database->transaction(
            callback: fn(): Task => Task::query()->create($this->payload->toArray()),
            attempts: 3,
        );
    }
}

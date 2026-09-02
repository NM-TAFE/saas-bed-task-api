<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Models\Task;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;

final readonly class DeleteTask
{
    public function __construct(public Task $task) {}

    public function handle(): void
    {

        // TODO What else needs to be deleted

        /** @var Collection<int, Attachment> $attachments */
        $attachments = $this->task->attachments()->get();
        $attachments->each(function (Attachment $attachment): void {
            $attachment->deleteStoredObject();
            $attachment->delete();
        });

        $this->task->delete();
    }
}

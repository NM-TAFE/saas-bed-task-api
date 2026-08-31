<?php

declare(strict_types=1);

namespace App\Jobs\Attachments;

use App\Models\Attachment;

final readonly class DeleteAttachment
{
    public function __construct(public Attachment $attachment) {}

    public function handle(): void
    {
        $this->attachment->delete();
    }
}

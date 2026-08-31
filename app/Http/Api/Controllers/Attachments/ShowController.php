<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Attachments;

use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class ShowController
{
    public function __invoke(Attachment $attachment): JsonResource
    {
        $attachment->load(['attachmentable', 'user']);

        return new AttachmentResource($attachment);
    }
}

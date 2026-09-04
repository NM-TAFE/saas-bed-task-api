<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Attachments;

use App\Http\Api\Resources\AttachmentResource;
use App\Models\Attachment;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class ShowController
{
    public function __invoke(Attachment $attachment): JsonResource
    {
        $attachment->load(['attachmentable', 'uploadedBy']);

        return new AttachmentResource($attachment);
    }
}

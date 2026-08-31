<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Attachment $resource */
final class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'file_path' => $this->resource->file_path,
            'uploaded_by' => new UserResource(resource: $this->whenLoaded(relationship: 'user')),
        ];
    }
}

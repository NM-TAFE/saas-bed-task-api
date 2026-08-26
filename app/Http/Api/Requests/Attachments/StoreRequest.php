<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Attachments;

use App\Http\Payloads\Attachments\NewAttachment;
use App\Models\User;
use App\Support\PolymorphicRelations;
use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file_path' => ['required', 'string'],
            'attachmentable_id' => ['required', 'ulid'],
            'attachmentable_type' => ['required', 'string'],
            'uploaded_by' => ['required', 'ulid'],
            'disk' => ['nullable', 'string', 'max:255'],
            'original_name' => ['nullable', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $attachmentableType = $this->string('attachmentable_type')->toString();
            $attachmentableId = $this->string('attachmentable_id')->toString();
            $attachmentable = PolymorphicRelations::findAttachmentable($attachmentableType, $attachmentableId);
            $uploader = User::query()->find($this->string('uploaded_by')->toString());

            if (null === PolymorphicRelations::classForAttachmentableAlias($attachmentableType)) {
                $validator->errors()->add('attachmentable_type', 'The selected attachmentable type is invalid.');

                return;
            }

            if (null === $attachmentable) {
                $validator->errors()->add('attachmentable_id', 'The selected attachmentable resource is invalid.');

                return;
            }

            if (null === $uploader) {
                $validator->errors()->add('uploaded_by', 'The selected uploaded_by is invalid.');

                return;
            }
        });
    }

    public function payload(): NewAttachment
    {
        return new NewAttachment(
            filePath: $this->string('file_path')->toString(),
            attachmentableId: $this->string('attachmentable_id')->toString(),
            attachmentableType: $this->string('attachmentable_type')->toString(),
            uploadedBy: $this->string('uploaded_by')->toString(),
            disk: $this->string('disk')->toString(),
            originalName: $this->string('original_name')->toString(),
            mimeType: $this->string('mime_type')->toString(),
            size: $this->filled('size') ? $this->integer('size') : null,
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('attachmentable_type')) {
            $resolved = PolymorphicRelations::normaliseAttachmentableType($this->input('attachmentable_type'));

            if (null !== $resolved) {
                $this->merge(['attachmentable_type' => $resolved]);
            }
        }
    }
}

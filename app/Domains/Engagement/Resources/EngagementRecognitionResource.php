<?php

namespace App\Domains\Engagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngagementRecognitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_employee_id,
            'sender_name' => $this->sender ? "{$this->sender->first_name} {$this->sender->last_name}" : null,
            'recipient_id' => $this->recipient_employee_id,
            'recipient_name' => $this->recipient ? "{$this->recipient->first_name} {$this->recipient->last_name}" : null,
            'recognition_type' => $this->recognition_type,
            'value_tag' => $this->value_tag,
            'title' => $this->title,
            'message' => $this->message,
            'visibility' => $this->visibility,
            'status' => $this->status,
            'likes_count' => $this->likes_count,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTimelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'old_status' => $this->old_status,
            'note' => $this->note,
            'actor_type' => $this->actor_type,
            'created_at' => $this->created_at,
        ];
    }
}

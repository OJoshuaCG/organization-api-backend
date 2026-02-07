<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_user_tag_id' => $this->company_user_tag_id,
            'tag' => $this->whenLoaded('companyUserTag', fn () => [
                'id' => $this->companyUserTag->id,
                'name' => $this->companyUserTag->name,
                'color' => $this->companyUserTag->color,
            ]),
            'is_primary' => $this->is_primary,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
        ];
    }
}

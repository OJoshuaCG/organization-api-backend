<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'whmcs_id' => $this->whmcs_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'companies_count' => $this->whenCounted('companies'),
            'users_count' => $this->whenCounted('users'),
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->full_name,
            ]),
            'updated_by' => $this->whenLoaded('updater', fn () => [
                'id' => $this->updater->id,
                'name' => $this->updater->full_name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}

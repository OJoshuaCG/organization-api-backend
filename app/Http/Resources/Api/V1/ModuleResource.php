<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'endpoints' => $this->whenLoaded('moduleEndpoints', fn () => $this->moduleEndpoints->map(fn ($ep) => [
                'id' => $ep->id,
                'route' => $ep->route,
                'http_method' => $ep->http_method,
                'description' => $ep->description,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

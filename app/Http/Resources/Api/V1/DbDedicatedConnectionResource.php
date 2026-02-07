<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DbDedicatedConnectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenancy_mode_group_id' => $this->tenancy_mode_group_id,
            'tenancy_mode_group' => $this->whenLoaded('tenancyModeGroup', fn () => [
                'id' => $this->tenancyModeGroup->id,
                'name' => $this->tenancyModeGroup->name,
            ]),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'db_host' => $this->db_host,
            'db_name' => $this->db_name,
            'db_user' => $this->db_user,
            'db_pass' => '********',
            'db_port' => $this->db_port,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

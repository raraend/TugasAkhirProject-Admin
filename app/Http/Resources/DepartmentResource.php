<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id_departments' => $this->id_departments,
            'name_departments' => $this->name_departments,
            'parent_id' => $this->parent_id,
            'uuid' => $this->uuid,
            'updated_at' => $this->updated_at,
        ];
    }
}

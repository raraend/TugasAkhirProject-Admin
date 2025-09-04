<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitorContentResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->id,
             'content_uuid' => $this->content->uuid ?? null,
            'id_departments' => $this->id_departments,
            'is_visible_to_parent' => $this->is_visible_to_parent,
            'is_tayang_request' => $this->is_tayang_request,
            'updated_at' => $this->updated_at,
        ];
    }
}

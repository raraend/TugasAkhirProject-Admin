<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{

    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'file_server' => $this->file_server,
            'duration' => $this->duration,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'repeat_days' => $this->repeat_days,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,

            // Web 2 bisa unduh file secara langsung
             'file_url' => url('/api/sync-file/' . $this->uuid),

              'monitor_content' => $this->monitorContent,

        ];
    }
}   

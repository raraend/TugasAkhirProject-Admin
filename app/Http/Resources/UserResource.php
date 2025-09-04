<?php

// app/Http/Resources/UserResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name_user,
            'email' => $this->email,
            'id_departments' => $this->id_departments,
            'uuid' => $this->uuid,
            'updated_at' => $this->updated_at,
        ];
    }
}

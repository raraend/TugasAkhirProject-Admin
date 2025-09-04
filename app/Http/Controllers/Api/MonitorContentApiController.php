<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitorContent;
use App\Http\Resources\MonitorContentResource;

class MonitorContentApiController extends Controller
{
    public function index()
    {
        return MonitorContentResource::collection(MonitorContent::all());

         $data = MonitorContent::with('content')->get();

    return response()->json([
        'data' => MonitorContentResource::collection($data)
    ]);
    }

    
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserApiController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::all());
    }
}


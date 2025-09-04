<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Http\Resources\DepartmentResource;

class DepartmentApiController extends Controller
{
    public function index()
    {
        return DepartmentResource::collection(Department::all());
    }
}

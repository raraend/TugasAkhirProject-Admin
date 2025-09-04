<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{

    public function __construct()
    {
        // Middleware untuk memastikan hanya superadmin yang bisa mengakses
        $this->middleware('role:superadmin');
    }
    
    public function index()
    {
        // Menampilkan semua role yang ada di database
        $roles = Role::all();
        
         // Mengembalikan data ke view
         return view('role.index', compact('roles'));
    }
}

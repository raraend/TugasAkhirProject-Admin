<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil DepartmentSeeder
        $this->call(DepartmentSeeder::class);

        // Buat Role
        Log::info('Seeding Roles');
        $superadminRole = Role::create([
            'id_roles' => 'RL01',
            'name_roles' => 'superadmin',
        ]);
        Log::info('Superadmin Role Created', ['role' => $superadminRole]);

        
        $adminRole = Role::create([
            'id_roles' => 'RL02',
            'name_roles' => 'admin',
        ]);
        Log::info('Admin Role Created', ['role' => $adminRole]);

        // Ambil ulang data fakultas teknik dari database
        
        $universitas = Department::where('id_departments', 'DP01')->first();
        $fakultasTeknik = Department::where('id_departments', 'DP02')->first();



        // Tambah user Superadmin
        Log::info('Seeding Users');
        User::create([
            'uuid' => Str::uuid(),
            'name_user' => 'Superadmin UMY',
            'email' => 'Superadmin@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => $superadminRole->id_roles,
            'id_departments' => 'DP00', // superadmin account
        ]);
        Log::info('Superadmin User Created');

        
        User::create([
            'uuid' => Str::uuid(),
            'name_user' => 'Admin Universitas',
            'email' => 'umy@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => $adminRole->id_roles,
            'id_departments' => $universitas->id_departments
        ]);
        Log::info('Admin User Created');

        // Tambah user Admin Fakultas Teknik
        User::create([
            'uuid' => Str::uuid(),
            'name_user' => 'Admin Fakultas Teknik',
            'email' => 'teknik@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => $adminRole->id_roles,
            'id_departments' => $fakultasTeknik->id_departments
        ]);
        Log::info('Admin User Created');
    }
}

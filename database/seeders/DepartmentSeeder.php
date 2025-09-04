<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        // Menambahkan akun Superadmin
        $superadmin_admin = Department::create([
            'id_departments' => 'DP00',
            'uuid' => Str::uuid(),
            'name_departments' => 'Superadmin',
            'parent_id' => null, // superadmin
        ]);

         $universitas = Department::create([
            'id_departments' => 'DP01',
            'uuid' => Str::uuid(),
            'name_departments' => 'Universitas',
            'parent_id' => $superadmin_admin->id_departments,
        ]);


        // Menambahkan Fakultas
        $fakultasTeknik = Department::create([
            'id_departments' => 'DP02',
            'uuid' => Str::uuid(),
            'name_departments' => 'Fakultas Teknik',
            'parent_id' => $universitas->id_departments,
        ]);

        Department::create([
            'id_departments' => 'DP03',
            'uuid' => Str::uuid(),
            'name_departments' => 'Direktorat Digital dan Informasi',
            'parent_id' => $universitas->id_departments,
        ]);
    
        // Menambahkan Prodi (Program Studi) ke Teknik sebagain child
        Department::create([
            'id_departments' => 'DP04',
            'uuid' => Str::uuid(),
            'name_departments' => 'Prodi Teknologi Infromasi',
            'parent_id' => $fakultasTeknik->id_departments, // Prodi Teknologi Informasi di bawah Fakultas Teknik
        ]);

        Department::create([
            'id_departments' => 'DP05',
            'uuid' => Str::uuid(),
            'name_departments' => 'Prodi Teknik Sipil',
            'parent_id' => $fakultasTeknik->id_departments, // Prodi Teknik sipil di bawah Fakultas Teknik
        ]);
    }

        
}

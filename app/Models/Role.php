<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id_roles'; // Primary key kustom
    public $incrementing = false;       // Karena ID berupa string seperti RL01
    protected $keyType = 'string';      // Tipe string untuk ID

    protected $fillable = [
        'id_roles',     // ID peran RL01
        'name_roles',   // Nama peran Admin, Superadmin
    ];

    // Relasi ke User: satu role bisa digunakan banyak user
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id_roles');
    }
}

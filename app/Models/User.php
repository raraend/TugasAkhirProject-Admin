<?php

namespace App\Models;

use App\Models\Content;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $primaryKey = 'id'; // Primary key default Laravel
    public $timestamps = true;    // Mengaktifkan created_at dan updated_at

    // Kolom yang bisa diisi
    protected $fillable = [
        'role_id',        // ID role dari tabel roles (relasi many-to-one)
        'id_departments', // ID departemen asal user (relasi many-to-one)
        'name_user',      // Nama lengkap user
        'email',          // Email user
        'password',       // Password terenkripsi
        'uuid',
    ];

    // Event booting model: set UUID otomatis saat user dibuat
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid(); // UUID Laravel
            }
        });
    }

    // Kolom yang disembunyikan saat model dikonversi ke array/JSON
    protected $hidden = [
        'password',        // Password disembunyikan untuk keamanan
        'remember_token',  // Token default Laravel
    ];

    // Casting atribut ke tipe data tertentu
    protected $casts = [
        'email_verified_at' => 'datetime' // Password akan di-hash otomatis saat diset
    ];

    // Relasi ke Role: satu user hanya punya satu role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_roles');
    }

    // Relasi ke Department: satu user hanya berasal dari satu departemen
    public function department()
    {
        return $this->belongsTo(Department::class, 'id_departments', 'id_departments');
    }

    // Relasi ke Content: satu user bisa membuat banyak konten
    public function contents()
    {
        return $this->hasMany(Content::class, 'created_by', 'id');
    }
}

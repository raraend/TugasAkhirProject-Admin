<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitorContent extends Model
{
    public $timestamps = true;
    protected $table = 'monitor_contents'; // Nama tabel pivot

    protected $fillable = [
        'id_departments', // ID Departemen yang menjadi target penayangan
        'content_id'    ,  // ID konten yang ditayangkan
        'is_visible_to_parent',
        'is_tayang_request' // Indikator apakah konten ini adalah tayangan request
    ];

    // Relasi ke konten yang ditampilkan
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }

    // Relasi ke departemen tempat konten ditayangkan
    public function department()
    {
        return $this->belongsTo(Department::class, 'id_departments', 'id_departments');
    }
}

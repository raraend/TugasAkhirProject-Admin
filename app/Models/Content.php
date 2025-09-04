<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $primaryKey = 'id';    // Primary key default
    protected $keyType = 'int';      // Tipe integer

    protected $fillable = [
        'title',             // Judul konten
        'description',       // Deskripsi singkat
        'file_original',     // Nama file asli
        'file_server',       // Nama file yang disimpan (acak)
        'duration',          // Durasi penayangan (dalam detik atau menit)
        'start_date',        // Tanggal mulai tayang
        'end_date',          // Tanggal akhir tayang
        'start_time',        // Jam mulai tayang
        'end_time',          // Jam akhir tayang
        'repeat_days',       // Hari penayangan (format: 1,2,3,4,5,6,7)
        'created_by',        // ID user yang membuat
        'modified_by'        // ID user terakhir mengubah
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    // Relasi ke pembuat konten
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Relasi ke user yang mengubah konten terakhir
    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    // Relasi many-to-many ke departemen penayangan via tabel pivot
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'monitor_contents',
            'content_id',
            'id_departments'
        )->withPivot('is_visible_to_parent', 'is_tayang_request');
    }
    public function monitorContent()
    {
        return $this->hasMany(\App\Models\MonitorContent::class, 'content_id');
    }

}

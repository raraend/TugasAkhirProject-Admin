<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    protected $primaryKey = 'id_departments';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id_departments',
        'name_departments',
        'parent_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            if (empty($department->uuid)) {
                $department->uuid = (string) Str::uuid();
            }
        });
    }

    // Relasi ke User
    public function users()
    {
        return $this->hasMany(User::class, 'id_departments');
    }

    // Relasi ke Content via pivot monitor_contents
    public function contents()
    {
        return $this->belongsToMany(
            Content::class,
            'monitor_contents',
            'id_departments',
            'content_id'
        )->withPivot('is_visible_to_parent', 'is_tayang_request');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id_departments');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id_departments');
    }

    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }
}

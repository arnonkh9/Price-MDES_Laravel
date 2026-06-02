<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'level', 'is_system', 'position'];

    protected $casts = [
        'is_system' => 'boolean',
        'position'  => 'integer',
    ];

    const LEVELS = ['admin', 'editor', 'viewer'];

    public function menuPermissions(): HasMany
    {
        return $this->hasMany(MenuPermission::class);
    }

    /** จำนวน users ที่ใช้ role นี้ (match by slug) */
    public function usersCount(): int
    {
        return User::where('role', $this->slug)->count();
    }

    /** label ระดับสิทธิ์เป็นภาษาไทย */
    public function levelLabel(): string
    {
        return match ($this->level) {
            'admin'  => 'ผู้ดูแลระบบ',
            'editor' => 'ผู้แก้ไขข้อมูล',
            default  => 'ผู้ดูข้อมูล',
        };
    }
}

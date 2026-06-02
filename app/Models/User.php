<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN  = 'admin';
    const ROLE_EDITOR = 'editor';
    const ROLE_VIEWER = 'viewer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'role',
        'department',
        'password',
    ];

    /** ผู้ดูแลระบบ — เข้าถึงทุกส่วนรวมถึงจัดการ User/Category/Brand */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** ผู้แก้ไขข้อมูล — แก้ไขสินค้า, specs, guidelines, comparisons ได้ */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /** ผู้ดูอย่างเดียว — ดูข้อมูลได้เท่านั้น */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /** admin หรือ editor (หรือ custom role ที่มี level admin/editor) — สามารถเพิ่ม/แก้ไข/ลบข้อมูลได้ */
    public function canEdit(): bool
    {
        if (in_array($this->role, ['admin', 'editor'])) {
            return true;
        }
        if ($this->role === 'viewer') {
            return false;
        }
        // Custom role: ตรวจ level จาก DB
        $roleModel = Role::where('slug', $this->role)->first();
        return $roleModel && in_array($roleModel->level, ['admin', 'editor']);
    }

    /** admin เท่านั้น — จัดการระบบ (User/Category/Brand) */
    public function canManageSystem(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cache ของ permissions ต่อ role: [role_slug][menu_key][column] = bool
     */
    private static array $menuCache = [];

    /**
     * โหลด permissions ของ role นี้จาก DB (cache per role per request)
     * คืน array [menu_key => [can_see, can_add, can_edit, can_delete, can_import, can_export]]
     */
    private function loadMenuPermissions(): array
    {
        if (! array_key_exists($this->role, self::$menuCache)) {
            $roleModel = Role::where('slug', $this->role)->first();
            if (! $roleModel) {
                self::$menuCache[$this->role] = [];
            } else {
                $perms = MenuPermission::where('role_id', $roleModel->id)->get();
                $map = [];
                foreach ($perms as $p) {
                    $map[$p->menu_key] = [
                        'can_see'    => (bool) $p->can_see,
                        'can_add'    => (bool) $p->can_add,
                        'can_edit'   => (bool) $p->can_edit,
                        'can_delete' => (bool) $p->can_delete,
                        'can_import' => (bool) $p->can_import,
                        'can_export' => (bool) $p->can_export,
                    ];
                }
                self::$menuCache[$this->role] = $map;
            }
        }

        return self::$menuCache[$this->role];
    }

    /**
     * ตรวจสิทธิ์แบบ granular ต่อ section และ action
     * action: 'view' | 'add' | 'edit' | 'delete' | 'import' | 'export'
     */
    public function hasPermission(string $section, string $action): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $col = match ($action) {
            'view'   => 'can_see',
            'add'    => 'can_add',
            'edit'   => 'can_edit',
            'delete' => 'can_delete',
            'import' => 'can_import',
            'export' => 'can_export',
            default  => null,
        };

        if ($col === null) {
            return false;
        }

        return (bool) ($this->loadMenuPermissions()[$section][$col] ?? false);
    }

    /**
     * ตรวจว่า role นี้สามารถมองเห็นเมนู (route name) นั้นได้หรือไม่
     */
    public function canSeeMenu(string $routeName): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $cache = $this->loadMenuPermissions();

        // Role ไม่มีใน DB → ไม่มีสิทธิ์
        if (empty($cache)) {
            return false;
        }

        // Route ที่ไม่อยู่ใน DB เลย → ทุก role เห็น (backward compat)
        if (! array_key_exists($routeName, $cache)) {
            return true;
        }

        return (bool) ($cache[$routeName]['can_see'] ?? false);
    }

    /** เคลียร์ cache หลังบันทึกสิทธิ์ใหม่ */
    public static function clearMenuCache(): void
    {
        self::$menuCache = [];
    }

    /** label ภาษาไทยของ role */
    public function roleName(): string
    {
        return match ($this->role) {
            'admin'  => 'ผู้ดูแลระบบ',
            'editor' => 'ผู้แก้ไขข้อมูล',
            'viewer' => 'ผู้ดูข้อมูล',
            default  => Role::where('slug', $this->role)->value('name') ?? $this->role,
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

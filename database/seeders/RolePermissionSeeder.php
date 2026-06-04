<?php

namespace Database\Seeders;

use App\Models\MenuPermission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /** เมนูทั้งหมดที่ควบคุมด้วยสิทธิ์ */
    public const MENU_KEYS = [
        'products',
        'specs',
        'comparisons',
        'compare',
        'guidelines',
        'recommendations',
        'users',
        'categories',
        'brands',
        'roles',
        'permissions',
    ];

    /** sections ที่รองรับ import */
    private const IMPORTABLE = ['products', 'specs'];

    /** sections ที่รองรับ export */
    private const EXPORTABLE = ['products', 'specs', 'comparisons'];

    /**
     * สิทธิ์เริ่มต้นต่อ role และ section
     * full = [view, add, edit, delete, import, export] ตาม section capabilities
     * view_only = เฉพาะ view
     * none = ไม่มีสิทธิ์เลย
     */
    private const DEFAULT_PERMISSIONS = [
        'admin' => [
            // admin ได้ทุกอย่างทุก section (จัดการใน hasPermission โดยตรง)
        ],
        'editor' => [
            'full'      => ['products', 'specs', 'comparisons', 'guidelines', 'recommendations'],
            'view_only' => ['compare'],
            'none'      => ['users', 'categories', 'brands', 'roles', 'permissions'],
        ],
        'viewer' => [
            'view_only' => ['products', 'specs', 'comparisons', 'compare', 'guidelines', 'recommendations'],
            'none'      => ['users', 'categories', 'brands', 'roles', 'permissions'],
        ],
    ];

    public function run(): void
    {
        // 1. Seed system roles
        $systemRoles = [
            ['slug' => 'admin',  'name' => 'ผู้ดูแลระบบ',    'description' => 'เข้าถึงทุกส่วนของระบบ รวมถึงจัดการผู้ใช้ หมวดหมู่ แบรนด์ และสิทธิ์', 'level' => 'admin',  'is_system' => true, 'position' => 1],
            ['slug' => 'editor', 'name' => 'ผู้แก้ไขข้อมูล', 'description' => 'เพิ่ม/แก้ไข/ลบสินค้า คุณลักษณะ แนวทาง ข้อแนะนำ และการเปรียบเทียบ',    'level' => 'editor', 'is_system' => true, 'position' => 2],
            ['slug' => 'viewer', 'name' => 'ผู้ดูข้อมูล',    'description' => 'ดูข้อมูลได้เท่านั้น ไม่สามารถเพิ่ม แก้ไข หรือลบข้อมูลใดๆ ได้',                'level' => 'viewer', 'is_system' => true, 'position' => 3],
        ];

        foreach ($systemRoles as $data) {
            Role::updateOrCreate(['slug' => $data['slug']], $data);
        }

        // 2. Seed default menu permissions for all roles
        $roles = Role::all()->keyBy('slug');

        foreach ($roles as $slug => $role) {
            foreach (self::MENU_KEYS as $menuKey) {
                $attrs = $this->resolvePermissions($slug, $menuKey);
                MenuPermission::updateOrCreate(
                    ['role_id' => $role->id, 'menu_key' => $menuKey],
                    $attrs
                );
            }
        }
    }

    /**
     * คำนวณ permission attributes สำหรับ role+section combination
     */
    private function resolvePermissions(string $roleSlug, string $menuKey): array
    {
        // admin → ทุก column true
        if ($roleSlug === 'admin') {
            return [
                'can_see'    => true,
                'can_add'    => true,
                'can_edit'   => true,
                'can_delete' => true,
                'can_import' => in_array($menuKey, self::IMPORTABLE),
                'can_export' => in_array($menuKey, self::EXPORTABLE),
            ];
        }

        $config = self::DEFAULT_PERMISSIONS[$roleSlug] ?? [];

        // ตรวจว่า section นี้อยู่ใน group ไหน
        $isFull     = in_array($menuKey, $config['full']      ?? []);
        $isViewOnly = in_array($menuKey, $config['view_only'] ?? []);

        if ($isFull) {
            return [
                'can_see'    => true,
                'can_add'    => true,
                'can_edit'   => true,
                'can_delete' => true,
                'can_import' => in_array($menuKey, self::IMPORTABLE),
                'can_export' => in_array($menuKey, self::EXPORTABLE),
            ];
        }

        if ($isViewOnly) {
            return [
                'can_see'    => true,
                'can_add'    => false,
                'can_edit'   => false,
                'can_delete' => false,
                'can_import' => false,
                'can_export' => false,
            ];
        }

        // none
        return [
            'can_see'    => false,
            'can_add'    => false,
            'can_edit'   => false,
            'can_delete' => false,
            'can_import' => false,
            'can_export' => false,
        ];
    }
}

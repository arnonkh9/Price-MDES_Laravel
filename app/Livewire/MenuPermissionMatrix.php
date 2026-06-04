<?php

namespace App\Livewire;

use App\Models\MenuPermission;
use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('จัดการสิทธิ์เมนู | ระบบราคากลาง')]
class MenuPermissionMatrix extends Component
{
    /**
     * $matrix[role_id][menu_key][action] = bool
     * action: can_see | can_add | can_edit | can_delete | can_import | can_export
     */
    public array $matrix = [];

    /** tab ที่กำลังแสดง (role slug) */
    public string $activeTab = '';

    /** เมนูทั้งหมดพร้อม label ภาษาไทย */
    public array $menuKeys = [
        'products'        => 'รายการสินค้า',
        'specs'           => 'คุณลักษณะพื้นฐาน',
        'comparisons'     => 'เปรียบเทียบราคา',
        'compare'         => 'เปรียบเทียบสินค้า',
        'guidelines'      => 'แนวทางการพิจารณา',
        'recommendations' => 'ข้อแนะนำประกอบ',
        'users'           => 'จัดการผู้ใช้',
        'categories'      => 'จัดการหมวดหมู่',
        'brands'          => 'จัดการแบรนด์',
        'roles'           => 'จัดการ Role',
        'permissions'     => 'จัดการสิทธิ์เมนู',
    ];

    /** actions ที่รองรับ (column => Thai label) */
    public array $actions = [
        'can_see'    => 'ดู',
        'can_add'    => 'เพิ่ม',
        'can_edit'   => 'แก้ไข',
        'can_delete' => 'ลบ',
        'can_import' => 'Import',
        'can_export' => 'Export',
    ];

    /** sections ที่รองรับ import/export (ไม่ต้องแสดง checkbox ถ้าไม่รองรับ) */
    private array $importable = ['products', 'specs'];
    private array $exportable = ['products', 'specs', 'comparisons'];

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->loadMatrix();

        // ตั้ง activeTab เป็น role แรกที่ไม่ใช่ admin
        $firstNonAdmin = Role::where('slug', '!=', 'admin')->orderBy('position')->orderBy('id')->first();
        $this->activeTab = $firstNonAdmin?->slug ?? 'editor';
    }

    private function loadMatrix(): void
    {
        $roles    = Role::orderBy('position')->orderBy('id')->get();
        $allPerms = MenuPermission::all()->groupBy('role_id');

        foreach ($roles as $role) {
            $rolePerms = $allPerms->get($role->id, collect());
            foreach (array_keys($this->menuKeys) as $key) {
                $perm = $rolePerms->firstWhere('menu_key', $key);
                foreach (array_keys($this->actions) as $action) {
                    if ($role->slug === 'admin') {
                        $this->matrix[$role->id][$key][$action] = true;
                    } else {
                        $this->matrix[$role->id][$key][$action] = $perm ? (bool) $perm->{$action} : false;
                    }
                }
            }
        }
    }

    public function setTab(string $slug): void
    {
        $this->activeTab = $slug;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $roles = Role::all()->keyBy('id');

        foreach ($this->matrix as $roleId => $menuPerms) {
            $role = $roles->get($roleId);
            if (! $role) {
                continue;
            }

            foreach ($menuPerms as $menuKey => $actions) {
                $data = [];
                foreach (array_keys($this->actions) as $action) {
                    // admin: ล็อค = true เสมอ
                    $data[$action] = ($role->slug === 'admin') ? true : (bool) ($actions[$action] ?? false);
                }

                MenuPermission::updateOrCreate(
                    ['role_id' => $roleId, 'menu_key' => $menuKey],
                    $data
                );
            }
        }

        User::clearMenuCache();
        $this->dispatch('toast', message: 'บันทึกสิทธิ์สำเร็จ');
    }

    public function render()
    {
        return view('livewire.menu-permission-matrix', [
            'roles'      => Role::orderBy('position')->orderBy('id')->get(),
            'importable' => $this->importable,
            'exportable' => $this->exportable,
        ]);
    }
}

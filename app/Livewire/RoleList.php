<?php

namespace App\Livewire;

use App\Models\MenuPermission;
use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('จัดการ Role | ระบบราคากลาง')]
class RoleList extends Component
{
    // Edit state
    public ?int $editingId = null;
    public string $editName = '';
    public string $editDescription = '';
    public string $editLevel = 'viewer';

    // Add new role state
    public bool $showAdd = false;
    public string $newSlug = '';
    public string $newName = '';
    public string $newDescription = '';
    public string $newLevel = 'viewer';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('roles', 'view'), 403);
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('roles', 'edit'), 403);
        $role = Role::find($id);
        if (! $role) {
            return;
        }
        $this->editingId = $id;
        $this->editName = $role->name;
        $this->editDescription = $role->description ?? '';
        $this->editLevel = $role->level;
        $this->showAdd = false;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->hasPermission('roles', 'edit'), 403);

        $role = Role::findOrFail($this->editingId);

        $rules = [
            'editName'        => 'required|string|max:100',
            'editDescription' => 'nullable|string|max:500',
        ];
        // system roles ล็อค level ไว้
        if (! $role->is_system) {
            $rules['editLevel'] = 'required|in:admin,editor,viewer';
        }

        $this->validate($rules, [], [
            'editName'        => 'ชื่อ Role',
            'editDescription' => 'คำอธิบาย',
            'editLevel'       => 'ระดับสิทธิ์',
        ]);

        $data = [
            'name'        => trim($this->editName),
            'description' => $this->editDescription ?: null,
        ];
        if (! $role->is_system) {
            $data['level'] = $this->editLevel;
        }

        $role->update($data);
        $this->editingId = null;
        $this->dispatch('toast', message: 'บันทึก Role สำเร็จ');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    public function toggleAdd(): void
    {
        $this->showAdd = ! $this->showAdd;
        $this->editingId = null;
        $this->resetValidation();
        if ($this->showAdd) {
            $this->newSlug = '';
            $this->newName = '';
            $this->newDescription = '';
            $this->newLevel = 'viewer';
        }
    }

    public function addRole(): void
    {
        abort_unless(auth()->user()->hasPermission('roles', 'add'), 403);

        $this->validate([
            'newSlug'        => 'required|string|max:50|unique:roles,slug|regex:/^[a-z0-9_-]+$/',
            'newName'        => 'required|string|max:100',
            'newDescription' => 'nullable|string|max:500',
            'newLevel'       => 'required|in:admin,editor,viewer',
        ], [
            'newSlug.regex' => 'Slug ต้องเป็นตัวอักษรพิมพ์เล็ก ตัวเลข ขีดกลาง (-) หรือขีดล่าง (_) เท่านั้น',
        ], [
            'newSlug'  => 'Slug',
            'newName'  => 'ชื่อ Role',
            'newLevel' => 'ระดับสิทธิ์',
        ]);

        $role = Role::create([
            'slug'        => trim($this->newSlug),
            'name'        => trim($this->newName),
            'description' => $this->newDescription ?: null,
            'level'       => $this->newLevel,
            'is_system'   => false,
            'position'    => Role::max('position') + 1,
        ]);

        // สร้าง menu_permissions rows ให้ครบทุก menu key (can_see = false)
        foreach (\Database\Seeders\RolePermissionSeeder::MENU_KEYS as $menuKey) {
            MenuPermission::create([
                'role_id'  => $role->id,
                'menu_key' => $menuKey,
                'can_see'  => false,
            ]);
        }

        $this->showAdd = false;
        $this->dispatch('toast', message: 'เพิ่ม Role "' . $role->name . '" สำเร็จ');
    }

    public function deleteRole(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('roles', 'delete'), 403);

        $role = Role::findOrFail($id);

        if ($role->is_system) {
            $this->dispatch('toast', message: 'ไม่สามารถลบ System Role ได้', type: 'warn');
            return;
        }

        if (User::where('role', $role->slug)->exists()) {
            $this->dispatch('toast', message: 'ไม่สามารถลบ Role ที่มีผู้ใช้งานอยู่ได้', type: 'warn');
            return;
        }

        $role->delete();
        $this->dispatch('toast', message: 'ลบ Role สำเร็จ');
    }

    public function render()
    {
        $authUser = auth()->user();
        $roles = Role::orderBy('position')->orderBy('id')->get();

        // นับจำนวน users ต่อ role
        $userCounts = User::selectRaw('role, count(*) as cnt')
            ->groupBy('role')
            ->pluck('cnt', 'role');

        return view('livewire.role-list', [
            'roles'      => $roles,
            'userCounts' => $userCounts,
            'canAdd'     => $authUser->hasPermission('roles', 'add'),
            'canEdit'    => $authUser->hasPermission('roles', 'edit'),
            'canDelete'  => $authUser->hasPermission('roles', 'delete'),
        ]);
    }
}

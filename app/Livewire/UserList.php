<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('จัดการผู้ใช้ | ระบบราคากลาง')]
class UserList extends Component
{
    // Editing state
    public ?int $editingId = null;
    public string $editName = '';
    public string $editUsername = '';
    public string $editEmail = '';
    public string $editRole = 'viewer';
    public string $editDepartment = '';
    public string $editPassword = '';

    // Add new user state
    public bool $showAdd = false;
    public string $newName = '';
    public string $newUsername = '';
    public string $newEmail = '';
    public string $newRole = 'viewer';
    public string $newDepartment = '';
    public string $newPassword = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('users', 'view'), 403);
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('users', 'edit'), 403);
        $user = User::find($id);
        if (! $user) {
            return;
        }
        $this->editingId = $id;
        $this->editName = $user->name;
        $this->editUsername = $user->username;
        $this->editEmail = $user->email ?? '';
        $this->editRole = $user->role;
        $this->editDepartment = $user->department ?? '';
        $this->editPassword = '';
        $this->showAdd = false;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->hasPermission('users', 'edit'), 403);

        $this->validate([
            'editName'     => 'required|string|max:100',
            'editUsername' => 'required|string|max:50|unique:users,username,'.$this->editingId,
            'editEmail'    => 'nullable|email|max:100|unique:users,email,'.$this->editingId,
            'editRole'     => 'required|string|exists:roles,slug',
            'editDepartment' => 'nullable|string|max:200',
            'editPassword' => 'nullable|string|min:8',
        ], [], [
            'editName'     => 'ชื่อ',
            'editUsername' => 'Username',
            'editEmail'    => 'Email',
            'editRole'     => 'Role',
            'editPassword' => 'รหัสผ่าน',
        ]);

        $data = [
            'name'       => trim($this->editName),
            'username'   => trim($this->editUsername),
            'email'      => $this->editEmail ?: null,
            'role'       => $this->editRole,
            'department' => $this->editDepartment ?: null,
        ];

        if ($this->editPassword) {
            $data['password'] = Hash::make($this->editPassword);
        }

        User::where('id', $this->editingId)->update($data);

        $this->editingId = null;
        $this->dispatch('toast', message: 'บันทึกข้อมูลผู้ใช้สำเร็จ');
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
            $this->newName = '';
            $this->newUsername = '';
            $this->newEmail = '';
            $this->newRole = 'viewer';
            $this->newDepartment = '';
            $this->newPassword = '';
        }
    }

    public function addUser(): void
    {
        abort_unless(auth()->user()->hasPermission('users', 'add'), 403);

        $this->validate([
            'newName'       => 'required|string|max:100',
            'newUsername'   => 'required|string|max:50|unique:users,username',
            'newEmail'      => 'nullable|email|max:100|unique:users,email',
            'newRole'       => 'required|string|exists:roles,slug',
            'newDepartment' => 'nullable|string|max:200',
            'newPassword'   => 'required|string|min:8',
        ], [], [
            'newName'     => 'ชื่อ',
            'newUsername' => 'Username',
            'newEmail'    => 'Email',
            'newRole'     => 'Role',
            'newPassword' => 'รหัสผ่าน',
        ]);

        User::create([
            'name'       => trim($this->newName),
            'username'   => trim($this->newUsername),
            'email'      => $this->newEmail ?: null,
            'role'       => $this->newRole,
            'department' => $this->newDepartment ?: null,
            'password'   => Hash::make($this->newPassword),
        ]);

        $this->showAdd = false;
        $this->dispatch('toast', message: 'เพิ่มผู้ใช้ใหม่สำเร็จ');
    }

    public function deleteUser(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('users', 'delete'), 403);

        if ($id === auth()->id()) {
            $this->dispatch('toast', message: 'ไม่สามารถลบบัญชีตัวเองได้');
            return;
        }

        User::destroy($id);
        $this->dispatch('toast', message: 'ลบผู้ใช้สำเร็จ');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.user-list', [
            'users'     => User::orderBy('role')->orderBy('name')->get(),
            'roles'     => Role::orderBy('position')->orderBy('id')->get(),
            'canAdd'    => $user->hasPermission('users', 'add'),
            'canEdit'   => $user->hasPermission('users', 'edit'),
            'canDelete' => $user->hasPermission('users', 'delete'),
        ]);
    }
}

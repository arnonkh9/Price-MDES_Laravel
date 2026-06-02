<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.app')]
#[Title('โปรไฟล์ผู้ใช้ | ระบบราคากลาง')]
class UserProfile extends Component
{
    // Profile edit mode
    public string $editName = '';
    public string $editEmail = '';
    public string $editDepartment = '';
    public bool $editMode = false;

    // Password change
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public bool $showPasswordModal = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->editName = $user->name;
        $this->editEmail = $user->email ?? '';
        $this->editDepartment = $user->department ?? '';

        if (session()->pull('profile_saved')) {
            $this->dispatch('toast', message: 'บันทึกข้อมูลส่วนตัวสำเร็จ');
        }
    }

    public function toggleEditMode(): void
    {
        $this->editMode = !$this->editMode;
        // Reset form when closing edit mode without saving
        if (!$this->editMode) {
            $this->resetEditFields();
        }
    }

    public function resetEditFields(): void
    {
        $user = auth()->user();
        $this->editName = $user->name;
        $this->editEmail = $user->email ?? '';
        $this->editDepartment = $user->department ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate(
            [
                'editName' => 'required|string|max:100',
                'editEmail' => 'nullable|email|max:100|unique:users,email,' . auth()->id(),
                'editDepartment' => 'nullable|string|max:200',
            ],
            [
                'editName.required' => 'ชื่อ จำเป็นต้องกรอก',
                'editName.max' => 'ชื่อ ต้องไม่เกิน 100 ตัวอักษร',
                'editEmail.email' => 'อีเมล ต้องเป็นรูปแบบอีเมลที่ถูกต้อง',
                'editEmail.unique' => 'อีเมล นี้ถูกใช้งานแล้ว',
                'editEmail.max' => 'อีเมล ต้องไม่เกิน 100 ตัวอักษร',
                'editDepartment.max' => 'แผนก ต้องไม่เกิน 200 ตัวอักษร',
            ]
        );

        auth()->user()->update([
            'name' => $this->editName,
            'email' => $this->editEmail ?: null,
            'department' => $this->editDepartment ?: null,
        ]);

        session()->flash('profile_saved', true);
        $this->redirect(route('profile'), navigate: true);
    }

    public function changePassword(): void
    {
        $this->validate(
            [
                'currentPassword' => 'required|current_password',
                'newPassword' => 'required|string|min:8|confirmed',
            ],
            [
                'currentPassword.required' => 'รหัสผ่านปัจจุบัน จำเป็นต้องกรอก',
                'currentPassword.current_password' => 'รหัสผ่านปัจจุบัน ไม่ตรง',
                'newPassword.required' => 'รหัสผ่านใหม่ จำเป็นต้องกรอก',
                'newPassword.min' => 'รหัสผ่านใหม่ ต้องมีอย่างน้อย 8 ตัวอักษร',
                'newPassword.confirmed' => 'รหัสผ่านใหม่ และการยืนยันต้องตรงกัน',
            ]
        );

        auth()->user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->showPasswordModal = false;
        $this->dispatch('toast', message: 'เปลี่ยนรหัสผ่านสำเร็จ');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.user-profile', [
            'user' => $user,
            'roleName' => $user->roleName(),
            'createdDate' => $user->created_at->format('d/m/Y H:i'),
        ]);
    }
}

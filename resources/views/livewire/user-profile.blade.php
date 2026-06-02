<div class="p-6 max-w-3xl mx-auto">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="text-[22px] font-extrabold text-ink">โปรไฟล์ผู้ใช้</h1>
        <p class="text-sm text-faint mt-0.5">จัดการข้อมูลส่วนตัว และเปลี่ยนรหัสผ่าน</p>
    </div>

    {{-- Personal Information Section --}}
    <div class="bg-surface rounded-lg border border-line p-6 mb-6">
        <h2 class="text-sm font-extrabold text-navy mb-5">ข้อมูลส่วนตัว</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-5">
            {{-- Name --}}
            <div>
                <label class="block text-xs font-bold text-ink mb-1.5">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                @if ($editMode)
                    <input type="text" wire:model="editName" placeholder="ชื่อ-นามสกุล"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded text-sm bg-surface outline-none focus:border-navy">
                    @error('editName')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                @else
                    <div class="px-3 py-2 bg-surface-alt rounded text-sm text-ink">{{ $editName }}</div>
                @endif
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-bold text-ink mb-1.5">อีเมล</label>
                @if ($editMode)
                    <input type="email" wire:model="editEmail" placeholder="อีเมล (ไม่บังคับ)"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded text-sm bg-surface outline-none focus:border-navy">
                    @error('editEmail')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                @else
                    <div class="px-3 py-2 bg-surface-alt rounded text-sm text-ink">{{ $editEmail ?: '—' }}</div>
                @endif
            </div>

            {{-- Department --}}
            <div>
                <label class="block text-xs font-bold text-ink mb-1.5">แผนก</label>
                @if ($editMode)
                    <input type="text" wire:model="editDepartment" placeholder="แผนก (ไม่บังคับ)"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded text-sm bg-surface outline-none focus:border-navy">
                    @error('editDepartment')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                @else
                    <div class="px-3 py-2 bg-surface-alt rounded text-sm text-ink">{{ $editDepartment ?: '—' }}</div>
                @endif
            </div>

            {{-- Role (Read-only) --}}
            <div>
                <label class="block text-xs font-bold text-ink mb-1.5">สิทธิ์</label>
                <div class="px-3 py-2 bg-surface-alt rounded text-sm text-ink">{{ $roleName }}</div>
            </div>
        </div>

        {{-- Account Creation Date (Read-only) --}}
        <div class="mb-5">
            <label class="block text-xs font-bold text-ink mb-1.5">สร้างเมื่อ</label>
            <div class="px-3 py-2 bg-surface-alt rounded text-sm text-ink">{{ $createdDate }}</div>
        </div>

        {{-- Edit/Cancel/Save Buttons --}}
        @if (!$editMode)
            <button wire:click="toggleEditMode()"
                class="flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-sm font-bold hover:opacity-90 transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                แก้ไขข้อมูล
            </button>
        @else
            <div class="flex gap-2">
                <button wire:click="saveProfile()"
                    class="flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-sm font-bold hover:opacity-90 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    บันทึก
                </button>
                <button wire:click="toggleEditMode()"
                    class="px-4 py-2 border border-line bg-surface rounded-lg text-sm font-bold text-muted hover:bg-surface-alt transition">
                    ยกเลิก
                </button>
            </div>
        @endif
    </div>

    {{-- Security Section --}}
    <div class="bg-surface rounded-lg border border-line p-6">
        <h2 class="text-sm font-extrabold text-navy mb-4">ความปลอดภัย</h2>

        <button wire:click="$toggle('showPasswordModal')"
            class="flex items-center gap-1.5 px-4 py-2 bg-[#2563EB]/10 text-[#2563EB] rounded-lg text-sm font-bold border border-[#2563EB]/20 hover:bg-[#2563EB]/20 transition">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M15 21H3v-2a6 6 0 0 1 6-6h0a6 6 0 0 1 6 6v2"/>
                <circle cx="10" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="16" y1="11" x2="22" y2="11"/>
            </svg>
            เปลี่ยนรหัสผ่าน
        </button>
    </div>

    {{-- Change Password Modal --}}
    @if ($showPasswordModal)
        <div class="fixed inset-0 bg-black/50 z-[300] flex items-center justify-center p-4">
            <div class="bg-surface rounded-lg border border-line p-5 max-w-sm w-full">
                <h3 class="text-sm font-bold text-navy mb-4">เปลี่ยนรหัสผ่าน</h3>

                {{-- Current Password --}}
                <div class="mb-3">
                    <label class="block text-xs font-bold text-ink mb-1.5">รหัสผ่านปัจจุบัน <span class="text-red-500">*</span></label>
                    <input type="password" wire:model="currentPassword" placeholder="กรอกรหัสผ่านปัจจุบัน"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded text-sm bg-surface outline-none focus:border-navy">
                    @error('currentPassword')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- New Password --}}
                <div class="mb-3">
                    <label class="block text-xs font-bold text-ink mb-1.5">รหัสผ่านใหม่ <span class="text-red-500">*</span></label>
                    <input type="password" wire:model="newPassword" placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded text-sm bg-surface outline-none focus:border-navy">
                    @error('newPassword')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-ink mb-1.5">ยืนยันรหัสผ่านใหม่ <span class="text-red-500">*</span></label>
                    <input type="password" wire:model="newPasswordConfirmation" placeholder="ยืนยันรหัสผ่านใหม่"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded text-sm bg-surface outline-none focus:border-navy">
                    @error('newPasswordConfirmation')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-1.5">
                    <button wire:click="changePassword()"
                        class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-navy text-white rounded-lg text-sm font-bold hover:opacity-90 transition">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        เปลี่ยนรหัส
                    </button>
                    <button wire:click="$toggle('showPasswordModal')"
                        class="flex-1 px-3 py-2 border border-line bg-surface rounded-lg text-sm font-bold text-muted hover:bg-surface-alt transition">
                        ยกเลิก
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

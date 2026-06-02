@php
    $levelColor = [
        'admin'  => ['bg' => '#1E3A5F', 'text' => '#fff'],
        'editor' => ['bg' => '#FEF3C7', 'text' => '#92400E'],
        'viewer' => ['bg' => '#F3F4F6', 'text' => '#6B7280'],
    ];
    $levelLabel = [
        'admin'  => 'ผู้ดูแลระบบ',
        'editor' => 'ผู้แก้ไขข้อมูล',
        'viewer' => 'ผู้ดูข้อมูล',
    ];
@endphp

<div class="p-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-[22px] font-extrabold text-ink">จัดการ Role</h1>
            <p class="text-sm text-faint mt-0.5">เพิ่ม แก้ไข และกำหนดระดับสิทธิ์ของ Role ในระบบ</p>
        </div>
        @if ($canAdd)
        <button wire:click="toggleAdd"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition {{ $showAdd ? 'bg-surface-alt text-muted border border-line' : 'bg-navy text-white' }}">
            @if ($showAdd)
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                ยกเลิก
            @else
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                เพิ่ม Role ใหม่
            @endif
        </button>
        @endif
    </div>

    {{-- Roles Table --}}
    <div class="overflow-x-auto border border-line rounded-xl bg-surface mb-5">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-surface-alt border-b border-line">
                <tr>
                    <th class="px-4 py-3 font-bold text-ink text-left">ชื่อ Role</th>
                    <th class="px-4 py-3 font-bold text-ink text-left">Slug</th>
                    <th class="px-4 py-3 font-bold text-ink text-center">ระดับสิทธิ์</th>
                    <th class="px-4 py-3 font-bold text-ink text-left">คำอธิบาย</th>
                    <th class="px-4 py-3 font-bold text-ink text-center w-20">Users</th>
                    <th class="px-4 py-3 font-bold text-ink text-right w-28">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    @if ($editingId === $role->id)
                        {{-- EDIT MODE --}}
                        <tr class="border-b border-line bg-surface-alt" wire:key="role-edit-{{ $role->id }}">
                            <td class="px-4 py-3" colspan="2">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-[11px] text-muted bg-surface px-2 py-0.5 rounded border border-line">{{ $role->slug }}</span>
                                    @if ($role->is_system)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background:#EFF6FF;color:#1D4ED8">🔒 System</span>
                                    @endif
                                </div>
                                <input type="text" wire:model="editName" placeholder="ชื่อ Role"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy" />
                                @error('editName')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($role->is_system)
                                    @php $lc = $levelColor[$role->level] ?? $levelColor['viewer']; @endphp
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full"
                                          style="background:{{ $lc['bg'] }};color:{{ $lc['text'] }}">
                                        {{ $levelLabel[$role->level] ?? $role->level }}
                                    </span>
                                    <div class="text-[10px] text-faint mt-0.5">ล็อค</div>
                                @else
                                    <select wire:model="editLevel" class="px-2 py-1.5 border-[1.5px] border-line rounded text-[12px] bg-surface outline-none focus:border-navy">
                                        <option value="viewer">ผู้ดูข้อมูล</option>
                                        <option value="editor">ผู้แก้ไขข้อมูล</option>
                                        <option value="admin">ผู้ดูแลระบบ</option>
                                    </select>
                                    @error('editLevel')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                                @endif
                            </td>
                            <td class="px-4 py-3" colspan="2">
                                <input type="text" wire:model="editDescription" placeholder="คำอธิบาย (ไม่บังคับ)"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy" />
                                @error('editDescription')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-1.5 justify-end">
                                    <button wire:click="saveEdit"
                                        class="flex items-center gap-1.5 px-2.5 py-1.5 bg-navy text-white rounded text-xs font-bold">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        บันทึก
                                    </button>
                                    <button wire:click="cancelEdit"
                                        class="px-2.5 py-1.5 border border-line bg-surface rounded text-xs font-semibold text-muted">
                                        ยกเลิก
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @else
                        {{-- DISPLAY MODE --}}
                        <tr class="border-b border-line hover:bg-surface-alt transition" wire:key="role-{{ $role->id }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-ink flex items-center gap-2">
                                    {{ $role->name }}
                                    @if ($role->is_system)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background:#EFF6FF;color:#1D4ED8">🔒 System</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-[12px] text-muted">{{ $role->slug }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $lc = $levelColor[$role->level] ?? $levelColor['viewer']; @endphp
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full"
                                      style="background:{{ $lc['bg'] }};color:{{ $lc['text'] }}">
                                    {{ $levelLabel[$role->level] ?? $role->level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-muted">{{ $role->description ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-bold text-ink">{{ $userCounts[$role->slug] ?? 0 }}</span>
                                <span class="text-[11px] text-faint ml-0.5">คน</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-1.5 justify-end">
                                    @if ($canEdit)
                                        <button wire:click="startEdit({{ $role->id }})"
                                            class="p-1.5 border border-line rounded bg-surface text-[#D97706] hover:border-[#FDE68A] transition" title="แก้ไข">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        @if (! $role->is_system)
                                            <button wire:click="deleteRole({{ $role->id }})"
                                                wire:confirm="ต้องการลบ Role '{{ $role->name }}' ออกจากระบบ?\n(ไม่สามารถลบได้ถ้ามีผู้ใช้งาน Role นี้อยู่)"
                                                class="p-1.5 border border-line rounded bg-surface text-[#DC2626] hover:border-[#FCA5A5] transition" title="ลบ">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            </button>
                                        @else
                                            <div class="p-1.5 w-[29px]"></div>{{-- system role: locked --}}
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-faint">
                            <div class="text-sm">ยังไม่มี Role ในระบบ</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add new role form --}}
    @if ($showAdd)
        <div class="bg-surface-alt border-[1.5px] border-navy/20 rounded-xl p-5">
            <div class="text-[13px] font-extrabold text-ink mb-4">เพิ่ม Role ใหม่</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">Slug * <span class="font-normal text-faint">(ตัวพิมพ์เล็ก ขีดกลาง ขีดล่าง)</span></label>
                    <input type="text" wire:model="newSlug" placeholder="เช่น supervisor, auditor"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy font-mono" />
                    @error('newSlug')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">ชื่อ Role *</label>
                    <input type="text" wire:model="newName" placeholder="เช่น หัวหน้าทีม, ผู้ตรวจสอบ"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                    @error('newName')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">ระดับสิทธิ์ * <span class="font-normal text-faint">(กำหนด canEdit())</span></label>
                    <select wire:model="newLevel"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy">
                        <option value="viewer">ผู้ดูข้อมูล — ดูอย่างเดียว</option>
                        <option value="editor">ผู้แก้ไขข้อมูล — เพิ่ม/แก้ไข/ลบข้อมูล</option>
                        <option value="admin">ผู้ดูแลระบบ — เข้าถึงทุกส่วน</option>
                    </select>
                    @error('newLevel')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">คำอธิบาย</label>
                    <input type="text" wire:model="newDescription" placeholder="อธิบายหน้าที่ของ Role นี้ (ไม่บังคับ)"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                </div>
            </div>

            <div class="mb-4 p-3 bg-surface border border-line rounded-lg text-[12px] text-muted leading-relaxed">
                <span class="font-bold text-ink">หมายเหตุ:</span>
                หลังเพิ่ม Role ใหม่ ให้ไปตั้งค่าสิทธิ์เมนูที่หน้า <a href="{{ route('permissions') }}" wire:navigate class="text-navy underline">จัดการสิทธิ์เมนู</a>
            </div>

            <button wire:click="addRole" class="flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-[13px] font-bold">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                เพิ่ม Role
            </button>
        </div>
    @endif
</div>

@php
    $roleColor = [
        'admin'  => ['bg' => '#1E3A5F', 'text' => '#fff'],
        'editor' => ['bg' => '#FEF3C7', 'text' => '#92400E'],
        'viewer' => ['bg' => '#F3F4F6', 'text' => '#6B7280'],
    ];
    // Build label map from roles collection (passed from Livewire component)
    $roleLabel = $roles->pluck('name', 'slug')->all();
@endphp

<div class="p-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-[22px] font-extrabold text-ink">จัดการผู้ใช้งาน</h1>
            <p class="text-sm text-faint mt-0.5">เพิ่ม แก้ไข และกำหนดสิทธิ์ผู้ใช้ในระบบ</p>
        </div>
        @if ($canAdd)
        <button wire:click="toggleAdd"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition {{ $showAdd ? 'bg-surface-alt text-muted border border-line' : 'bg-navy text-white' }}">
            @if ($showAdd)
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                ยกเลิก
            @else
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                เพิ่มผู้ใช้ใหม่
            @endif
        </button>
        @endif
    </div>

    {{-- Users Table --}}
    <div class="overflow-x-auto border border-line rounded-xl bg-surface mb-5">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-surface-alt border-b border-line">
                <tr>
                    <th class="px-4 py-3 font-bold text-ink text-left">ชื่อ-นามสกุล</th>
                    <th class="px-4 py-3 font-bold text-ink text-left">Username</th>
                    <th class="px-4 py-3 font-bold text-ink text-left">แผนก</th>
                    <th class="px-4 py-3 font-bold text-ink text-center">สิทธิ์</th>
                    <th class="px-4 py-3 font-bold text-ink text-right w-28">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @if ($editingId === $user->id)
                        {{-- EDIT MODE --}}
                        <tr class="border-b border-line bg-surface-alt" wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <input type="text" wire:model="editName" placeholder="ชื่อ-นามสกุล"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy" />
                                @error('editName')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" wire:model="editUsername" placeholder="username"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy" />
                                @error('editUsername')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                                <input type="text" wire:model="editDepartment" placeholder="แผนก (ไม่บังคับ)"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy mt-1" />
                            </td>
                            <td class="px-4 py-3">
                                <input type="email" wire:model="editEmail" placeholder="email (ไม่บังคับ)"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy" />
                                @error('editEmail')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                                <input type="password" wire:model="editPassword" placeholder="รหัสผ่านใหม่ (เว้นว่าง = ไม่เปลี่ยน)"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy mt-1" />
                                @error('editPassword')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                            </td>
                            <td class="px-4 py-3 text-center">
                                <select wire:model="editRole" class="px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy">
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                @error('editRole')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
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
                        <tr class="border-b border-line hover:bg-surface-alt transition" wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-ink">{{ $user->name }}</div>
                                @if ($user->email)
                                    <div class="text-[11px] text-faint mt-0.5">{{ $user->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-[13px] text-muted">{{ $user->username }}</td>
                            <td class="px-4 py-3 text-[13px] text-muted">{{ $user->department ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $rc = $roleColor[$user->role] ?? $roleColor['viewer']; @endphp
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full"
                                    style="background:{{ $rc['bg'] }};color:{{ $rc['text'] }}">
                                    {{ $roleLabel[$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-1.5 justify-end">
                                    @if ($canEdit)
                                        <button wire:click="startEdit({{ $user->id }})"
                                            class="p-1.5 border border-line rounded bg-surface text-[#D97706] hover:border-[#FDE68A] transition" title="แก้ไข">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        @if ($user->id !== auth()->id())
                                            <button wire:click="deleteUser({{ $user->id }})" wire:confirm="ต้องการลบผู้ใช้ '{{ $user->name }}' ออกจากระบบ?"
                                                class="p-1.5 border border-line rounded bg-surface text-[#DC2626] hover:border-[#FCA5A5] transition" title="ลบ">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            </button>
                                        @else
                                            <div class="p-1.5 w-[29px]"></div>{{-- placeholder (self) --}}
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-faint">
                            <svg class="mx-auto mb-3 opacity-30" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <div class="text-sm">ยังไม่มีผู้ใช้ในระบบ</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add new user form --}}
    @if ($showAdd)
        <div class="bg-surface-alt border-[1.5px] border-navy/20 rounded-xl p-5">
            <div class="text-[13px] font-extrabold text-ink mb-4">เพิ่มผู้ใช้ใหม่</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">ชื่อ-นามสกุล *</label>
                    <input type="text" wire:model="newName" placeholder="ชื่อ นามสกุล"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                    @error('newName')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">Username *</label>
                    <input type="text" wire:model="newUsername" placeholder="username"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                    @error('newUsername')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">Email</label>
                    <input type="email" wire:model="newEmail" placeholder="email@example.com"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                    @error('newEmail')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">แผนก</label>
                    <input type="text" wire:model="newDepartment" placeholder="ชื่อแผนก (ไม่บังคับ)"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">รหัสผ่าน *</label>
                    <input type="password" wire:model="newPassword" placeholder="อย่างน้อย 6 ตัวอักษร"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                    @error('newPassword')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-muted mb-1">สิทธิ์การใช้งาน *</label>
                    <select wire:model="newRole"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy">
                        @foreach ($roles as $r)
                            <option value="{{ $r->slug }}">{{ $r->name }} ({{ $r->slug }})</option>
                        @endforeach
                    </select>
                    @error('newRole')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Role description (dynamic from roles table) --}}
            @php $selectedRole = $roles->firstWhere('slug', $newRole); @endphp
            @if ($selectedRole && $selectedRole->description)
                <div class="mb-4 p-3 bg-surface border border-line rounded-lg text-[12px] text-muted leading-relaxed">
                    <span class="font-bold text-ink">{{ $selectedRole->name }}:</span>
                    {{ $selectedRole->description }}
                </div>
            @endif

            <button wire:click="addUser" class="flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-[13px] font-bold">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                เพิ่มผู้ใช้
            </button>
        </div>
    @endif
</div>

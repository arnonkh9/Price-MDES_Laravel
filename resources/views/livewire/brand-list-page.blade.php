<div class="p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-[22px] font-extrabold text-ink">จัดการแบรนด์สินค้า</h1>
        <p class="text-sm text-faint mt-0.5">สร้าง แก้ไข และลบแบรนด์สินค้า</p>
    </div>

    {{-- Brands table --}}
    <div class="overflow-x-auto border border-line rounded-xl bg-surface">
        <table class="w-full min-w-[420px] text-sm">
            <thead class="bg-surface-alt border-b border-line">
                <tr>
                    <th class="px-4 py-3 font-bold text-ink text-left">ชื่อแบรนด์</th>
                    <th class="px-4 py-3 font-bold text-ink text-right w-24">ลำดับที่</th>
                    <th class="px-4 py-3 font-bold text-ink text-right w-24">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    @if ($editingId === $brand->id)
                        {{-- EDIT MODE --}}
                        <tr class="border-b border-line bg-surface-alt" wire:key="brand-{{ $brand->id }}">
                            <td class="px-4 py-3">
                                <input type="text" wire:model="editName" placeholder="ชื่อแบรนด์"
                                    class="w-full px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy" />
                                @error('editName')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="number" wire:model="editPosition" placeholder="0" min="0"
                                    class="w-20 px-2 py-1.5 border-[1.5px] border-line rounded text-[13px] bg-surface outline-none focus:border-navy text-right" />
                                @error('editPosition')<div class="text-xs text-[#DC2626] mt-0.5">{{ $message }}</div>@enderror
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-1.5 justify-end">
                                    <button wire:click="saveEdit" class="flex items-center gap-1.5 px-2.5 py-1.5 bg-navy text-white rounded text-xs font-bold" title="บันทึก">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </button>
                                    <button wire:click="cancelEdit" class="px-2.5 py-1.5 border border-line bg-surface rounded text-xs font-semibold text-muted">ยกเลิก</button>
                                </div>
                            </td>
                        </tr>
                    @else
                        {{-- DISPLAY MODE --}}
                        <tr class="border-b border-line hover:bg-surface-alt transition" wire:key="brand-{{ $brand->id }}">
                            <td class="px-4 py-3 text-ink">{{ $brand->name }}</td>
                            <td class="px-4 py-3 text-right text-muted">{{ $brand->position ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-1.5 justify-end">
                                    @if ($canEdit)
                                        <button wire:click="startEdit({{ $brand->id }})" class="p-1.5 border border-line rounded bg-surface text-[#D97706] hover:border-[#FDE68A] transition" title="แก้ไข">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        <button wire:click="deleteBrand({{ $brand->id }})" wire:confirm="ต้องการลบแบรนด์นี้?" class="p-1.5 border border-line rounded bg-surface text-[#DC2626] hover:border-[#FCA5A5] transition" title="ลบ">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-12 text-center text-faint">
                            <svg class="mx-auto mb-3 opacity-30" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            <div class="text-sm">ยังไม่มีแบรนด์สินค้า</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add new brand form --}}
    @if ($canAdd)
        <div class="mt-6 bg-surface-alt border-[1.5px] border-line rounded-xl p-4">
            <div class="text-[13px] font-extrabold text-ink mb-4">เพิ่มแบรนด์ใหม่</div>
            <div class="grid grid-cols-[1fr_auto] gap-3 mb-3">
                <div>
                    <input type="text" wire:model="newName" placeholder="ชื่อแบรนด์"
                        class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy" />
                    @error('newName')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <input type="number" wire:model="newPosition" placeholder="ลำดับที่" min="0"
                        class="w-24 px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] bg-surface outline-none focus:border-navy text-right" />
                    @error('newPosition')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            <button wire:click="addBrand" class="flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-[13px] font-bold">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                เพิ่มแบรนด์
            </button>
        </div>
    @endif
</div>

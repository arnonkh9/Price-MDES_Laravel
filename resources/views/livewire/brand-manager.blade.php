<div>
@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75">
        <div class="bg-surface rounded-2xl w-[500px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            <div class="px-[26px] py-5 border-b border-line flex justify-between items-start">
                <div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-[3px]">จัดการแบรนด์สินค้า</h2>
                    <p class="text-[13px] text-faint m-0">เพิ่ม แก้ไข และลบแบรนด์</p>
                </div>
                <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto px-[26px] py-5 flex-1">
                {{-- Existing brands --}}
                <div class="flex flex-col gap-2 mb-6">
                    @forelse ($brands as $brand)
                        @if ($editingId === $brand->id)
                            {{-- EDIT MODE --}}
                            <div class="flex items-center gap-3 p-2.5 border-2 border-navy rounded-[10px] bg-surface-alt" wire:key="brand-{{ $brand->id }}">
                                <input wire:model="editName" class="flex-1 px-3 py-1.5 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                <button wire:click="saveEdit" class="p-1.5 border-[1.5px] border-[#059669] rounded-[7px] bg-[#059669] text-white flex" title="บันทึก">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                                <button wire:click="cancelEdit" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-muted flex" title="ยกเลิก">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                            @error('editName')<div class="text-xs text-[#DC2626] -mt-1">{{ $message }}</div>@enderror
                        @else
                            {{-- DISPLAY MODE --}}
                            <div class="flex items-center gap-3 p-2.5 border border-line rounded-[10px]" wire:key="brand-{{ $brand->id }}">
                                <div class="flex-1 text-sm font-semibold text-ink">{{ $brand->name }}</div>
                                <button wire:click="startEdit({{ $brand->id }})" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex" title="แก้ไข">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="deleteBrand({{ $brand->id }})" wire:confirm="ต้องการลบแบรนด์นี้?" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex" title="ลบ">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        @endif
                    @empty
                        <div class="text-center text-faint py-6">ยังไม่มีแบรนด์</div>
                    @endforelse
                </div>

                {{-- Add new --}}
                <div class="bg-surface-alt border-[1.5px] border-line rounded-xl p-4">
                    <div class="text-[13px] font-extrabold text-ink mb-3">เพิ่มแบรนด์ใหม่</div>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <input wire:model="newName" wire:keydown.enter="addBrand" placeholder="เช่น ASUS, HP, Dell" class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                            @error('newName')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button wire:click="addBrand" class="flex items-center gap-1.5 px-4 py-2 border-none bg-navy text-white rounded-lg text-[13px] font-bold shrink-0">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            เพิ่ม
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

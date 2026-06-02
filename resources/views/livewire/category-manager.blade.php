<div>
@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75">
        <div class="bg-surface rounded-2xl w-[620px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            <div class="px-[26px] py-5 border-b border-line flex justify-between items-start">
                <div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-[3px]">จัดการหมวดหมู่สินค้า</h2>
                    <p class="text-[13px] text-faint m-0">เพิ่ม แก้ไข ลบ และเปลี่ยนสีของหมวดหมู่</p>
                </div>
                <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto px-[26px] py-5 flex-1">
                {{-- Existing categories --}}
                <div class="flex flex-col gap-2 mb-6">
                    @foreach ($categories as $cat)
                        @if ($editingSlug === $cat->slug)
                            {{-- EDIT MODE --}}
                            <div class="flex items-center gap-3 p-2.5 border-2 border-navy rounded-[10px] bg-surface-alt" wire:key="cat-{{ $cat->slug }}">
                                <div x-data="{ open:false }" class="relative">
                                    <button @click="open=!open" class="w-7 h-7 rounded-lg border-2 border-navy" style="background:{{ $editColor }}" title="เปลี่ยนสี"></button>
                                    <div x-show="open" x-cloak @click.outside="open=false" class="absolute top-9 left-0 bg-surface-raised border border-line rounded-lg p-2 grid grid-cols-6 gap-1.5 z-10" style="box-shadow:0 8px 24px rgba(0,0,0,0.12)">
                                        @foreach ($palette as $c)
                                            <button wire:click="setColor('{{ $cat->slug }}', @js($c))" @click="open=false" class="w-6 h-6 rounded-md {{ $editColor === $c ? 'ring-2 ring-offset-1 ring-navy' : '' }}" style="background:{{ $c }}"></button>
                                        @endforeach
                                    </div>
                                </div>
                                <input wire:model="editShort" maxlength="6" class="w-[70px] px-2 py-1.5 border-[1.5px] border-line rounded-lg text-xs font-bold text-center uppercase bg-surface">
                                <input wire:model="editLabel" class="flex-1 px-3 py-1.5 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                <button wire:click="saveEdit" class="p-1.5 border-[1.5px] border-[#059669] rounded-[7px] bg-[#059669] text-white flex" title="บันทึก">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                                <button wire:click="cancelEdit" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-muted flex" title="ยกเลิก">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                            @error('editLabel')<div class="text-xs text-[#DC2626] ml-10 -mt-1">{{ $message }}</div>@enderror
                            @error('editShort')<div class="text-xs text-[#DC2626] ml-10 -mt-1">{{ $message }}</div>@enderror
                        @else
                            {{-- DISPLAY MODE --}}
                            <div class="flex items-center gap-3 p-2.5 border border-line rounded-[10px]" wire:key="cat-{{ $cat->slug }}">
                                <div x-data="{ open:false }" class="relative">
                                    <button @click="open=!open" class="w-7 h-7 rounded-lg border border-line" style="background:{{ $cat->color }}" title="เปลี่ยนสี"></button>
                                    <div x-show="open" x-cloak @click.outside="open=false" class="absolute top-9 left-0 bg-surface-raised border border-line rounded-lg p-2 grid grid-cols-6 gap-1.5 z-10" style="box-shadow:0 8px 24px rgba(0,0,0,0.12)">
                                        @foreach ($palette as $c)
                                            <button wire:click="setColor('{{ $cat->slug }}', @js($c))" @click="open=false" class="w-6 h-6 rounded-md" style="background:{{ $c }}"></button>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded text-white" style="background:{{ $cat->color }}">{{ $cat->short }}</span>
                                <div class="flex-1 text-sm font-semibold text-ink">{{ $cat->label }}</div>
                                <span class="text-xs text-faint">{{ $counts[$cat->slug] ?? 0 }} สินค้า</span>
                                <button wire:click="startEdit('{{ $cat->slug }}')" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex" title="แก้ไข">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="deleteCategory('{{ $cat->slug }}')" wire:confirm="ต้องการลบหมวดหมู่นี้?" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex" title="ลบ">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Add new --}}
                <div class="bg-surface-alt border-[1.5px] border-line rounded-xl p-4">
                    <div class="text-[13px] font-extrabold text-ink mb-3">เพิ่มหมวดหมู่ใหม่</div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">ชื่อหมวดหมู่</label>
                            <input wire:model="newLabel" placeholder="เช่น Tablet" class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                            @error('newLabel')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">ชื่อย่อ</label>
                            <input wire:model="newShort" placeholder="เช่น TAB" maxlength="6" class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                            @error('newShort')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-ink mb-1.5">สี</label>
                        <div class="flex gap-1.5 flex-wrap">
                            @foreach ($palette as $c)
                                <button wire:click="$set('newColor', @js($c))" class="w-7 h-7 rounded-md {{ $newColor === $c ? 'ring-2 ring-offset-1 ring-navy' : '' }}" style="background:{{ $c }}"></button>
                            @endforeach
                        </div>
                    </div>
                    <button wire:click="addCategory" class="flex items-center gap-1.5 px-4 py-2 border-none bg-navy text-white rounded-lg text-[13px] font-bold">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        เพิ่มหมวดหมู่
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

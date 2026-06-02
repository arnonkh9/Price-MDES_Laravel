<div class="p-6 max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-[22px] font-extrabold text-ink">ข้อแนะนำประกอบการพิจารณา</h1>
            <p class="text-sm text-faint mt-0.5">ข้อแนะนำและข้อควรพิจารณาเพิ่มเติมประกอบการกำหนดราคากลาง</p>
        </div>
    </div>

    {{-- Items list --}}
    <div class="flex flex-col gap-3 mb-6">
        @forelse ($items as $i => $item)
            @if ($editingId === $item->id)
                {{-- EDIT MODE --}}
                <div class="flex gap-3 items-start p-4 border-2 border-navy rounded-xl bg-surface-alt" wire:key="item-{{ $item->id }}">
                    <span class="shrink-0 w-7 h-7 rounded-full bg-navy text-white text-xs font-extrabold flex items-center justify-center mt-0.5">{{ $i + 1 }}</span>
                    <div class="flex-1">
                        <textarea wire:model="editContent" rows="3"
                            class="w-full px-3 py-2 border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed outline-none focus:border-navy"></textarea>
                        @error('editContent')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                        <div class="flex gap-2 mt-2">
                            <button wire:click="saveEdit" class="flex items-center gap-1.5 px-3 py-1.5 bg-navy text-white rounded-lg text-xs font-bold">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                บันทึก
                            </button>
                            <button wire:click="cancelEdit" class="px-3 py-1.5 border border-line bg-surface rounded-lg text-xs font-semibold text-muted">ยกเลิก</button>
                        </div>
                    </div>
                </div>
            @else
                {{-- DISPLAY MODE --}}
                <div class="flex gap-3 items-start p-4 border border-line rounded-xl bg-surface hover:border-[#CBD5E1] transition" wire:key="item-{{ $item->id }}">
                    <span class="shrink-0 w-7 h-7 rounded-full bg-[#EFF6FF] text-navy text-xs font-extrabold flex items-center justify-center mt-0.5">{{ $i + 1 }}</span>
                    <div class="flex-1 text-[13.5px] text-ink leading-relaxed">{{ $item->content }}</div>
                    @if ($canEdit || $canDelete)
                        <div class="flex gap-1.5 shrink-0">
                            @if ($canEdit)
                                <button wire:click="startEdit({{ $item->id }})" class="p-1.5 border border-line rounded-lg bg-surface text-[#D97706] hover:border-[#FDE68A] transition" title="แก้ไข">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                            @endif
                            @if ($canDelete)
                                <button wire:click="deleteItem({{ $item->id }})" wire:confirm="ต้องการลบข้อนี้?" class="p-1.5 border border-line rounded-lg bg-surface text-[#DC2626] hover:border-[#FCA5A5] transition" title="ลบ">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        @empty
            <div class="text-center py-16 text-faint">
                <svg class="mx-auto mb-3 opacity-30" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <div class="text-sm">ยังไม่มีข้อแนะนำ</div>
            </div>
        @endforelse
    </div>

    {{-- Add new item --}}
    @if ($canAdd)
        <div class="bg-surface-alt border-[1.5px] border-line rounded-xl p-4">
            <div class="text-[13px] font-extrabold text-ink mb-3">เพิ่มข้อใหม่</div>
            <textarea wire:model="newContent" rows="3" placeholder="กรอกข้อแนะนำประกอบการพิจารณา..."
                class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed bg-surface outline-none focus:border-navy mb-2"></textarea>
            @error('newContent')<div class="text-xs text-[#DC2626] mb-2">{{ $message }}</div>@enderror
            <button wire:click="addItem" class="flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-[13px] font-bold">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                เพิ่มข้อใหม่
            </button>
        </div>
    @endif
</div>

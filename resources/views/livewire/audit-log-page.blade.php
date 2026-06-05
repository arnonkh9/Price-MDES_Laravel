@php
    $actionBadge = fn ($a) => match($a) {
        'add'    => ['เพิ่ม',  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'],
        'edit'   => ['แก้ไข', 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'],
        'delete' => ['ลบ',    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'],
        default  => [$a,      'bg-surface-alt text-muted'],
    };
    $typeBadge = fn ($t) => $t === 'product'
        ? ['สินค้า',    'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400']
        : ['คุณลักษณะ', 'bg-purple-50 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400'];
@endphp

<div class="px-4 md:px-7 pt-4 md:pt-7 pb-10">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <div>
            <h2 class="text-[22px] font-extrabold text-ink m-0">ประวัติการแก้ไข</h2>
            <p class="text-[13px] text-faint mt-0.5 m-0">บันทึกกิจกรรมการเพิ่ม/แก้ไข/ลบสินค้าและคุณลักษณะพื้นฐาน</p>
        </div>
    </div>

    {{-- Bulk delete bar (admin only) - MOVED TO TOP FOR PROMINENCE --}}
    @if ($isAdmin && count($selectedKeys) > 0)
        <div class="flex items-center justify-between bg-red-600 dark:bg-red-700 border-2 border-red-700 dark:border-red-600 rounded-[14px] px-5 py-3.5 mb-4 shadow-md">
            <div class="flex items-center gap-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="text-white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11z"/></svg>
                <span class="text-white font-bold">เลือกแล้ว {{ count($selectedKeys) }} รายการ</span>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="$set('selectedKeys', [])"
                    class="flex items-center gap-1 bg-white bg-opacity-20 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-opacity-30 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    ล้าง
                </button>
                <button wire:click="bulkDelete"
                    wire:confirm="ต้องการลบประวัติที่เลือก {{ count($selectedKeys) }} รายการใช่ไหม? การกระทำนี้ไม่สามารถยกเลิกได้"
                    class="flex items-center gap-1.5 bg-white text-red-600 text-xs font-bold px-4 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    ลบที่เลือก ({{ count($selectedKeys) }})
                </button>
            </div>
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="bg-surface border border-line rounded-[14px] px-5 py-4 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-3">

            {{-- Type filter --}}
            <div>
                <label class="block text-[11px] font-semibold text-muted mb-1">ประเภท</label>
                <select wire:model.live="filterType"
                    class="w-full text-sm bg-canvas border border-line rounded-lg px-3 py-2 text-ink focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— ทั้งหมด —</option>
                    <option value="product">สินค้า</option>
                    <option value="spec">คุณลักษณะพื้นฐาน</option>
                </select>
            </div>

            {{-- Action filter --}}
            <div>
                <label class="block text-[11px] font-semibold text-muted mb-1">การดำเนินการ</label>
                <select wire:model.live="filterAction"
                    class="w-full text-sm bg-canvas border border-line rounded-lg px-3 py-2 text-ink focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— ทั้งหมด —</option>
                    <option value="add">เพิ่ม</option>
                    <option value="edit">แก้ไข</option>
                    <option value="delete">ลบ</option>
                </select>
            </div>

            {{-- User filter (admin only) --}}
            @if ($isAdmin)
            <div>
                <label class="block text-[11px] font-semibold text-muted mb-1">ผู้ดำเนินการ</label>
                <input wire:model.live.debounce.400ms="filterUser" type="text" placeholder="ค้นหาชื่อ..."
                    class="w-full text-sm bg-canvas border border-line rounded-lg px-3 py-2 text-ink placeholder:text-muted focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            @endif

            {{-- Date from --}}
            <div>
                <label class="block text-[11px] font-semibold text-muted mb-1">ตั้งแต่วันที่</label>
                <input wire:model.live="filterFrom" type="text" placeholder="2569-01-01"
                    class="w-full text-sm bg-canvas border border-line rounded-lg px-3 py-2 text-ink placeholder:text-muted focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- Date to --}}
            <div>
                <label class="block text-[11px] font-semibold text-muted mb-1">ถึงวันที่</label>
                <input wire:model.live="filterTo" type="text" placeholder="2569-12-31"
                    class="w-full text-sm bg-canvas border border-line rounded-lg px-3 py-2 text-ink placeholder:text-muted focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        {{-- Clear filter --}}
        @if ($filterType || $filterAction || $filterUser || $filterFrom || $filterTo)
            <button wire:click="clearFilters"
                class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                ล้างตัวกรอง
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-surface border border-line rounded-[14px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-alt">
                    <tr>
                        @if ($isAdmin)
                            <th class="px-4 py-3 text-center w-[44px]">
                                <input type="checkbox" wire:click="toggleSelectAll" @checked($allSelected)
                                    class="rounded border-line text-red-600 focus:ring-red-500 cursor-pointer">
                            </th>
                        @endif
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-muted uppercase tracking-wider w-[100px]">วันที่</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-muted uppercase tracking-wider w-[80px]">ประเภท</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-muted uppercase tracking-wider w-[80px]">การดำเนินการ</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-muted uppercase tracking-wider">รายละเอียด</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-muted uppercase tracking-wider w-[120px]">ผู้ดำเนินการ</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-muted uppercase tracking-wider w-[80px]">แหล่งที่มา</th>
                        @if ($isAdmin)
                            <th class="px-4 py-3 text-center text-[11px] font-bold text-muted uppercase tracking-wider w-[60px]">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    @forelse ($logs as $log)
                        @php
                            [$actionText, $actionClass] = $actionBadge($log->action);
                            [$typeText,   $typeClass]   = $typeBadge($log->type);
                        @endphp
                        @php $rowKey = $log->type . ':' . $log->id; @endphp
                        <tr class="hover:bg-surface-alt transition-colors {{ in_array($rowKey, $selectedKeys) ? 'bg-red-50/60 dark:bg-red-950/20' : '' }}">
                            @if ($isAdmin)
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" wire:click="toggleSelectItem('{{ $rowKey }}')" @checked(in_array($rowKey, $selectedKeys))
                                        class="rounded border-line text-red-600 focus:ring-red-500 cursor-pointer">
                                </td>
                            @endif
                            <td class="px-4 py-3 text-[13px] text-muted whitespace-nowrap">{{ $log->date }}</td>
                            <td class="px-4 py-3">
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded {{ $typeClass }}">{{ $typeText }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $actionClass }}">{{ $actionText }}</span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-ink max-w-xs">
                                <span class="line-clamp-2">{{ $log->detail }}</span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-muted">{{ $log->user ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if (! empty($log->url))
                                    <a href="{{ $log->url }}" target="_blank"
                                        class="text-[11px] text-blue-500 hover:underline truncate max-w-[80px] block">
                                        {{ $log->source ?? 'ลิงก์' }}
                                    </a>
                                @elseif (! empty($log->source))
                                    <span class="text-[11px] text-muted">{{ $log->source }}</span>
                                @else
                                    <span class="text-[11px] text-muted">—</span>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="deleteLog('{{ $log->type }}', {{ $log->id }})"
                                        wire:confirm="ต้องการลบประวัตินี้ใช่ไหม? การกระทำนี้ไม่สามารถยกเลิกได้"
                                        title="ลบประวัติ"
                                        class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 hover:border-red-400 transition-colors inline-flex">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 8 : 6 }}" class="px-4 py-16 text-center text-muted">
                                <div class="flex flex-col items-center gap-3">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="opacity-30">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    <span class="text-sm">ไม่พบประวัติการแก้ไข</span>
                                    @if ($filterType || $filterAction || $filterUser || $filterFrom || $filterTo)
                                        <button wire:click="clearFilters" class="text-xs text-blue-500 hover:underline">ล้างตัวกรอง</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($logs->hasPages())
            <div class="px-5 py-3 border-t border-line-soft">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Record count --}}
    <p class="text-xs text-muted mt-3 text-right">
        แสดง {{ $logs->firstItem() }}–{{ $logs->lastItem() }} จากทั้งหมด {{ $logs->total() }} รายการ
    </p>
</div>

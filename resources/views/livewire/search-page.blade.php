<div class="px-4 md:px-7 pt-4 md:pt-7 pb-10 max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-[22px] font-extrabold text-ink mb-5">ค้นหาในระบบ</h2>

        {{-- Search box --}}
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-muted">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <input
                wire:model.live.debounce.350ms="query"
                type="search"
                placeholder="ค้นหา... (พิมพ์อย่างน้อย 2 ตัวอักษร)"
                autofocus
                class="w-full pl-12 pr-5 py-4 text-[15px] bg-surface border border-line rounded-[14px] text-ink
                       placeholder:text-muted focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm"
            >
            <div wire:loading class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                <svg class="animate-spin w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Results --}}
    @if (strlen($query) >= 2)
        @if ($total > 0)
            <p class="text-sm text-muted mb-5">พบ <strong class="text-ink">{{ $total }}</strong> ผลลัพธ์สำหรับ "<strong class="text-ink">{{ $query }}</strong>"</p>

            @foreach ($results as $section)
            <div class="mb-6">
                {{-- Section header --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $section['color'] }}"></span>
                    <a href="{{ route($section['route']) }}" wire:navigate
                       class="text-sm font-extrabold text-ink hover:underline">{{ $section['label'] }}</a>
                    <span class="text-xs text-muted bg-surface-alt px-2 py-0.5 rounded-full">{{ $section['count'] }} รายการ</span>
                </div>

                {{-- Result cards --}}
                <div class="bg-surface border border-line rounded-[12px] overflow-hidden divide-y divide-line-soft">
                    @foreach ($section['items'] as $item)
                    <a href="{{ $item['link'] }}" wire:navigate
                       class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-surface-alt transition-colors">
                        {{-- color dot --}}
                        <div class="w-1.5 h-8 rounded-full shrink-0" style="background:{{ $item['color'] }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-bold text-ink truncate">{{ $item['title'] }}</div>
                            @if ($item['subtitle'])
                                <div class="text-[11px] text-muted mt-0.5">{{ $item['subtitle'] }}</div>
                            @endif
                        </div>
                        @if ($item['meta'])
                            <div class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded"
                                 style="background:{{ $item['color'] }}15;color:{{ $item['color'] }}">
                                {{ $item['meta'] }}
                            </div>
                        @endif
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted shrink-0">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                    @endforeach
                </div>

                {{-- View all link --}}
                <div class="mt-2 text-right">
                    <a href="{{ route($section['route']) }}" wire:navigate class="text-xs text-blue-500 hover:underline">
                        ดูทั้งหมดใน{{ $section['label'] }} →
                    </a>
                </div>
            </div>
            @endforeach

        @else
            {{-- No results --}}
            <div class="flex flex-col items-center justify-center py-20 text-muted">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="opacity-25 mb-4">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p class="text-[15px] font-semibold">ไม่พบผลลัพธ์</p>
                <p class="text-sm mt-1">ลองเปลี่ยนคำค้นหาหรือตรวจสอบการสะกด</p>
            </div>
        @endif

    @elseif (strlen($query) > 0 && strlen($query) < 2)
        <p class="text-sm text-muted text-center mt-8">กรุณาพิมพ์อย่างน้อย 2 ตัวอักษร</p>

    @else
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-16 text-muted">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="opacity-15 mb-5">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <p class="text-base font-semibold">ค้นหาข้ามทุกส่วนของระบบ</p>
            <p class="text-sm mt-2">สินค้า · คุณลักษณะพื้นฐาน · การเปรียบเทียบ · แนวทาง · ข้อแนะนำ</p>
        </div>
    @endif
</div>

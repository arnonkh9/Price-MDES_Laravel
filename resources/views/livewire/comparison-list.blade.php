@php
    use App\Support\Specs;
    $fmt = fn ($n) => $n ? number_format((float) $n).' ฿' : '—';
    $sortIcon = fn ($f) => $sortBy === $f ? ($sortDir === 'asc' ? '↑' : '↓') : '↕';
    $statuses = ['draft' => ['label' => 'ร่าง', 'color' => '#94A3B8', 'bg' => '#F1F5F9'], 'final' => ['label' => 'สรุปแล้ว', 'color' => '#059669', 'bg' => '#F0FFF4']];
@endphp
<div class="p-4 md:p-7">
    {{-- Toolbar --}}
    <div class="flex items-end justify-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="text-[22px] font-extrabold text-ink mb-1">เปรียบเทียบราคาผู้ผลิต</h2>
            <p class="text-muted text-[13px] m-0">{{ $comparisons->count() }} รายการ · {{ $periodLabel }}</p>
        </div>
        <div class="flex items-center gap-2.5">
            {{-- View toggle --}}
            <div class="flex gap-1">
                <button wire:click="$set('viewMode','table')" title="ตาราง"
                        class="p-[7px] border-[1.5px] rounded-[7px] flex {{ $viewMode === 'table' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-faint' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
                <button wire:click="$set('viewMode','card')" title="การ์ด"
                        class="p-[7px] border-[1.5px] rounded-[7px] flex {{ $viewMode === 'card' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-faint' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </button>
            </div>
            @if ($canAdd)
                <button wire:click="$dispatch('open-comparison-form')" class="flex items-center gap-1.5 px-[18px] py-[9px] bg-navy text-white border-none rounded-[9px] text-sm font-bold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    สร้างการเปรียบเทียบใหม่
                </button>
            @endif
        </div>
    </div>

    {{-- Year filter --}}
    <div class="flex items-center gap-1.5 mb-2 flex-wrap">
        <span class="text-xs font-bold text-muted mr-0.5 whitespace-nowrap">ปี พ.ศ.:</span>
        <button wire:click="selectYear('all')" class="px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold {{ $selYear === 'all' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">ทั้งหมด</button>
        @foreach ($availYears as $y)
            <button wire:click="selectYear('{{ $y }}')" class="flex items-center gap-1.5 px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold {{ $selYear === $y ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">
                ปี {{ $y }}<span class="text-[11px] font-bold px-[5px] rounded-lg {{ $selYear === $y ? 'bg-white/25' : 'bg-line-soft' }}">{{ $yearCounts[$y] }}</span>
            </button>
        @endforeach
    </div>
    @if ($selYear !== 'all' && $availMonths->isNotEmpty())
        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
            <span class="text-xs font-bold text-muted mr-0.5 whitespace-nowrap">เดือน:</span>
            <button wire:click="$set('selMonth','all')" class="px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold {{ $selMonth === 'all' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">ทุกเดือน</button>
            @foreach ($availMonths as $m)
                <button wire:click="$set('selMonth','{{ $m }}')" class="px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold {{ $selMonth === $m ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">{{ Specs::monthLabel($m) }}</button>
            @endforeach
        </div>
    @endif

    {{-- Bulk export button --}}
    @if ($canExport && count($selectedIds) > 0)
        <div class="flex items-center gap-2.5 mb-4 p-3.5 bg-[#F0FFF4] border border-[#86EFAC] rounded-xl">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 16 16 12 12 8"/><polyline points="8 12 12 12 12 8"/></svg>
            <span class="text-sm font-semibold text-[#059669]">เลือก {{ count($selectedIds) }} รายการ</span>
            <button wire:click="bulkExportComparisons()"
                class="ml-auto flex items-center gap-1.5 bg-[#059669] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-[#047857]">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><polyline points="14 2 14 8 20 8"/></svg>
                ส่งออก Excel
            </button>
        </div>
    @endif

    {{-- Summary bar --}}
    @if ($all->isNotEmpty())
        <div class="flex gap-3 my-3 mb-5 px-[18px] py-3.5 bg-surface rounded-xl border border-line flex-wrap">
            @foreach ($availYears as $y)
                <button wire:click="selectYear('{{ $y }}')" class="flex flex-col items-center min-w-[70px] px-2.5 py-1 rounded-lg hover:bg-surface-alt">
                    <div class="text-xs text-faint font-semibold mb-0.5">ปี {{ $y }}</div>
                    <div class="text-[22px] font-black text-price leading-none">{{ $yearCounts[$y] }}</div>
                    <div class="text-[11px] text-faint mt-0.5">รายการ</div>
                </button>
            @endforeach
            <div class="flex flex-col items-center min-w-[70px] px-2.5 py-1">
                <div class="text-xs text-faint font-semibold mb-0.5">รวมทั้งหมด</div>
                <div class="text-[22px] font-black text-navy leading-none">{{ $all->count() }}</div>
                <div class="text-[11px] text-faint mt-0.5">รายการ</div>
            </div>
        </div>
    @endif

    {{-- Empty state --}}
    @if ($comparisons->isEmpty())
        <div class="text-center py-20">
            <svg class="mx-auto" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <p class="text-faint mt-3">ยังไม่มีรายการเปรียบเทียบ</p>
        </div>

    {{-- TABLE VIEW --}}
    @elseif ($viewMode === 'table')
        <div class="bg-surface rounded-[14px] border border-line overflow-auto" style="box-shadow:0 2px 8px rgba(0,0,0,0.04)">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-surface-alt">
                        {{-- Checkbox column --}}
                        @if ($canExport)
                            <th class="px-3.5 py-[11px] w-10 text-center border-b border-line">
                                <input type="checkbox" wire:click="toggleSelectAll()" @checked($allSelected)
                                    style="cursor:pointer">
                            </th>
                        @endif
                        {{-- ชื่อเปรียบเทียบ (sortable) --}}
                        <th wire:click="sort('name')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none min-w-[240px]">
                            ชื่อเปรียบเทียบ
                            <span class="{{ $sortBy === 'name' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('name') }}</span>
                        </th>
                        {{-- ประเภท (sortable) --}}
                        <th wire:click="sort('category')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            ประเภท
                            <span class="{{ $sortBy === 'category' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('category') }}</span>
                        </th>
                        {{-- สถานะ (sortable) --}}
                        <th wire:click="sort('status')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            สถานะ
                            <span class="{{ $sortBy === 'status' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('status') }}</span>
                        </th>
                        {{-- ปี / เดือน (sortable) --}}
                        <th wire:click="sort('year')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            ปี / เดือน
                            <span class="{{ $sortBy === 'year' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('year') }}</span>
                        </th>
                        {{-- ผู้ขาย & ราคา (no sort) --}}
                        <th class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line whitespace-nowrap min-w-[280px]">
                            ผู้ขาย & ราคา
                        </th>
                        {{-- ราคาต่ำสุด (sortable) --}}
                        <th wire:click="sort('min_price')"
                            class="px-3.5 py-[11px] text-right text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            ราคาต่ำสุด
                            <span class="{{ $sortBy === 'min_price' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('min_price') }}</span>
                        </th>
                        {{-- จัดการ --}}
                        <th class="px-3.5 py-[11px] text-center text-xs font-bold text-muted border-b border-line w-[130px]">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comparisons as $cmp)
                        @php
                            $color = $colors[$cmp->category] ?? '#64748B';
                            $st = $statuses[$cmp->status] ?? $statuses['draft'];
                            $linkSpec = $specs[$cmp->characteristics_template_id] ?? null;
                            $prices = $cmp->vendors->map(fn ($v) => (float) $v->price)->filter(fn ($p) => $p > 0);
                            $minPrice = $prices->min();
                            $m = $getMonth($cmp);
                        @endphp
                        <tr class="border-b border-line-soft hover:bg-surface-alt" wire:key="row-{{ $cmp->id }}">
                            {{-- Checkbox --}}
                            @if ($canExport)
                                <td class="px-3.5 py-[11px] text-center">
                                    <input type="checkbox"
                                        wire:click="toggleSelectItem('{{ $cmp->id }}')"
                                        @if(in_array($cmp->id, $selectedIds)) checked @endif
                                        style="cursor:pointer">
                                </td>
                            @endif
                            {{-- ชื่อเปรียบเทียบ --}}
                            <td class="px-3.5 py-[11px]">
                                <button wire:click="$dispatch('open-comparison-detail', { id: '{{ $cmp->id }}' })"
                                        class="font-bold cursor-pointer text-navy text-left hover:underline max-w-[300px] overflow-hidden text-ellipsis whitespace-nowrap block"
                                        title="{{ $cmp->name }}">{{ $cmp->name }}</button>
                                @if ($linkSpec)
                                    <div class="text-[11px] text-[#7C3AED] bg-[#F5F3FF] px-2 py-0.5 rounded inline-block mt-0.5">สเปคอ้างอิง: {{ $linkSpec->name }}</div>
                                @endif
                            </td>
                            {{-- ประเภท --}}
                            <td class="px-3.5 py-[11px]">
                                <span class="px-2 py-[3px] rounded-[5px] text-[11px] font-bold whitespace-nowrap text-white" style="background:{{ $color }}">{{ Specs::label($cmp->category) }}</span>
                            </td>
                            {{-- สถานะ --}}
                            <td class="px-3.5 py-[11px]">
                                <span class="text-[11px] font-bold px-2 py-[3px] rounded-[10px] whitespace-nowrap" style="background:{{ $st['bg'] }};color:{{ $st['color'] }}">{{ $st['label'] }}</span>
                            </td>
                            {{-- ปี / เดือน --}}
                            <td class="px-3.5 py-[11px] whitespace-nowrap text-[13px] text-ink">
                                {{ $m ? Specs::monthLabel($m).' ' : '' }}ปี {{ $getYear($cmp) }}
                            </td>
                            {{-- ผู้ขาย & ราคา --}}
                            <td class="px-3.5 py-[11px]">
                                <div class="flex flex-col gap-1">
                                    @foreach ($cmp->vendors as $i => $v)
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-[16px] h-[16px] rounded-full bg-line-soft flex items-center justify-center text-[10px] font-extrabold text-muted shrink-0">{{ $i + 1 }}</span>
                                            <span class="text-[11px] text-ink font-semibold flex-1 overflow-hidden text-ellipsis whitespace-nowrap max-w-[140px]">{{ $v->name ?: '—' }}</span>
                                            <span class="text-[12px] font-extrabold text-ink shrink-0">{{ $v->price ? number_format((float) $v->price).' ฿' : '—' }}</span>
                                            @if ((float) $v->price > 0 && (float) $v->price === (float) $minPrice)
                                                <span class="text-[9px] font-extrabold text-price bg-[#F0FFF4] px-1 py-px rounded shrink-0">ต่ำสุด</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            {{-- ราคาต่ำสุด --}}
                            <td class="px-3.5 py-[11px] text-right font-extrabold text-price text-sm whitespace-nowrap">
                                {{ $minPrice ? $fmt($minPrice) : '—' }}
                            </td>
                            {{-- จัดการ --}}
                            <td class="px-3.5 py-[11px]">
                                <div class="flex gap-[5px] justify-center">
                                    {{-- View --}}
                                    <button wire:click="$dispatch('open-comparison-detail', { id: '{{ $cmp->id }}' })"
                                            title="ดูรายงาน"
                                            class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#2563EB] flex hover:bg-[#2563EB12]">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    {{-- Excel export --}}
                                    @if ($canExport)
                                        <a href="{{ route('comparisons.export', $cmp->id) }}"
                                           title="ส่งออก Excel"
                                           class="p-1.5 border-none rounded-[7px] text-white flex hover:opacity-80"
                                           style="background:{{ $color }}">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </a>
                                    @endif
                                    @if ($canEdit)
                                        {{-- Edit --}}
                                        <button wire:click="$dispatch('open-comparison-form', { id: '{{ $cmp->id }}' })"
                                                title="แก้ไข"
                                                class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex hover:bg-[#D9770612]">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        {{-- Delete --}}
                                        <button wire:click="deleteComparison('{{ $cmp->id }}')"
                                                wire:confirm="ต้องการลบการเปรียบเทียบนี้?"
                                                title="ลบ"
                                                class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex hover:bg-[#DC262612]">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    {{-- CARD VIEW --}}
    @else
        <div class="grid gap-4 mt-4" style="grid-template-columns:repeat(auto-fill,minmax(340px,1fr))">
            @foreach ($comparisons as $cmp)
                @php
                    $color = $colors[$cmp->category] ?? '#64748B';
                    $st = $statuses[$cmp->status] ?? $statuses['draft'];
                    $linkSpec = $specs[$cmp->characteristics_template_id] ?? null;
                    $prices = $cmp->vendors->map(fn ($v) => (float) $v->price)->filter(fn ($p) => $p > 0);
                    $minPrice = $prices->min();
                @endphp
                <div class="bg-surface rounded-[14px] border border-line overflow-hidden flex flex-col hover:shadow-lg transition relative" wire:key="cmp-{{ $cmp->id }}">
                    {{-- Card checkbox --}}
                    @if ($canExport)
                        <div class="absolute top-3 right-3 z-10">
                            <input type="checkbox"
                                wire:click="toggleSelectItem('{{ $cmp->id }}')"
                                @if(in_array($cmp->id, $selectedIds)) checked @endif
                                style="cursor:pointer">
                        </div>
                    @endif
                    <div class="px-4 py-3 flex justify-between items-center" style="background:{{ $color }}">
                        <span class="text-white text-xs font-extrabold">{{ Specs::label($cmp->category) }}</span>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-[10px]" style="background:{{ $st['bg'] }};color:{{ $st['color'] }}">{{ $st['label'] }}</span>
                    </div>
                    <div class="p-4 flex-1">
                        <div class="text-[15px] font-extrabold text-ink mb-1 leading-tight">{{ $cmp->name }}</div>
                        @if ($linkSpec)<div class="text-[11px] text-[#7C3AED] bg-[#F5F3FF] px-2 py-0.5 rounded inline-block mb-2.5">สเปคอ้างอิง: {{ $linkSpec->name }}</div>@endif
                        <div class="flex flex-col gap-1.5 mb-2.5">
                            @foreach ($cmp->vendors as $i => $v)
                                <div class="flex items-center gap-2">
                                    <span class="w-[18px] h-[18px] rounded-full bg-line-soft flex items-center justify-center text-[11px] font-extrabold text-muted shrink-0">{{ $i + 1 }}</span>
                                    <span class="text-xs text-ink font-semibold flex-1 overflow-hidden text-ellipsis whitespace-nowrap">{{ $v->name }}</span>
                                    <span class="text-[13px] font-extrabold text-ink shrink-0">{{ $v->price ? number_format((float) $v->price).' ฿' : '—' }}</span>
                                    @if ((float) $v->price === (float) $minPrice && $minPrice > 0)<span class="text-[10px] font-extrabold text-price bg-[#F0FFF4] px-1.5 py-px rounded shrink-0">ต่ำสุด</span>@endif
                                </div>
                            @endforeach
                        </div>
                        <div class="flex gap-3 flex-wrap">
                            <span class="text-[11px] text-faint">{{ $cmp->year }}{{ $cmp->month ? '/'.$cmp->month : '' }}</span>
                            <span class="text-[11px] text-faint">สร้างโดย {{ $cmp->created_by }}</span>
                        </div>
                    </div>
                    <div class="px-4 py-3 border-t border-line-soft flex gap-[7px] items-center">
                        <button wire:click="$dispatch('open-comparison-detail', { id: '{{ $cmp->id }}' })" class="px-3 py-1.5 border-[1.5px] border-line bg-surface text-ink rounded-[7px] text-xs font-semibold">ดูรายงาน</button>
                        @if ($canExport)
                            <a href="{{ route('comparisons.export', $cmp->id) }}" class="flex items-center gap-1.5 px-3 py-1.5 border-none text-white rounded-[7px] text-xs font-bold" style="background:{{ $color }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Excel
                            </a>
                        @endif
                        @if ($canEdit || $canDelete)
                            <div class="flex gap-[5px] ml-auto">
                                @if ($canEdit)
                                    <button wire:click="$dispatch('open-comparison-form', { id: '{{ $cmp->id }}' })" title="แก้ไข" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                @endif
                                @if ($canDelete)
                                    <button wire:click="deleteComparison('{{ $cmp->id }}')" wire:confirm="ต้องการลบการเปรียบเทียบนี้?" title="ลบ" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <livewire:comparison-detail />
    @if ($canAdd || $canEdit)<livewire:comparison-form />@endif
</div>

@php
    use App\Support\Specs;
    $fmt = fn ($n) => $n ? number_format((float) $n).' ฿' : '—';
    $fieldCount = fn ($s) => collect($s->specs ?? [])->filter()->count();
    $sortIcon = fn ($f) => $sortBy === $f ? ($sortDir === 'asc' ? '↑' : '↓') : '↕';
@endphp
<div class="p-4 md:p-7">
    {{-- Toolbar --}}
    <div class="flex items-start justify-between mb-4 gap-4 flex-wrap">
        <div>
            <h2 class="text-[22px] font-extrabold text-ink mb-1">คุณลักษณะพื้นฐาน</h2>
            <p class="text-muted text-[13px] m-0">{{ $specs->count() }} รายการ · {{ $periodLabel }}</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- Search input --}}
            <div class="flex items-center gap-[9px] bg-surface-alt border-[1.5px] border-line rounded-[10px] px-3 min-w-[210px]">
                <svg class="shrink-0 text-faint" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="search"
                       placeholder="ค้นหาชื่อ TOR..."
                       class="border-none bg-transparent outline-none text-[13px] py-[7px] text-ink w-full min-w-0">
                @if ($search)
                    <button wire:click="$set('search','')" class="shrink-0 text-faint hover:text-ink">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                @endif
            </div>
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
            {{-- Import & Create buttons --}}
            @if ($canImport)
                <button wire:click="$dispatch('open-specs-import')" class="flex items-center gap-1.5 px-[18px] py-[9px] bg-surface border-[1.5px] border-line text-ink rounded-[9px] text-sm font-bold hover:bg-surface-alt">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    นำเข้าคุณลักษณะ
                </button>
            @endif
            @if ($canAdd)
                <button wire:click="$dispatch('open-characteristics-form')" class="flex items-center gap-1.5 px-[18px] py-[9px] bg-navy text-white border-none rounded-[9px] text-sm font-bold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    สร้างคุณลักษณะพื้นฐานใหม่
                </button>
            @endif
        </div>
    </div>

    {{-- ── Filter rows ─────────────────────────────────────────────────────── --}}

    {{-- Year filter --}}
    <div class="flex items-center gap-1.5 mb-2 flex-wrap">
        <span class="text-xs font-bold text-muted mr-0.5 whitespace-nowrap">ปี พ.ศ.:</span>
        <button wire:click="selectYear('all')"
                class="flex items-center gap-1.5 px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold
                       {{ $selYear === 'all' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">
            ทั้งหมด
        </button>
        @foreach ($availYears as $y)
            <button wire:click="selectYear('{{ $y }}')"
                    class="flex items-center gap-1.5 px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold
                           {{ $selYear === $y ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">
                ปี {{ $y }}
                <span class="text-[11px] font-bold px-[5px] rounded-lg {{ $selYear === $y ? 'bg-white/25' : 'bg-line-soft' }}">{{ $yearCounts[$y] }}</span>
            </button>
        @endforeach
    </div>

    {{-- Month filter — independent (always visible when months exist) --}}
    @if ($availMonths->isNotEmpty())
        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
            <span class="text-xs font-bold text-muted mr-0.5 whitespace-nowrap">เดือน:</span>
            <button wire:click="$set('selMonth','all')"
                    class="px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold
                           {{ $selMonth === 'all' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">
                ทุกเดือน
            </button>
            @foreach ($availMonths as $m)
                <button wire:click="$set('selMonth','{{ $m }}')"
                        class="px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold
                               {{ $selMonth === $m ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">
                    {{ Specs::monthLabel($m) }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Category filter (multi-select) --}}
    @if ($availCategories->isNotEmpty())
        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
            <span class="text-xs font-bold text-muted mr-0.5 whitespace-nowrap">ประเภท:</span>
            <button wire:click="$set('selCategories',[])"
                    class="px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold
                           {{ empty($selCategories) ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted' }}">
                ทั้งหมด
            </button>
            @foreach ($availCategories as $cat)
                @php
                    $catActive = in_array($cat, $selCategories);
                    $catColor  = $colors[$cat] ?? '#64748B';
                    $catLabel  = Specs::label($cat);
                @endphp
                <button wire:click="toggleCategory('{{ $cat }}')"
                        class="flex items-center gap-1 px-3 py-[5px] border-[1.5px] rounded-full text-[13px] font-semibold transition-all"
                        style="{{ $catActive ? "background:{$catColor}; border-color:{$catColor}; color:white" : '' }}"
                        @if (!$catActive) class="bg-surface border-line text-muted" @endif>
                    {{ $catLabel }}
                    @if ($catActive)
                        <svg class="ml-0.5 opacity-80" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    {{-- Active filters summary + clear button --}}
    @if ($hasActiveFilters)
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-[11px] text-faint font-semibold">กรองอยู่:</span>
            @if ($search)
                <span class="flex items-center gap-1 text-[11px] px-2 py-0.5 bg-navy/10 text-navy rounded-full font-semibold">
                    ค้นหา: "{{ $search }}"
                    <button wire:click="$set('search','')" class="hover:opacity-70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </span>
            @endif
            @if ($selYear !== 'all')
                <span class="flex items-center gap-1 text-[11px] px-2 py-0.5 bg-navy/10 text-navy rounded-full font-semibold">
                    ปี {{ $selYear }}
                    <button wire:click="selectYear('all')" class="hover:opacity-70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </span>
            @endif
            @if ($selMonth !== 'all')
                <span class="flex items-center gap-1 text-[11px] px-2 py-0.5 bg-navy/10 text-navy rounded-full font-semibold">
                    {{ Specs::monthLabel($selMonth) }}
                    <button wire:click="$set('selMonth','all')" class="hover:opacity-70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </span>
            @endif
            @foreach ($selCategories as $cat)
                @php $catColor = $colors[$cat] ?? '#64748B'; @endphp
                <span class="flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full font-semibold text-white"
                      style="background: {{ $catColor }}">
                    {{ Specs::label($cat) }}
                    <button wire:click="toggleCategory('{{ $cat }}')" class="hover:opacity-70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </span>
            @endforeach
            <button wire:click="clearFilters()"
                    class="ml-auto text-[11px] text-muted underline hover:text-ink">
                ล้างทั้งหมด
            </button>
        </div>
    @endif

    {{-- Bulk delete + export buttons --}}
    @if ((($canDelete || $canExport) && count($selectedIds) > 0))
        <div class="flex items-center gap-2.5 mb-4 p-3.5 bg-[#F0FFF4] border border-[#86EFAC] rounded-xl">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 16 16 12 12 8"/><polyline points="8 12 12 12 12 8"/></svg>
            <span class="text-sm font-semibold text-[#059669]">เลือก {{ count($selectedIds) }} รายการ</span>
            <div class="ml-auto flex items-center gap-2">
                {{-- Delete button --}}
                @if ($canDelete)
                    <button wire:click="bulkDeleteCharacteristics()"
                        wire:confirm="ต้องการลบคุณลักษณะพื้นฐานที่เลือก {{ count($selectedIds) }} รายการ ใช่ไหม? การกระทำนี้ไม่สามารถยกเลิกได้"
                        class="flex items-center gap-1.5 bg-[#FEF2F2] text-[#DC2626] text-xs font-bold px-3 py-1.5 rounded-lg border border-[#DC262630] hover:bg-[#DC262620] dark:hover:bg-red-950/40 transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                        ลบ ({{ count($selectedIds) }})
                    </button>
                @endif
                {{-- Export button --}}
                @if ($canExport)
                    <button wire:click="bulkExportCharacteristics()"
                        class="flex items-center gap-1.5 bg-[#059669] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-[#047857]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><polyline points="14 2 14 8 20 8"/></svg>
                        ส่งออก Excel
                    </button>
                @endif
            </div>
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
    @if ($specs->isEmpty())
        <div class="text-center py-20">
            <svg class="mx-auto" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <p class="text-faint mt-3">ยังไม่มีคุณลักษณะพื้นฐาน</p>
        </div>

    {{-- TABLE VIEW --}}
    @elseif ($viewMode === 'table')
        <div class="bg-surface rounded-[14px] border border-line overflow-auto" style="box-shadow:0 2px 8px rgba(0,0,0,0.04)">
            {{-- Mobile scroll hint --}}
            <div class="text-[11px] text-faint text-center md:hidden py-1 px-2 border-b border-line-soft">← เลื่อนเพื่อดูเพิ่มเติม →</div>
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
                        {{-- Sortable: ชื่อ --}}
                        <th wire:click="sort('name')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none min-w-[120px] md:min-w-[280px]">
                            ชื่อ / TOR
                            <span class="{{ $sortBy === 'name' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('name') }}</span>
                        </th>
                        {{-- Sortable: ประเภท --}}
                        <th wire:click="sort('category')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            ประเภท
                            <span class="{{ $sortBy === 'category' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('category') }}</span>
                        </th>
                        {{-- Sortable: ปี/เดือน --}}
                        <th wire:click="sort('year')"
                            class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            ปี / เดือน
                            <span class="{{ $sortBy === 'year' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('year') }}</span>
                        </th>
                        {{-- Sortable: วงเงิน --}}
                        <th wire:click="sort('budget')"
                            class="px-3.5 py-[11px] text-right text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            วงเงิน
                            <span class="{{ $sortBy === 'budget' ? 'text-navy' : 'opacity-25' }}">{{ $sortIcon('budget') }}</span>
                        </th>
                        {{-- No sort: ข้อกำหนด --}}
                        <th class="px-3.5 py-[11px] text-center text-xs font-bold text-muted border-b border-line whitespace-nowrap">
                            ข้อกำหนด
                        </th>
                        {{-- Actions --}}
                        <th class="px-3.5 py-[11px] text-center text-xs font-bold text-muted border-b border-line w-[130px]">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($specs as $s)
                        @php $color = $colors[$s->category] ?? '#64748B'; $m = $getMonth($s); @endphp
                        <tr class="border-b border-line-soft hover:bg-surface-alt" wire:key="row-{{ $s->id }}">
                            {{-- Checkbox --}}
                            @if ($canExport)
                                <td class="px-3.5 py-[11px] text-center">
                                    <input type="checkbox"
                                        wire:click="toggleSelectItem('{{ $s->id }}')"
                                        @if(in_array($s->id, $selectedIds)) checked @endif
                                        style="cursor:pointer">
                                </td>
                            @endif
                            {{-- ชื่อ / TOR --}}
                            <td class="px-3.5 py-[11px]">
                                <button wire:click="$dispatch('open-characteristics-detail', { id: '{{ $s->id }}' })"
                                        class="font-bold cursor-pointer text-navy text-left hover:underline max-w-[340px] overflow-hidden text-ellipsis whitespace-nowrap block"
                                        title="{{ $s->name }}">{{ $s->name }}</button>
                                @if ($s->purpose)
                                    <div class="text-[11px] text-faint mt-0.5 overflow-hidden text-ellipsis whitespace-nowrap max-w-[340px]">{{ $s->purpose }}</div>
                                @endif
                            </td>
                            {{-- ประเภท --}}
                            <td class="px-3.5 py-[11px]">
                                <span class="px-2 py-[3px] rounded-[5px] text-[11px] font-bold whitespace-nowrap text-white" style="background:{{ $color }}">{{ Specs::label($s->category) }}</span>
                            </td>
                            {{-- ปี / เดือน --}}
                            <td class="px-3.5 py-[11px] whitespace-nowrap text-[13px] text-ink">
                                {{ $m ? Specs::monthLabel($m).' ' : '' }}ปี {{ $getYear($s) }}
                            </td>
                            {{-- วงเงิน --}}
                            <td class="px-3.5 py-[11px] text-right font-extrabold text-price text-sm whitespace-nowrap">
                                {{ $fmt($s->budget) }}
                            </td>
                            {{-- ข้อกำหนด --}}
                            <td class="px-3.5 py-[11px] text-center">
                                <span class="text-[13px] font-semibold text-ink">{{ $fieldCount($s) }}</span>
                                <span class="text-[11px] text-faint ml-0.5">รายการ</span>
                            </td>
                            {{-- Actions --}}
                            <td class="px-3.5 py-[11px]">
                                <div class="flex gap-[5px] justify-center">
                                    {{-- View --}}
                                    <button wire:click="$dispatch('open-characteristics-detail', { id: '{{ $s->id }}' })"
                                            title="ดูรายละเอียด"
                                            class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#2563EB] flex hover:bg-[#2563EB12]">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    {{-- Use compare --}}
                                    <button wire:click="useCompare('{{ $s->id }}')"
                                            title="ใช้เปรียบเทียบ"
                                            class="p-1.5 border-none rounded-[7px] text-white flex hover:opacity-80"
                                            style="background:{{ $color }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                    </button>
                                    {{-- Export --}}
                                    @if ($canExport)
                                        <a href="{{ route('specs.export', $s->id) }}"
                                           title="ส่งออก Excel"
                                           class="p-1.5 border-none rounded-[7px] text-white flex hover:opacity-80"
                                           style="background:#2563EB">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        </a>
                                        <a href="{{ route('specs.export.pdf', $s->id) }}"
                                           title="ส่งออก PDF"
                                           class="p-1.5 border-none rounded-[7px] text-white flex hover:opacity-80"
                                           style="background:#DC2626">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
                                        </a>
                                    @endif
                                    @if ($canEdit)
                                        {{-- Edit --}}
                                        <button wire:click="$dispatch('open-characteristics-form', { id: '{{ $s->id }}' })"
                                                title="แก้ไข"
                                                class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex hover:bg-[#D9770612]">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        {{-- Delete --}}
                                        <button wire:click="deleteCharacteristics('{{ $s->id }}')"
                                                wire:confirm="ต้องการลบคุณลักษณะพื้นฐานนี้?"
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
        <div class="grid gap-4" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr))">
            @foreach ($specs as $s)
                @php $color = $colors[$s->category] ?? '#64748B'; $m = $getMonth($s); @endphp
                <div class="bg-surface rounded-[14px] border border-line overflow-hidden flex flex-col hover:shadow-lg transition relative" wire:key="characteristics-{{ $s->id }}">
                    {{-- Card checkbox --}}
                    @if ($canExport)
                        <div class="absolute top-3 left-0  z-10">
                            <input type="checkbox"
                                wire:click="toggleSelectItem('{{ $s->id }}')"
                                @if(in_array($s->id, $selectedIds)) checked @endif
                                style="cursor:pointer">
                        </div>

                    @endif
                    <div class="px-4 py-3 flex justify-between items-center" style="background:{{ $color }}">
                        <span class="text-white text-xs font-extrabold">{{ Specs::label($s->category) }}</span>
                        <span class="text-white/85 text-[11px] font-semibold bg-white/[0.18] px-2 py-0.5 rounded-[10px]">{{ $fieldCount($s) }} ข้อกำหนด</span>
                    </div>
                    <div class="p-4 flex-1">
                        <div class="text-sm font-bold text-ink mb-1 leading-snug">{{ $s->name }}</div>
                        @if ($s->purpose)<div class="text-xs text-muted leading-snug mb-1.5 line-clamp-2">{{ $s->purpose }}</div>@endif
                        <div class="flex gap-3 mb-3 flex-wrap">
                            <span class="flex items-center gap-1.5 text-xs text-faint">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $m ? Specs::monthLabel($m).' ' : '' }}ปี {{ $getYear($s) }}
                            </span>
                            @if ((float) $s->budget > 0)
                                <span class="flex items-center gap-1 text-xs text-faint">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    วงเงิน {{ $fmt($s->budget) }}
                                </span>
                            @endif
                        </div>
                        <div class="bg-surface-alt rounded-lg px-3.5 py-2.5 flex flex-col gap-1.5">
                            @foreach (collect($s->specs ?? [])->take(3) as $k => $v)
                                <div class="flex gap-2 items-start">
                                    <span class="text-[11px] font-bold text-muted min-w-[60px] shrink-0">{{ $k }}</span>
                                    <span class="text-[11px] text-ink leading-relaxed">{{ \Illuminate\Support\Str::limit((string) $v, 50) }}</span>
                                </div>
                            @endforeach
                            @if ($fieldCount($s) > 3)<div class="text-[11px] text-faint mt-0.5">+ อีก {{ $fieldCount($s) - 3 }} ข้อกำหนด</div>@endif
                        </div>
                    </div>
                    <div class="px-4 py-3 border-t border-line-soft flex gap-2.5 items-center">

                        <button wire:click="$dispatch('open-characteristics-detail', { id: '{{ $s->id }}' })"
                                            title="ดูรายละเอียด"
                                            class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#2563EB] flex hover:bg-[#2563EB12]">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                                   
                        <button wire:click="useCompare('{{ $s->id }}')"
                                            title="ใช้เปรียบเทียบ"
                                            class="p-1.5 border-none rounded-[7px] text-white flex hover:opacity-80"
                                            style="background:{{ $color }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        </button>

                        @if ($canExport)

                            <a href="{{ route('specs.export', $s->id) }}" class="flex items-center gap-1.5 px-3 py-1.5 border-none text-white rounded-[7px] text-xs font-bold" style="background:#2563EB">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Excel
                            </a>
                            <a href="{{ route('specs.export.pdf', $s->id) }}" class="flex items-center gap-1.5 px-3 py-1.5 border-none text-white rounded-[7px] text-xs font-bold" style="background:#DC2626">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><polyline points="14 2 14 8 20 8"/></svg>
                                PDF
                            </a>
                        @endif
                        @if ($canEdit || $canDelete)
                            <div class="flex gap-[5px] ml-auto">
                                @if ($canEdit)
                                    <button wire:click="$dispatch('open-characteristics-form', { id: '{{ $s->id }}' })" title="แก้ไข" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                @endif
                                @if ($canDelete)
                                    <button wire:click="deleteCharacteristics('{{ $s->id }}')" wire:confirm="ต้องการลบคุณลักษณะพื้นฐานนี้?" title="ลบ" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex">
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

    <livewire:characteristics-detail />
    @if ($canAdd || $canEdit)<livewire:characteristics-form />@endif
    @if ($canImport)<livewire:specs-import-modal />@endif
</div>

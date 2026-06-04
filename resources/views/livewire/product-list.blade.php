@php
    use App\Support\Specs;
    $fmt = fn ($n) => $n ? number_format((float) $n).' ฿' : '—';
@endphp
<div class="p-4 md:p-7">
    {{-- Toolbar --}}
    <div class="flex items-end justify-between mb-[18px]">
        <div>
            <h2 class="text-[22px] font-extrabold text-ink mb-1">{{ $catLabel }}</h2>
            <p class="text-muted text-[13px] m-0 flex items-center gap-2">
                {{ $products->count() }} รายการ
                @if ($search)
                    <span class="bg-[#EFF6FF] text-[#2563EB] px-2 py-0.5 rounded text-xs font-semibold">ค้นหา: "{{ $search }}"</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            @if ($canCompare && count($compareIds) > 0)
                <a href="{{ route('compare') }}" wire:navigate class="flex items-center gap-1.5 bg-[#EFF6FF] text-[#2563EB] text-xs font-bold px-3 py-1.5 rounded-lg">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    เลือกเปรียบเทียบ {{ count($compareIds) }}/3 รายการ
                </a>
            @endif
            @if ($canDelete && count($selectedIds) > 0)
                <button wire:click="bulkDelete()"
                    wire:confirm="ต้องการลบสินค้าที่เลือก {{ count($selectedIds) }} รายการ ใช่ไหม? การกระทำนี้ไม่สามารถยกเลิกได้"
                    class="flex items-center gap-1.5 bg-[#FEF2F2] text-[#DC2626] text-xs font-bold px-3 py-1.5 rounded-lg border border-[#DC262630] hover:bg-[#DC262620] dark:hover:bg-red-950/40 transition-colors">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    ลบที่เลือก ({{ count($selectedIds) }} รายการ)
                </button>
            @endif
            <div class="flex gap-1">
                <button wire:click="$set('viewMode','table')" title="ตาราง"
                        class="p-2 md:p-[7px] border-[1.5px] rounded-[7px] flex {{ $viewMode === 'table' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-faint' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
                <button wire:click="$set('viewMode','card')" title="การ์ด"
                        class="p-2 md:p-[7px] border-[1.5px] rounded-[7px] flex {{ $viewMode === 'card' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-faint' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ── --}}
    <div class="flex flex-wrap gap-2.5 mb-4 items-end">
        {{-- Category dropdown --}}
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-bold text-muted">หมวดหมู่</label>
            <select wire:model.live="category" class="px-3 py-[7px] border-[1.5px] border-line rounded-lg text-sm bg-surface min-w-[130px]">
                <option value="all">— ทุกหมวดหมู่ —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->label }}</option>
                @endforeach
            </select>
        </div>
        {{-- Brand dropdown --}}
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-bold text-muted">แบรนด์</label>
            <select wire:model.live="filterBrand" class="px-3 py-[7px] border-[1.5px] border-line rounded-lg text-sm bg-surface min-w-[130px]">
                <option value="">— ทุกแบรนด์ —</option>
                @foreach ($availableBrands as $b)
                    <option value="{{ $b }}">{{ $b }}</option>
                @endforeach
            </select>
        </div>
        {{-- Year dropdown --}}
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-bold text-muted">ปี (พ.ศ.)</label>
            <select wire:model.live="filterYear" class="px-3 py-[7px] border-[1.5px] border-line rounded-lg text-sm bg-surface min-w-[110px]">
                <option value="">— ทุกปี —</option>
                @foreach ($availableYears as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        {{-- Model text filter --}}
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-bold text-muted">โมเดล</label>
            <input wire:model.live.debounce.300ms="filterModel" type="text"
                   placeholder="พิมพ์ชื่อรุ่น..." class="px-3 py-[7px] border-[1.5px] border-line rounded-lg text-sm min-w-[150px]">
        </div>
        {{-- Sort by price --}}
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-bold text-muted">เรียงราคา</label>
            <div class="flex gap-1">
                <button wire:click="sortPriceDir('asc')"
                    class="flex items-center gap-1 px-3 py-[7px] border-[1.5px] rounded-lg text-sm font-semibold whitespace-nowrap transition-colors
                           {{ $sortBy === 'price' && $sortDir === 'asc' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted hover:border-navy hover:text-navy' }}">
                    ราคา ↑
                </button>
                <button wire:click="sortPriceDir('desc')"
                    class="flex items-center gap-1 px-3 py-[7px] border-[1.5px] rounded-lg text-sm font-semibold whitespace-nowrap transition-colors
                           {{ $sortBy === 'price' && $sortDir === 'desc' ? 'bg-navy border-navy text-white' : 'bg-surface border-line text-muted hover:border-navy hover:text-navy' }}">
                    ราคา ↓
                </button>
            </div>
        </div>
        {{-- Clear filters (visible only when any filter is active) --}}
        @if ($hasFilters)
            <div class="flex flex-col gap-1 justify-end">
                <button wire:click="resetFilters"
                    class="px-3 py-[7px] border-[1.5px] border-[#DC2626] text-[#DC2626] bg-surface rounded-lg text-sm font-semibold whitespace-nowrap hover:bg-[#FEF2F2] dark:hover:bg-red-950/40 transition-colors">
                    ✕ ล้างตัวกรอง
                </button>
            </div>
        @endif
    </div>

    @if ($products->isEmpty())
        <div class="text-center py-20">
            <svg class="mx-auto" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <p class="text-faint mt-3 text-[15px]">ไม่พบรายการที่ค้นหา</p>
        </div>
    @elseif ($viewMode === 'table')
        <div class="bg-surface rounded-[14px] border border-line overflow-auto" style="box-shadow:0 2px 8px rgba(0,0,0,0.04)">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-surface-alt">
                        <th class="px-3.5 py-[11px] w-9 text-center border-b border-line">
                            @if ($canDelete)
                                <input type="checkbox" wire:click="toggleSelectAll()" @checked($allSelected)
                                    style="accent-color:#1B3A6B;cursor:pointer" title="เลือก/ยกเลิกทั้งหมด">
                            @endif
                        </th>
                        @foreach (['brand' => 'แบรนด์', 'model' => 'รุ่น / โมเดล', 'category' => 'ประเภท'] as $f => $lbl)
                            <th wire:click="sort('{{ $f }}')" class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none {{ $f === 'model' ? 'min-w-[100px] md:min-w-[240px]' : '' }}">
                                {{ $lbl }}
                                <span class="{{ $sortBy === $f ? 'text-navy' : 'opacity-25' }}">{{ $sortBy === $f ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}</span>
                            </th>
                        @endforeach
                        <th class="px-3.5 py-[11px] text-left text-xs font-bold text-muted border-b border-line min-w-[100px] md:min-w-[200px]">สเปคหลัก</th>
                        <th wire:click="sort('price')" class="px-3.5 py-[11px] text-right text-xs font-bold text-muted border-b border-line cursor-pointer whitespace-nowrap select-none">
                            ราคากลาง <span class="{{ $sortBy === 'price' ? 'text-navy' : 'opacity-25' }}">{{ $sortBy === 'price' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </th>
                        <th class="px-3.5 py-[11px] text-center text-xs font-bold text-muted border-b border-line w-[100px]">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $p)
                        @php $color = $colors[$p->category] ?? '#64748B'; $inCompare = in_array($p->id, $compareIds, true); @endphp
                        <tr class="border-b border-line-soft hover:bg-surface-alt" wire:key="row-{{ $p->id }}">
                            <td class="px-3.5 py-[11px] text-center">
                                @if ($canDelete)
                                    <input type="checkbox" wire:model="selectedIds" value="{{ $p->id }}"
                                        style="accent-color:#1B3A6B;cursor:pointer">
                                @endif
                            </td>
                            <td class="px-3.5 py-[11px]">
                                <span class="px-[9px] py-[3px] rounded-md font-bold text-xs whitespace-nowrap" style="background:{{ $color }}18;color:{{ $color }}">{{ $p->brand }}</span>
                            </td>
                            <td class="px-3.5 py-[11px]">
                                <button wire:click="$dispatch('open-product-detail', { id: '{{ $p->id }}' })" class="font-bold cursor-pointer text-navy max-w-[100px] md:max-w-[260px] overflow-hidden text-ellipsis whitespace-nowrap block text-left" title="{{ $p->model }}">{{ $p->model }}</button>
                            </td>
                            <td class="px-3.5 py-[11px]">
                                <span class="px-2 py-[3px] rounded-[5px] text-[11px] font-bold whitespace-nowrap text-white" style="background:{{ $color }}">{{ Specs::label($p->category) }}</span>
                            </td>
                            <td class="px-3.5 py-[11px]">
                                <div class="text-xs text-muted overflow-hidden text-ellipsis whitespace-nowrap max-w-[220px]">{{ \Illuminate\Support\Str::limit(trim(explode('(', $p->specs['Processor'] ?? '—')[0]), 38, '') ?: '—' }}</div>
                                <div class="text-[11px] text-faint overflow-hidden text-ellipsis whitespace-nowrap max-w-[220px] mt-0.5">{{ $p->specs['Main Memory'] ?? $p->specs['Storage'] ?? '' }}</div>
                            </td>
                            <td class="px-3.5 py-[11px] text-right">
                                <div class="font-extrabold text-price text-sm">{{ $fmt($p->price) }}</div>
                                <div class="text-[11px] text-faint mt-0.5">{{ $p->price_date }}</div>
                            </td>
                            <td class="px-3.5 py-[11px]">
                                <div class="flex gap-[5px] justify-center">
                                    {{-- Compare button --}}
                                    @if ($canCompare)
                                    <button wire:click="toggleCompare('{{ $p->id }}')"
                                        title="{{ $inCompare ? 'ยกเลิกเปรียบเทียบ' : 'เพิ่มเปรียบเทียบ' }}"
                                        class="p-2 md:p-1.5 border-[1.5px] rounded-[7px] flex transition-colors
                                               {{ $inCompare ? 'bg-[#EFF6FF] border-[#2563EB] text-[#2563EB]' : 'bg-surface border-line text-faint hover:text-[#2563EB] hover:border-[#2563EB]' }}"
                                        @disabled(! $inCompare && count($compareIds) >= 3)>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                    </button>
                                    @endif
                                    {{-- View button --}}
                                    <button wire:click="$dispatch('open-product-detail', { id: '{{ $p->id }}' })" title="ดูรายละเอียด" class="p-2 md:p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#2563EB] flex hover:bg-[#2563EB12]">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    @if ($canEdit)
                                        <button wire:click="$dispatch('open-product-form', { id: '{{ $p->id }}' })" title="แก้ไข" class="p-2 md:p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex hover:bg-[#D9770612]">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        <button wire:click="deleteProduct('{{ $p->id }}')" wire:confirm="ต้องการลบสินค้านี้ใช่ไหม?" title="ลบ" class="p-2 md:p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex hover:bg-[#DC262612]">
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
    @else
        <div class="grid gap-4" style="grid-template-columns:repeat(auto-fill,minmax(270px,1fr))">
            @foreach ($products as $p)
                @php $color = $colors[$p->category] ?? '#64748B'; $inCompare = in_array($p->id, $compareIds, true); @endphp
                <div class="bg-surface rounded-[14px] border border-line overflow-hidden cursor-pointer hover:shadow-lg transition" style="box-shadow:0 2px 6px rgba(0,0,0,0.04)" wire:key="card-{{ $p->id }}">
                    <div class="px-3.5 py-3 flex justify-between items-center" style="background:{{ $color }}">
                        <span class="text-white text-xs font-bold">{{ Specs::label($p->category) }}</span>
                        @if ($canDelete)
                            <input type="checkbox" wire:model="selectedIds" value="{{ $p->id }}"
                                style="accent-color:white;cursor:pointer">
                        @endif
                    </div>
                    <div class="px-4 py-3.5" wire:click="$dispatch('open-product-detail', { id: '{{ $p->id }}' })">
                        <div class="text-xs text-faint mb-[3px]">{{ $p->brand }}</div>
                        <div class="text-sm font-extrabold text-ink mb-2.5 leading-tight line-clamp-2">{{ $p->model }}</div>
                        <div class="flex flex-col gap-[3px] mb-3">
                            <div class="text-xs text-muted overflow-hidden text-ellipsis whitespace-nowrap">{{ \Illuminate\Support\Str::limit(trim(explode('(', $p->specs['Processor'] ?? '—')[0]), 44, '') ?: '—' }}</div>
                            <div class="text-xs text-muted overflow-hidden text-ellipsis whitespace-nowrap">{{ $p->specs['Main Memory'] ?? '—' }}</div>
                            <div class="text-xs text-muted overflow-hidden text-ellipsis whitespace-nowrap">{{ $p->specs['Storage'] ?? '—' }}</div>
                        </div>
                        <div class="flex justify-between items-end border-t border-line-soft pt-2.5">
                            <div>
                                <div class="text-[11px] text-faint mb-0.5">ราคากลาง</div>
                                <div class="text-base font-extrabold text-price">{{ $fmt($p->price) }}</div>
                            </div>
                            <div class="flex gap-[5px]" wire:click.stop>
                                {{-- Compare button --}}
                                @if ($canCompare)
                                <button wire:click="toggleCompare('{{ $p->id }}')"
                                    title="{{ $inCompare ? 'ยกเลิกเปรียบเทียบ' : 'เพิ่มเปรียบเทียบ' }}"
                                    class="p-1.5 border-[1.5px] rounded-[7px] bg-surface flex transition-colors
                                           {{ $inCompare ? 'border-[#2563EB] text-[#2563EB]' : 'border-line text-faint hover:text-[#2563EB]' }}"
                                    @disabled(!$inCompare && count($compareIds) >= 3)>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                </button>
                                @endif
                                @if ($canEdit)
                                    <button wire:click="$dispatch('open-product-form', { id: '{{ $p->id }}' })" title="แก้ไข" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#D97706] flex">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                @endif
                                @if ($canDelete)
                                    <button wire:click="deleteProduct('{{ $p->id }}')" wire:confirm="ต้องการลบสินค้านี้ใช่ไหม?" title="ลบ" class="p-1.5 border-[1.5px] border-line rounded-[7px] bg-surface text-[#DC2626] flex">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modals --}}
    <livewire:product-detail />
    @if ($canAdd || $canEdit)
        <livewire:product-form />
    @endif
    @if ($canImport)
        <livewire:import-modal />
    @endif
    @if (auth()->user()->hasPermission('categories', 'view'))
        <livewire:category-manager />
    @endif
    @if (auth()->user()->hasPermission('brands', 'view'))
        <livewire:brand-manager />
    @endif
</div>

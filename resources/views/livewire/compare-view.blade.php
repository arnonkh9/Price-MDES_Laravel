@php
    use App\Support\Specs;
    $fmt = fn ($n) => $n ? number_format((float) $n).' ฿' : '—';
    $specColor = '#7C3AED';
    $colCount = ($baseSpec ? 1 : 0) + $items->count() + 1;
@endphp
<div class="p-4 md:p-7">
    {{-- Toolbar --}}
    <div class="flex justify-between items-end mb-[18px] no-print flex-wrap gap-3">
        <div>
            <h2 class="text-[22px] font-extrabold text-ink mb-1.5">เปรียบเทียบสินค้า</h2>
            <p class="text-muted text-[13px] m-0 flex items-center gap-2.5">
                @if ($baseSpec)<span class="bg-[#F5F3FF] text-[#7C3AED] px-2.5 py-[3px] rounded-md text-xs font-bold">📋 สเปคอ้างอิง: {{ $baseSpec->name }}</span>@endif
                @if ($items->count())<span>{{ $items->count() }} สินค้าที่เลือก</span>@endif
            </p>
        </div>
        <div class="flex gap-2 items-center">
            <div class="relative" x-data="{ open:false }">
                <button @click="open=!open" class="flex items-center gap-1.5 px-3.5 py-2 border-[1.5px] rounded-lg text-[13px] font-semibold {{ $baseSpec ? 'border-[#7C3AED] bg-[#F5F3FF] text-[#7C3AED]' : 'border-line bg-surface text-ink' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    {{ $baseSpec ? 'เปลี่ยนสเปคอ้างอิง' : 'เลือกสเปคอ้างอิง' }}
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open=false" class="absolute top-[calc(100%+6px)] right-0 bg-surface-raised border-[1.5px] border-line rounded-xl p-2 w-[260px] z-[100]" style="box-shadow:0 8px 24px rgba(0,0,0,0.12)">
                    <div class="text-[11px] font-bold text-faint uppercase tracking-wider px-2 pt-1 pb-2">เลือกคุณลักษณะพื้นฐาน</div>
                    @forelse ($specs as $s)
                        <button wire:click="setBaseSpec('{{ $s->id }}')" @click="open=false" class="w-full flex items-center gap-2.5 px-2.5 py-[9px] border-none bg-transparent rounded-lg text-left hover:bg-surface-alt">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $colors[$s->category] ?? '#64748B' }}"></span>
                            <div>
                                <div class="text-[13px] font-bold text-ink">{{ $s->name }}</div>
                                <div class="text-[11px] text-faint">{{ Specs::label($s->category) }}</div>
                            </div>
                        </button>
                    @empty
                        <div class="px-2 py-3 text-[13px] text-faint">ยังไม่มีสเปค</div>
                    @endforelse
                    @if ($baseSpec)
                        <button wire:click="clearBaseSpec" @click="open=false" class="w-full p-2 border-none bg-transparent text-[#DC2626] text-xs font-bold border-t border-line-soft mt-1">ล้างสเปคอ้างอิง</button>
                    @endif
                </div>
            </div>
            <button onclick="window.print()" class="flex items-center gap-1.5 px-3.5 py-2 border-[1.5px] border-line bg-surface text-ink rounded-lg text-[13px] font-semibold">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                พิมพ์
            </button>
            @if ($items->count())
                <button wire:click="clear" class="px-3.5 py-2 border-[1.5px] border-[#FEE2E2] bg-[#FFF5F5] text-[#DC2626] rounded-lg text-[13px] font-bold">ล้างสินค้า</button>
            @endif
        </div>
    </div>

    @if ($items->isEmpty() && ! $baseSpec)
        <div class="flex flex-col items-center justify-center py-[100px] gap-3">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <h3 class="text-lg font-bold text-faint m-0">ยังไม่มีสินค้าในรายการเปรียบเทียบ</h3>
            <p class="text-sm text-[#CBD5E1] m-0 text-center">กลับไปที่รายการสินค้าแล้วติ๊กเลือกสินค้าที่ต้องการ (สูงสุด 3 รายการ)</p>
        </div>
    @else
        <div class="bg-surface rounded-[14px] border border-line overflow-auto" style="box-shadow:0 2px 8px rgba(0,0,0,0.04)">
            {{-- Mobile scroll hint --}}
            <div class="text-[11px] text-faint text-center md:hidden py-1 px-2 border-b border-line-soft">← เลื่อนเพื่อดูเพิ่มเติม →</div>
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-3.5 bg-surface-alt text-xs font-bold text-muted border-b border-line sticky left-0 z-10 text-left" style="width:190px">รายละเอียด</th>
                        @if ($baseSpec)
                            <th class="p-0 border-l border-line align-top bg-surface" style="border-top:4px solid {{ $specColor }};min-width:80px;@media(min-width:768px){min-width:280px}">
                                <div class="px-[18px] pt-4 pb-3.5 flex flex-col gap-1.5">
                                    <span class="self-start text-white text-[10px] font-extrabold px-[7px] py-0.5 rounded" style="background:{{ $specColor }}">📋 สเปคอ้างอิง</span>
                                    <div class="text-xs text-faint font-semibold">{{ Specs::label($baseSpec->category) }}</div>
                                    <div class="text-sm font-extrabold leading-tight" style="color:{{ $specColor }}">{{ $baseSpec->name }}</div>
                                    @if ((float) $baseSpec->budget > 0)<div class="text-xl font-black text-price">{{ $fmt($baseSpec->budget) }}</div>@endif
                                    <div class="text-[11px] text-faint">วงเงินงบประมาณ · {{ $baseSpec->created_date }}</div>
                                    <span class="self-start text-xs font-bold text-[#7C3AED] bg-[#F5F3FF] px-2 py-[3px] rounded">{{ collect($baseSpec->specs ?? [])->filter()->count() }} ข้อกำหนด</span>
                                </div>
                            </th>
                        @endif
                        @foreach ($items as $p)
                            @php $color = $colors[$p->category] ?? '#64748B'; @endphp
                            <th class="p-0 border-l border-line align-top bg-surface relative" style="border-top:4px solid {{ $color }};min-width:80px;@media(min-width:768px){min-width:260px}">
                                <div class="px-[18px] pt-4 pb-3.5 flex flex-col gap-1.5">
                                    <button wire:click="remove('{{ $p->id }}')" title="นำออก" class="no-print absolute top-2.5 right-2.5 w-[22px] h-[22px] rounded-full bg-[#FEE2E2] border-none text-[#DC2626] flex items-center justify-center">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                    <span class="self-start text-white text-[10px] font-extrabold px-[7px] py-0.5 rounded" style="background:{{ $color }}">{{ Specs::label($p->category) }}</span>
                                    <div class="text-xs text-faint font-semibold">{{ $p->brand }}</div>
                                    <div class="text-xs font-extrabold text-ink leading-tight pr-6">{{ $p->model }}</div>
                                    <div class="text-lg font-black text-price">{{ $fmt($p->price) }}</div>
                                    <div class="text-[11px] text-faint">{{ $p->price_ref }} · {{ $p->price_date }}</div>
                                    <button wire:click="$dispatch('open-product-detail', { id: '{{ $p->id }}' })" class="no-print self-start px-2.5 py-[5px] bg-[#EFF6FF] text-[#1D4ED8] border-none rounded-md text-xs font-bold mt-0.5">ดูรายละเอียดเต็ม →</button>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @if ($row['type'] === 'group')
                            <tr class="bg-line-soft">
                                <td colspan="{{ $colCount }}" class="px-4 py-[9px] text-[11px] font-extrabold text-navy uppercase tracking-wider border-b border-line sticky left-0">{{ $row['label'] }}</td>
                            </tr>
                        @else
                            @php
                                $field = $row['field'];
                                $vals = $items->map(fn ($p) => trim((string) ($p->specs[$field] ?? '')));
                                $allSame = $vals->count() > 1 && $vals->unique()->count() === 1;
                                $differ = $vals->count() > 1 && ! $allSame;
                            @endphp
                            <tr class="border-b border-line-soft">
                                <td class="px-4 py-2.5 text-xs text-muted font-semibold align-top bg-surface-alt sticky left-0 border-r border-line whitespace-nowrap">{{ $field }}</td>
                                @if ($baseSpec)
                                    <td class="px-4 py-2.5 align-top" style="background:#FAF5FF;border-left:2px solid {{ $specColor }}20">
                                        @if (! empty($baseSpec->specs[$field] ?? null))
                                            <div class="text-[10px] font-bold text-[#7C3AED] bg-[#F5F3FF] inline-block px-1.5 rounded mb-1">ข้อกำหนด</div>
                                            @foreach (explode("\n", (string) $baseSpec->specs[$field]) as $ln)<div class="text-[12px] leading-snug" style="color:#4C1D95">{{ $ln }}</div>@endforeach
                                        @else<span class="text-[#C4B5FD] text-[12px]">ไม่ระบุ</span>@endif
                                    </td>
                                @endif
                                @foreach ($items as $p)
                                    <td class="px-4 py-2.5 text-[12px] text-ink align-top border-l border-line-soft {{ $differ ? 'bg-amber-50 dark:bg-amber-950/20' : 'bg-surface' }}">
                                        @if (! empty($p->specs[$field] ?? null))
                                            @foreach (explode("\n", (string) $p->specs[$field]) as $ln)<div class="leading-snug">{{ $ln }}</div>@endforeach
                                        @else<span class="text-[#CBD5E1] text-base">—</span>@endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-2 mt-3 pl-1 flex-wrap">
            @if ($baseSpec)
                <span class="inline-block w-3.5 h-3.5 rounded-[3px]" style="background:#FAF5FF;border:1px solid #DDD6FE"></span>
                <span class="text-xs text-faint mr-3">คอลัมน์สีม่วง = ข้อกำหนดสเปคอ้างอิง</span>
            @endif
            <span class="inline-block w-3.5 h-3.5 rounded-[3px]" style="background:#FFFBEB;border:1px solid #FDE68A"></span>
            <span class="text-xs text-faint">พื้นหลังสีเหลืองอ่อน = ค่าที่แตกต่างกันระหว่างสินค้า</span>
        </div>
    @endif

    <livewire:product-detail />
</div>

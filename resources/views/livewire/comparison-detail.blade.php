@php
    use App\Support\Specs;
    $fmt = fn ($n) => $n ? number_format((float) $n).' ฿' : '—';
    $canEdit = auth()->user()->hasPermission('comparisons', 'edit');
    $vColors = ['#2563EB', '#059669', '#DC2626', '#D97706', '#7C3AED'];
    $statuses = ['draft' => ['label' => 'ร่าง', 'color' => '#94A3B8', 'bg' => '#F1F5F9'], 'final' => ['label' => 'สรุปแล้ว', 'color' => '#059669', 'bg' => '#F0FFF4']];
@endphp
<div>
@if ($show && $cmp)
    @php $colCount = ($spec ? 1 : 0) + $cmp->vendors->count() + 1; $st = $statuses[$cmp->status] ?? $statuses['draft']; @endphp
    <div class="modal-overlay fixed inset-0 z-[200] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75" wire:click.self="close">
        <div class="modal-card bg-surface rounded-2xl w-[900px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            <div class="px-[26px] pt-5 pb-4 flex justify-between items-start border-b border-line-soft gap-4" style="border-top:4px solid {{ $color }}">
                <div class="flex-1">
                    <div class="flex gap-2.5 items-center mb-1.5">
                        <span class="text-white text-[11px] font-extrabold px-2 py-[3px] rounded-[5px]" style="background:{{ $color }}">{{ $catLabel }}</span>
                        <span class="text-xs font-bold text-faint bg-line-soft px-2 py-[3px] rounded">เปรียบเทียบราคา {{ $cmp->vendors->count() }} ผู้ผลิต</span>
                        <span class="text-[11px] font-bold px-2 py-[3px] rounded" style="background:{{ $st['bg'] }};color:{{ $st['color'] }}">{{ $st['label'] }}</span>
                    </div>
                    <h2 class="text-[18px] font-extrabold text-ink mb-2">{{ $cmp->name }}</h2>
                    <div class="flex gap-2.5 flex-wrap items-center">
                        @if ($spec)<span class="text-xs text-[#7C3AED] bg-[#F5F3FF] px-2 py-0.5 rounded font-semibold">สเปคอ้างอิง: {{ $spec->name }}</span>@endif
                        <span class="text-xs text-faint">ปี {{ $cmp->year }}{{ $cmp->month ? ' / '.Specs::monthLabel($cmp->month) : '' }}</span>
                        <span class="text-xs text-faint">สร้างโดย {{ $cmp->created_by }} · {{ $cmp->created_date }}</span>
                    </div>
                </div>
                <div class="flex gap-[7px] shrink-0 no-print">
                    @if ($canEdit)
                        <button wire:click="editComparison" class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] rounded-lg text-[13px] font-semibold" style="color:#D97706;background:#FFFBEB;border-color:#FDE68A">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            แก้ไข
                        </button>
                    @endif
                    <a href="{{ route('comparisons.export', $cmp->id) }}" class="flex items-center gap-1.5 px-3.5 py-2 border-none text-white rounded-lg text-[13px] font-bold" style="background:#059669">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Excel
                    </a>
                    <a href="{{ route('comparisons.export.pdf', $cmp->id) }}" class="flex items-center gap-1.5 px-3.5 py-2 border-none text-white rounded-lg text-[13px] font-bold" style="background:#DC2626">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        PDF
                    </a>
                    <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto px-[26px] py-5 flex-1">
                <div class="bg-surface border border-line rounded-xl overflow-auto mb-4">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-3.5 py-3 bg-surface-alt text-xs font-bold text-muted border-b border-line sticky left-0 z-10 text-left" style="width:180px">รายการ</th>
                                @if ($spec)
                                    <th class="p-0 border-l border-line align-top bg-surface" style="border-top:4px solid #7C3AED;min-width:200px">
                                        <div class="px-4 py-3.5 flex flex-col gap-1">
                                            <span class="self-start text-white text-[10px] font-extrabold px-[7px] py-0.5 rounded" style="background:#7C3AED">📋 สเปคอ้างอิง</span>
                                            <div class="text-[13px] font-extrabold leading-tight" style="color:#4C1D95">{{ $spec->name }}</div>
                                            @if ((float) $spec->budget > 0)<div class="text-lg font-black text-ink">{{ number_format((float) $spec->budget) }} ฿</div>@endif
                                            <div class="text-[11px] text-faint">วงเงินงบประมาณ</div>
                                        </div>
                                    </th>
                                @endif
                                @foreach ($cmp->vendors as $i => $vd)
                                    @php $vc = $vColors[$i] ?? $color; @endphp
                                    <th class="p-0 border-l border-line align-top bg-surface" style="border-top:4px solid {{ $vc }};min-width:200px">
                                        <div class="px-4 py-3.5 flex flex-col gap-1">
                                            <span class="self-start text-white text-[10px] font-extrabold px-[7px] py-0.5 rounded" style="background:{{ $vc }}"> {{ $i + 1 }}</span>
                                            <div class="text-[13px] font-extrabold text-ink leading-tight">{{ $vd->name }}</div>
                                            <div class="text-[11px] text-faint">{{ $vd->brand }} — {{ $vd->model }}</div>
                                            <div class="text-lg font-black flex items-center gap-2" style="color:{{ (float) $vd->price === (float) $minPrice && $minPrice ? '#059669' : '#1E293B' }}">
                                                {{ $fmt($vd->price) }}
                                                @if ((float) $vd->price === (float) $minPrice && $minPrice)<span class="text-[10px] font-extrabold text-price bg-[#F0FFF4] px-[7px] py-0.5 rounded">ต่ำสุด</span>@endif
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @if ($row['type'] === 'group')
                                    <tr class="bg-line-soft"><td colspan="{{ $colCount }}" class="px-4 py-[9px] text-[11px] font-extrabold text-navy uppercase tracking-wider border-b border-line sticky left-0">{{ $row['label'] }}</td></tr>
                                @else
                                    @php $field = $row['field']; @endphp
                                    <tr class="border-b border-line-soft">

                                        <td class="px-3.5 py-3 text-xs text-muted font-semibold align-top bg-surface-alt sticky left-0 border-r border-line whitespace-nowrap">{{ $field }}
                                            
                                        </td>
                                        
                                        @if ($spec)
                                            <td class="px-3.5 py-3 align-top" style="background:#FAF5FF;border-left:2px solid #DDD6FE">
                                                
                                                @if (! empty($spec->specs[$field] ?? null))
    
                                                    @foreach (explode("\n", (string) $spec->specs[$field]) as $ln)<div class="text-[13px] leading-relaxed mb-0.5" style="color:#4C1D95">{{ $ln }}</div>@endforeach
                                                @else<span style="color:#C4B5FD">—</span>@endif
                                            </td>
                                        @endif
                                        @foreach ($cmp->vendors as $vd)
                                            <td class="px-3.5 py-3 text-[13px] text-ink align-top border-l border-line-soft">
                                                @if (! empty($vd->specs[$field] ?? null))
                                                    @foreach (explode("\n", (string) $vd->specs[$field]) as $ln)<div class="leading-relaxed mb-0.5">{{ $ln }}</div>@endforeach
                                                @else<span style="color:#CBD5E1">—</span>@endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($cmp->notes)
                    <div class="px-4 py-3 bg-[#FFFBEB] rounded-lg text-[13px] border border-[#FDE68A]" style="color:#92400E"><strong>หมายเหตุ:</strong> {{ $cmp->notes }}</div>
                @endif
            </div>
        </div>
    </div>
@endif
</div>

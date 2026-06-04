@php
    $fmt = fn ($n) => $n ? number_format((float) $n).' บาท' : '—';
    $canEdit = auth()->user()->hasPermission('specs', 'edit');
@endphp
<div>
@if ($show && $spec)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75" wire:click.self="close">
        <div class="bg-surface rounded-2xl w-[760px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            <div class="px-[26px] pt-[22px] pb-[18px] flex justify-between items-start gap-4 border-b border-line-soft" style="border-top:4px solid {{ $color }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 mb-1.5">
                        <span class="text-white text-[11px] font-extrabold px-2 py-[3px] rounded-[5px]" style="background:{{ $color }}">{{ $catLabel }}</span>
                        <span class="text-xs font-bold text-faint bg-line-soft px-2 py-[3px] rounded">คุณลักษณะพื้นฐาน</span>
                    </div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-1.5 leading-tight">{{ $spec->name }}</h2>
                    @if ($spec->purpose)<p class="text-[13px] text-muted mb-2 leading-normal">{{ $spec->purpose }}</p>@endif
                    <div class="flex items-center gap-3 flex-wrap">
                        @if ((float) $spec->budget > 0)<span class="text-lg font-black text-price">วงเงิน {{ $fmt($spec->budget) }}</span>@endif
                        <span class="text-xs text-faint">สร้างโดย {{ $spec->created_by }} · {{ $spec->created_date }}</span>
                    </div>
                </div>
                <div class="flex gap-[7px] shrink-0">
                    @if ($canEdit)
                        <button wire:click="editCharacteristics" class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] rounded-lg text-[13px] font-semibold" style="color:#D97706;background:#FFFBEB;border-color:#FDE68A">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            แก้ไข
                        </button>
                    @endif
                    @if ($canCompare)
                    <button wire:click="useCompare" class="flex items-center gap-1.5 px-3.5 py-2 border-none text-white rounded-lg text-[13px] font-bold" style="background:{{ $color }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        ใช้เปรียบเทียบ
                    </button>
                    @endif
                    <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex border-b border-line px-[26px]">
                @foreach (['characteristics' => 'คุณลักษณะพื้นฐาน', 'history' => 'ประวัติ'] as $id => $lbl)
                    <button wire:click="$set('tab','{{ $id }}')" class="px-[18px] py-3 text-sm font-semibold border-b-2 {{ $tab === $id ? 'text-navy border-navy' : 'text-faint border-transparent' }}">{{ $lbl }}</button>
                @endforeach
            </div>

            <div class="overflow-y-auto px-[26px] py-[22px] flex-1">
                @if ($tab === 'characteristics')
                    @foreach ($groups as $group)
                        @php $rows = collect($group['fields'])->filter(fn ($f) => ! empty($spec->specs[$f] ?? null)); @endphp
                        @if ($rows->isNotEmpty())
                            <div class="mb-[22px]">

                                <table class="w-full border-collapse">
                                    <tbody>
                                        @foreach ($rows as $field)
                                            <tr class="border-b border-line-soft">
                                              
                                                <td class="py-2 text-[13px] text-ink leading-relaxed align-top">
                                                    
                                                    @foreach (explode("\n", (string) $spec->specs[$field]) as $ln)<div class="leading-relaxed">{{ $ln }}</div>@endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endforeach
                @else
                    @forelse ($spec->histories->reverse() as $h)
                        <div class="flex gap-3 py-3 border-b border-line-soft">
                            <div class="w-2.5 h-2.5 rounded-full mt-[5px] shrink-0" style="background:{{ $color }}"></div>
                            <div>
                                <div class="flex gap-2.5 items-center mb-[3px]">
                                    <span class="font-bold text-ink text-sm">{{ $h->action }}</span>
                                    <span class="text-[#2563EB] text-[13px]">โดย {{ $h->user }}</span>
                                    <span class="text-faint text-xs ml-auto">{{ $h->date }}</span>
                                </div>
                                @if ($h->detail)<div class="text-[13px] text-muted">{{ $h->detail }}</div>@endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-faint p-12">ไม่มีประวัติ</div>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
@endif
</div>

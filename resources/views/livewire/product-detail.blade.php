@php
    use Illuminate\Support\Facades\Storage;
    $fmt = fn ($n) => $n ? number_format((float) $n).' บาท' : '—';
    $canEdit = auth()->user()->hasPermission('products', 'edit');
    $srcIcon = ['Excel' => '📊', 'กรอกด้วยมือ' => '✍️', 'ดาวน์โหลดจากเว็บ' => '🌐', 'API / ระบบอัตโนมัติ' => '⚙️'];
@endphp
<div>
@if ($show && $product)
    <div class="modal-overlay fixed inset-0 z-[200] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75" wire:click.self="close">
        <div class="modal-card bg-surface rounded-2xl w-[780px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            {{-- Header --}}
            <div class="px-4 md:px-[26px] pt-4 md:pt-[22px] pb-4 md:pb-[18px] flex justify-between items-start gap-4 border-b border-line-soft" style="border-top:4px solid {{ $color }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 mb-1.5">
                        <span class="text-white text-[11px] font-extrabold px-2 py-[3px] rounded-[5px]" style="background:{{ $color }}">{{ $catLabel }}</span>
                        <span class="text-sm font-bold text-muted">{{ $product->brand }}</span>
                    </div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-2.5 leading-tight">{{ $product->model }}</h2>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-[22px] font-black text-price">{{ $fmt($product->price) }}</span>
                        <span class="text-xs text-faint">วันที่ {{ $product->price_date }}</span>
                    </div>
                </div>
                <div class="flex gap-[7px] items-start shrink-0 no-print flex-wrap justify-end">
                    @if ($canEdit)
                        <button wire:click="editProduct" class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] rounded-lg text-[13px] font-semibold" style="color:#D97706;background:#FFFBEB;border-color:#FDE68A">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            แก้ไข
                        </button>
                    @endif
                    <button onclick="window.print()" class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] border-line bg-surface text-ink rounded-lg text-[13px] font-semibold">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        พิมพ์
                    </button>
                    @if ($canCompare)
                    <button wire:click="toggleCompare" @disabled(! $inCompare && $compareCount >= 3)
                        class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] rounded-lg text-[13px] font-semibold disabled:opacity-50"
                        style="{{ $inCompare ? 'color:white;background:#1D4ED8;border-color:#1D4ED8' : 'color:#1D4ED8;background:#EFF6FF;border-color:#BFDBFE' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        {{ $inCompare ? 'ยกเลิกเปรียบเทียบ' : 'เปรียบเทียบ' }}
                    </button>
                    @endif
                    <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-line px-4 md:px-[26px] no-print overflow-x-auto">
                @foreach (['characteristics' => 'คุณลักษณะพื้นฐาน', 'specs' => 'ข้อมูลจำเพาะ', 'history' => 'ประวัติการแก้ไข'] as $id => $lbl)
                    <button wire:click="$set('tab','{{ $id }}')" class="px-[18px] py-3 text-sm font-semibold border-b-2 {{ $tab === $id ? 'text-navy border-navy' : 'text-faint border-transparent' }}">{{ $lbl }}</button>
                @endforeach
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto px-4 md:px-[26px] py-4 md:py-[22px] flex-1">
                @if ($tab === 'characteristics')
                    {{-- Price & Sourcing Information Section --}}
                    <div class="mb-[28px]">
                        <div class="text-[13px] font-extrabold text-navy pl-2.5 mb-2.5" style="border-left:3px solid {{ $color }}">ข้อมูลราคา & แหล่งที่มา</div>
                        <div class="bg-surface-alt border-[1.5px] border-line rounded-xl p-4 mb-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Column --}}
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <div class="text-[11px] font-bold text-muted uppercase tracking-wider mb-1">ราคา</div>
                                        <div class="text-[18px] font-black text-price">{{ $fmt($product->price) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-bold text-muted uppercase tracking-wider mb-1">ราคาต่อหน่วย</div>
                                        <div class="text-sm text-ink">{{ $product->price_unit ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-bold text-muted uppercase tracking-wider mb-1">วันที่อ้างอิง</div>
                                        <div class="text-sm text-ink">{{ $product->price_date ?? '—' }}</div>
                                    </div>
                                </div>
                                {{-- Right Column --}}
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <div class="text-[11px] font-bold text-muted uppercase tracking-wider mb-1">แหล่งที่มา/อ้างอิง</div>
                                        <div class="text-sm text-ink">{{ $product->price_source ?? '—' }}</div>
                                    </div>
                                    @if ($product->price_url)
                                        <div>
                                            <div class="text-[11px] font-bold text-muted uppercase tracking-wider mb-1">Link/URL</div>
                                            <a href="{{ $product->price_url }}" target="_blank" rel="noopener noreferrer" class="text-[13px] text-[#2563EB] hover:underline break-all">
                                                {{ \Illuminate\Support\Str::limit($product->price_url, 50) }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if ($product->attachments && $product->attachments->count() > 0)
                                <div class="mt-3.5 pt-3.5 border-t border-line">
                                    <div class="text-[11px] font-bold text-muted uppercase tracking-wider mb-2.5">ไฟล์แนบ</div>
                                    <div class="space-y-2">
                                        @foreach ($product->attachments as $attachment)
                                            @if ($attachment->is_image)
                                                <div class="flex items-center justify-between p-2.5 bg-surface-alt rounded border-[1px] border-line hover:bg-[#EFF6FF] dark:hover:bg-blue-950/40 transition">
                                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                                        <img src="{{ Storage::disk('public')->url($attachment->file_path) }}"
                                                             class="w-12 h-12 object-cover rounded border border-line shrink-0" alt="{{ $attachment->original_name }}">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="text-[12px] text-ink truncate font-medium">{{ $attachment->original_name }}</div>
                                                            <div class="text-[10px] text-faint">{{ $attachment->formatted_size }}</div>
                                                        </div>
                                                    </div>
                                                    <a href="{{ Storage::disk('public')->url($attachment->file_path) }}" download class="p-1.5 rounded text-[#2563EB] hover:bg-surface transition ml-2 shrink-0" title="ดาวน์โหลด">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-between p-2.5 bg-surface-alt rounded border-[1px] border-line hover:bg-[#EFF6FF] dark:hover:bg-blue-950/40 transition">
                                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#DC2626] shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="text-[12px] text-ink truncate font-medium">{{ $attachment->original_name }}</div>
                                                            <div class="text-[10px] text-faint">{{ $attachment->formatted_size }}</div>
                                                        </div>
                                                    </div>
                                                    <a href="{{ Storage::disk('public')->url($attachment->file_path) }}" download class="p-1.5 rounded text-[#2563EB] hover:bg-surface transition ml-2 shrink-0" title="ดาวน์โหลด">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                @elseif ($tab === 'specs')
                    @php $hasSpecs = collect($groups)->some(fn ($g) => collect($g['fields'])->some(fn ($f) => ! empty($product->specs[$f] ?? null))); @endphp
                    @if ($hasSpecs)
                        @foreach ($groups as $group)
                            @php $rows = collect($group['fields'])->filter(fn ($f) => ! empty($product->specs[$f] ?? null)); @endphp
                            @if ($rows->isNotEmpty())
                                <div class="mb-[22px]">
                                    <div class="text-[13px] font-extrabold text-navy pl-2.5 mb-2.5" style="border-left:3px solid {{ $color }}">{{ $group['label'] }}</div>
                                    <table class="w-full border-collapse">
                                        <tbody>
                                            @foreach ($rows as $field)
                                                <tr class="border-b border-line-soft">
                                                    <td class="py-2 pr-3 pl-0 text-xs text-muted font-semibold align-top">{{ $field }}</td>
                                                    <td class="py-2 text-[13px] text-ink leading-relaxed align-top">
                                                        @foreach (explode("\n", (string) $product->specs[$field]) as $ln)
                                                            <div class="leading-relaxed">{{ $ln }}</div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="text-center text-faint p-12">ไม่มีข้อมูลจำเพาะ</div>
                    @endif
                @else
                    @if ($canEdit)
                        <div class="bg-surface-alt border-[1.5px] border-line rounded-xl p-4 mb-5 no-print">
                            <div class="flex items-center gap-[7px] text-[13px] font-extrabold text-ink mb-3.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                บันทึกช่องทางนำข้อมูลเข้า
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs font-bold text-ink mb-1.5">ช่องทางนำข้อมูลเข้า</label>
                                <div class="flex gap-1.5 flex-wrap">
                                    @foreach ($sources as $s)
                                        <button wire:click="$set('hSource', @js($s))" class="px-3 py-[5px] border-[1.5px] rounded-full text-xs font-semibold {{ $hSource === $s ? 'text-white' : 'bg-surface text-muted border-line' }}"
                                            @style(['background:'.$color.';border-color:'.$color => $hSource === $s])>{{ $s }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs font-bold text-ink mb-1.5">URL อ้างอิง / แหล่งที่มา</label>
                                <div class="flex items-center gap-2 bg-surface border-[1.5px] border-line rounded-lg px-3">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" class="shrink-0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    <input wire:model="hUrl" placeholder="https://example.com/spec.xlsx" class="flex-1 border-none outline-none text-[13px] py-[9px] text-ink bg-transparent">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs font-bold text-ink mb-1.5">หมายเหตุ</label>
                                <input wire:model="hNote" placeholder="เช่น อัปเดตราคาจาก Spec Sheet เดือน พ.ค. 2569" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] outline-none">
                            </div>
                            <button wire:click="addHistory" class="flex items-center gap-1.5 px-[18px] py-2 border-none text-white rounded-lg text-[13px] font-bold" style="background:{{ $color }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                บันทึกประวัติ
                            </button>
                        </div>
                    @endif

                    <div class="flex flex-col">
                        @forelse ($product->histories->reverse() as $h)
                            <div class="flex gap-3 py-3.5 border-b border-line-soft">
                                <div class="w-2.5 h-2.5 rounded-full mt-[5px] shrink-0" style="background:{{ $color }}"></div>
                                <div class="flex-1">
                                    <div class="flex gap-2.5 items-center flex-wrap mb-1">
                                        <span class="font-bold text-ink text-sm">{{ $h->action }}</span>
                                        @if ($h->source)
                                            <span class="text-[11px] font-bold text-[#7C3AED] bg-[#F5F3FF] px-2 py-0.5 rounded">{{ $srcIcon[$h->source] ?? '' }} {{ $h->source }}</span>
                                        @endif
                                        <span class="text-[#2563EB] text-[13px] font-semibold">โดย {{ $h->user }}</span>
                                        <span class="text-faint text-xs ml-auto">{{ $h->date }}</span>
                                    </div>
                                    @if ($h->detail)<div class="text-[13px] text-muted">{{ $h->detail }}</div>@endif
                                    @if ($h->url)
                                        <a href="{{ $h->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs text-[#2563EB] mt-1 break-all">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                            {{ \Illuminate\Support\Str::limit($h->url, 60) }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-faint p-12">ไม่มีประวัติการแก้ไข</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
</div>

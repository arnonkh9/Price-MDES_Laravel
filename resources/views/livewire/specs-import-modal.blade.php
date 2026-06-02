<div>
@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75"
         @if ($step === 'upload') wire:click.self="close" @endif>

        {{-- Modal card — wider in preview step --}}
        <div class="bg-surface rounded-2xl flex flex-col transition-all duration-200
                    {{ $step === 'preview' ? 'w-[780px]' : 'w-[560px]' }} max-w-full"
             style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">

            {{-- ── Header ── --}}
            <div class="px-[26px] py-5 border-b border-line flex justify-between items-start shrink-0">
                <div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-[3px]">นำเข้าคุณลักษณะจาก Excel</h2>
                    <p class="text-[13px] text-faint m-0">
                        @if ($step === 'upload')
                            อัปโหลดไฟล์ .xlsx / .csv
                        @else
                            ตรวจสอบข้อมูลก่อนยืนยัน
                        @endif
                    </p>
                </div>
                <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- ── Body ── --}}
            <div class="px-[26px] py-5 overflow-y-auto" style="max-height: 70vh">

                @if ($step === 'upload')
                    {{-- ─── Upload Step ─── --}}
                    <div class="text-xs text-muted bg-surface-alt border border-line rounded-lg px-3 py-2.5 mb-4 leading-relaxed">
                        แถวหัวตาราง (heading) รองรับคอลัมน์: <code class="font-mono">name, category, year, month, budget</code>
                        และคอลัมน์เพิ่มเติม: <code class="font-mono">created_date, created_by, purpose</code> รวมถึง <code class="font-mono">Spec 1, Spec 2</code>
                        ฯลฯ จะถูกบันทึกอัตโนมัติ (ต้องมี name และ category อย่างน้อย)
                    </div>

                    <label class="block text-xs font-bold text-ink mb-1.5">เลือกไฟล์</label>
                    <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                           class="w-full text-sm border-[1.5px] border-line rounded-lg p-2.5
                                  file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-none
                                  file:bg-navy file:text-white file:text-xs file:font-bold file:cursor-pointer">
                    @error('file')<div class="text-xs text-[#DC2626] mt-1.5">{{ $message }}</div>@enderror

                    <div wire:loading wire:target="file" class="text-xs text-faint mt-2">กำลังอัปโหลด...</div>

                    @if ($result)
                        <div class="mt-4 flex items-center gap-2 text-[13px] text-price bg-[#F0FFF4] dark:bg-emerald-950/40
                                    border border-[#A7F3D0] dark:border-emerald-800 rounded-lg px-3.5 py-2.5">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            {{ $result }}
                        </div>
                    @endif

                @else
                    {{-- ─── Preview Step ─── --}}

                    {{-- Summary bar --}}
                    <div class="flex items-center gap-4 mb-4 text-[13px]">
                        @if ($previewValid > 0)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40
                                        border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-700 dark:text-emerald-400 font-semibold">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                จะนำเข้า {{ $previewValid }} รายการ
                            </div>
                        @endif
                        @if ($previewSkipped > 0)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-950/40
                                        border border-red-200 dark:border-red-800 rounded-lg text-red-600 dark:text-red-400 font-semibold">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                                ข้าม {{ $previewSkipped }} แถว
                            </div>
                        @endif
                        @if (count($previewRows) === 0)
                            <div class="text-muted">ไม่พบข้อมูลในไฟล์</div>
                        @endif
                    </div>

                    {{-- Preview table --}}
                    @if (count($previewRows) > 0)
                        <div class="rounded-xl border border-line overflow-hidden">
                            <table class="w-full text-[12px]">
                                <thead>
                                    <tr class="bg-surface-alt border-b border-line">
                                        <th class="text-left px-3 py-2 text-muted font-bold w-8">#</th>
                                        <th class="text-left px-3 py-2 text-muted font-bold">ชื่อ</th>
                                        <th class="text-left px-3 py-2 text-muted font-bold">หมวดหมู่</th>
                                        <th class="text-left px-3 py-2 text-muted font-bold">ปี/เดือน</th>
                                        <th class="text-right px-3 py-2 text-muted font-bold">งบประมาณ</th>
                                        <th class="text-center px-3 py-2 text-muted font-bold">Specs</th>
                                        <th class="text-left px-3 py-2 text-muted font-bold w-36">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line-soft">
                                    @foreach ($previewRows as $row)
                                        <tr class="{{ $row['status'] === 'invalid' ? 'bg-red-50/50 dark:bg-red-950/20' : '' }}">
                                            <td class="px-3 py-2 text-faint">{{ $row['row'] }}</td>
                                            <td class="px-3 py-2 text-ink font-medium max-w-[180px] truncate" title="{{ $row['name'] }}">
                                                {{ $row['name'] ?: '—' }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @php
                                                    $catColors = \App\Support\Specs::colorMap();
                                                    $catColor  = $catColors[$row['category']] ?? null;
                                                    $catLabel  = \App\Support\Specs::label($row['category']);
                                                @endphp
                                                @if ($catColor)
                                                    <span class="inline-flex px-2 py-0.5 rounded-[6px] text-[11px] font-bold text-white"
                                                          style="background:{{ $catColor }}">{{ $catLabel }}</span>
                                                @else
                                                    <span class="text-muted">{{ $row['category'] ?: '—' }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-muted whitespace-nowrap">
                                                {{ $row['year'] ? $row['year'] . ($row['month'] ? '/' . $row['month'] : '') : '—' }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono text-ink">
                                                {{ $row['budget'] !== null ? number_format($row['budget'], 0) : '—' }}
                                            </td>
                                            <td class="px-3 py-2 text-center text-muted">
                                                {{ $row['specCount'] > 0 ? $row['specCount'] : '—' }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($row['status'] === 'valid')
                                                    <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                                            <polyline points="20 6 9 17 4 12"/>
                                                        </svg>
                                                        นำเข้าได้
                                                    </span>
                                                @else
                                                    <span class="flex items-start gap-1 text-red-500 dark:text-red-400 font-semibold leading-tight" title="{{ $row['error'] }}">
                                                        <svg class="shrink-0 mt-0.5" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                                        </svg>
                                                        <span class="line-clamp-2">{{ $row['error'] }}</span>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                @endif
            </div>

            {{-- ── Footer ── --}}
            <div class="px-[26px] py-3.5 border-t border-line flex justify-between gap-2.5 shrink-0">

                @if ($step === 'upload')
                    {{-- Upload step footer --}}
                    <button wire:click="downloadSample"
                            class="flex items-center gap-1.5 px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold hover:bg-surface-alt">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        ดาวน์โหลดตัวอย่าง
                    </button>
                    <div class="flex gap-2.5">
                        <button wire:click="close"
                                class="px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold">
                            ปิด
                        </button>
                        <button wire:click="preview" wire:loading.attr="disabled" wire:target="preview,file"
                                @disabled(! $file)
                                class="flex items-center gap-1.5 px-[22px] py-[9px] border-none bg-navy text-white rounded-lg text-sm font-bold disabled:opacity-50">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <span wire:loading.remove wire:target="preview">ตรวจสอบข้อมูล</span>
                            <span wire:loading wire:target="preview">กำลังตรวจสอบ...</span>
                        </button>
                    </div>

                @else
                    {{-- Preview step footer --}}
                    <button wire:click="backToUpload"
                            class="flex items-center gap-1.5 px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold hover:bg-surface-alt">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        ย้อนกลับ
                    </button>
                    <button wire:click="import" wire:loading.attr="disabled" wire:target="import"
                            @disabled($previewValid === 0)
                            class="flex items-center gap-1.5 px-[22px] py-[9px] border-none bg-navy text-white rounded-lg text-sm font-bold disabled:opacity-50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <span wire:loading.remove wire:target="import">ยืนยันนำเข้า ({{ $previewValid }} รายการ)</span>
                        <span wire:loading wire:target="import">กำลังนำเข้า...</span>
                    </button>
                @endif

            </div>
        </div>
    </div>
@endif
</div>

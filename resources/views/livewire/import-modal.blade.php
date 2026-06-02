<div>

@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-3 sm:p-5 bg-slate-900/55 dark:bg-black/75"
         @if ($step === 'upload') wire:click.self="close" @endif>

        {{-- Modal card — responsive width (wider in preview), capped height --}}

        <div wire:key="modal-step-{{ $step }}"
            class="bg-surface rounded-2xl w-[880px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">

            {{-- ── Header ── --}}
            <div class="px-4 md:px-5 py-3.5 md:py-4 border-b border-line bg-surface-alt flex justify-between items-start gap-3 shrink-0">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="hidden sm:flex shrink-0 w-9 h-9 rounded-lg bg-navy/10 text-navy items-center justify-center">
                        @if ($step === 'upload')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        @if ($step === 'upload')
                            <h2 class="text-[17px] sm:text-[19px] font-extrabold text-ink mb-[3px] leading-tight">นำเข้าข้อมูลจาก Excel</h2>
                            <p class="text-xs sm:text-[13px] text-faint m-0">อัปโหลดไฟล์ .xlsx / .csv</p>
                        @else
                            <h2 class="text-[17px] sm:text-[19px] font-extrabold text-ink mb-[3px] leading-tight">ตรวจสอบข้อมูล</h2>
                            <p class="text-xs sm:text-[13px] text-faint m-0">ทบทวนข้อมูลก่อนยืนยันการนำเข้า</p>
                        @endif
                    </div>
                </div>
                <button wire:click="close" class="shrink-0 p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex hover:bg-surface-alt">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- ── Body (scrollable) ── --}}
            <div class="px-4 md:px-5 py-4 overflow-y-auto">
                @if ($step === 'upload')
                    <div class="text-xs text-muted bg-surface-alt border border-line rounded-lg px-3 py-2 mb-3 leading-relaxed">
                        แถวหัวตาราง (heading) รองรับคอลัมน์: <code class="font-mono">id, category, brand, model, price, priceDate, priceRef</code>
                        และคอลัมน์คุณลักษณะพื้นฐานอื่น ๆ เช่น <code class="font-mono">Processor, Main Memory, Storage</code> จะถูกบันทึกอัตโนมัติ
                        (ต้องมี brand และ model อย่างน้อย)
                    </div>

                    <label class="block text-xs font-bold text-ink mb-1">เลือกไฟล์</label>
                    <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                           class="w-full text-sm border-[1.5px] border-line rounded-lg p-2.5 file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-none file:bg-navy file:text-white file:text-xs file:font-bold file:cursor-pointer">
                    @error('file')<div class="text-xs text-[#DC2626] mt-1.5">{{ $message }}</div>@enderror

                    <div wire:loading wire:target="file" class="text-xs text-faint mt-2">กำลังอัปโหลด...</div>

                    @if ($result)
                        <div class="mt-3 flex items-center gap-2 text-[13px] text-price bg-[#F0FFF4] dark:bg-emerald-950/40 border border-[#A7F3D0] dark:border-emerald-800 rounded-lg px-3.5 py-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ $result }}
                        </div>
                    @endif
                @elseif ($step === 'preview')
                    {{-- Preview summary --}}
                    <div class="flex flex-wrap items-center gap-2 mb-3 text-[13px]">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-700 dark:text-emerald-400 font-semibold">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            จะนำเข้า {{ $previewValid }} รายการ
                        </span>
                        @if ($previewSkipped > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-lg text-red-600 dark:text-red-400 font-semibold">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                ข้าม {{ $previewSkipped }} แถว
                            </span>
                        @endif
                    </div>

                    {{-- Preview table — horizontal scroll on small screens --}}
                    <div class="rounded-xl border border-line overflow-hidden">
                        <div class="max-h-[50vh] md:max-h-[400px] overflow-y-auto overflow-x-auto">
                            <table class="w-full min-w-[640px] text-xs">
                                <thead class="sticky top-0 bg-surface-alt border-b border-line z-10">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-bold text-muted w-8">#</th>
                                        <th class="px-3 py-2 text-left font-bold text-muted">ID</th>
                                        <th class="px-3 py-2 text-left font-bold text-muted">แบรนด์</th>
                                        <th class="px-3 py-2 text-left font-bold text-muted">รุ่น</th>
                                        <th class="px-3 py-2 text-left font-bold text-muted">หมวด</th>
                                        <th class="px-3 py-2 text-right font-bold text-muted">ราคา</th>
                                        <th class="px-3 py-2 text-center font-bold text-muted">Specs</th>
                                        <th class="px-3 py-2 text-center font-bold text-muted">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line-soft">
                                    @forelse ($previewRows as $row)
                                        <tr class="hover:bg-[#00000005] dark:hover:bg-white/5">
                                            <td class="px-3 py-2 text-faint">{{ $row['row'] }}</td>
                                            <td class="px-3 py-2 font-mono text-[11px] text-muted max-w-[120px] truncate" title="{{ $row['id'] }}">{{ $row['id'] }}</td>
                                            <td class="px-3 py-2 text-ink">{{ $row['brand'] }}</td>
                                            <td class="px-3 py-2 text-ink max-w-[160px] truncate" title="{{ $row['model'] }}">{{ $row['model'] }}</td>
                                            <td class="px-3 py-2 text-muted whitespace-nowrap">{{ $row['category'] }}</td>
                                            <td class="px-3 py-2 text-right font-mono text-ink whitespace-nowrap">{{ $row['price'] }}</td>
                                            <td class="px-3 py-2 text-center text-muted">{{ $row['specCount'] }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if ($row['status'] === 'valid')
                                                    <span class="text-price">✅</span>
                                                @else
                                                    <span class="text-[#DC2626] cursor-help" title="{{ $row['error'] }}">❌</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-3 py-4 text-center text-faint">ไม่มีข้อมูลในตัวอย่าง</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── Footer ── --}}
            <div class="px-4 md:px-5 py-3 border-t border-line bg-surface-alt shrink-0">
                @if ($step === 'upload')
                    <div wire:key="footer-upload" class="flex flex-col sm:flex-row sm:justify-between gap-2.5">
                        <button wire:click="downloadSample"
                                class="flex items-center justify-center gap-1.5 px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold hover:bg-surface-alt">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            ดาวน์โหลดตัวอย่าง
                        </button>
                        <div class="flex gap-2.5">
                            <button wire:click="close" class="flex-1 sm:flex-none px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold hover:bg-surface-alt">ปิด</button>
                            <button wire:click="preview" wire:loading.attr="disabled" wire:target="preview,file" @disabled(! $file)
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-[22px] py-[9px] border-none bg-navy text-white rounded-lg text-sm font-bold disabled:opacity-50">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <span wire:loading.remove wire:target="preview">ตรวจสอบข้อมูล</span>
                                <span wire:loading wire:target="preview">กำลังตรวจสอบ...</span>
                            </button>
                        </div>
                    </div>
                @elseif ($step === 'preview')
                    <div wire:key="footer-preview" class="flex justify-end gap-2.5">
                        <button wire:click="backToUpload" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold hover:bg-surface-alt">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                            ย้อนกลับ
                        </button>

                        <button wire:click="import" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold hover:bg-surface-alt">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <polyline points="20 6 9 17 4 12"/></svg>
                            <span wire:loading.remove wire:target="import">ยืนยันนำเข้า ({{ $previewValid }})</span>
                            <span wire:loading wire:target="import">กำลังนำเข้า...</span>
                            ยืนยันนำเข้า +
                        </button>


                        
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
</div>

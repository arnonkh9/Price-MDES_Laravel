<div>
@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75">
        <div class="bg-surface rounded-2xl w-[860px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            <div class="px-[26px] py-5 border-b border-line flex justify-between items-start" style="border-left:4px solid {{ $color }}">
                <div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-1">{{ $editingId ? 'แก้ไขคุณลักษณะพื้นฐาน' : 'สร้างคุณลักษณะพื้นฐานใหม่' }}</h2>
                    <p class="text-[13px] text-faint m-0 flex items-center gap-2.5">
                        {{ $editingId ? 'แก้ไข: '.$name : 'กำหนดข้อกำหนดสเปคสำหรับการจัดซื้อ' }}
                        @if ($totalSpecCount > 0)<span class="text-xs bg-[#EFF6FF] text-[#2563EB] px-2 py-0.5 rounded font-bold">{{ $totalSpecCount }} ข้อกำหนด</span>@endif
                    </p>
                </div>
                <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-line px-[26px] overflow-x-auto shrink-0">
                <button wire:click="$set('section','basic')" class="px-[18px] py-[11px] text-sm font-semibold border-b-2 whitespace-nowrap {{ $section === 'basic' ? 'text-navy border-navy' : 'text-faint border-transparent' }}">ข้อมูลพื้นฐาน</button>
                <button wire:click="$set('section','allspecs')" class="px-[18px] py-[11px] text-sm font-semibold border-b-2 whitespace-nowrap {{ $section === 'allspecs' ? 'text-navy border-navy' : 'text-faint border-transparent' }}">
                    คุณลักษณะพื้นฐาน
                    @if ($totalSpecCount > 0)<span class="text-[11px] font-bold px-1.5 py-0.5 rounded-lg ml-1" style="background:{{ $color }}20;color:{{ $color }}">{{ $totalSpecCount }}</span>@endif
                </button>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto px-[26px] py-5 min-w-0">
                    @if ($section === 'basic')
                        <div class="text-sm font-extrabold text-navy mb-4 pb-2 border-b-[1.5px] border-[#EFF6FF]">ข้อมูลพื้นฐาน</div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">ชื่อคุณลักษณะพื้นฐาน *</label>
                            <input wire:model="name" placeholder="เช่น Notebook สำหรับงานสำนักงาน ปี 2569" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                            @error('name')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">ประเภทสินค้า</label>
                                <select wire:model.live="category" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                    @foreach ($categories as $c)<option value="{{ $c->slug }}">{{ $c->label }}</option>@endforeach
                                </select>
                            </div>
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">วงเงินงบประมาณ (บาท/เครื่อง)</label>
                                <input type="number" wire:model="budget" placeholder="0" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">ปี พ.ศ. (ปีงบประมาณ)</label>
                                <select wire:model="year" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                    @foreach ($years as $y)<option value="{{ $y }}">ปี {{ $y }}</option>@endforeach
                                </select>
                            </div>
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">เดือน</label>
                                <select wire:model="month" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                    <option value="">— ไม่ระบุเดือน —</option>
                                    @foreach ($months as $i => $mn)
                                        @php $val = str_pad($i + 1, 2, '0', STR_PAD_LEFT); @endphp
                                        <option value="{{ $val }}">{{ $mn }} (เดือน {{ $val }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">วัตถุประสงค์ / หมายเหตุ</label>
                            <textarea wire:model="purpose" rows="3" placeholder="ระบุวัตถุประสงค์การใช้งาน หรือหมายเหตุเพิ่มเติม" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed"></textarea>
                        </div>

                        <div class="mt-5 p-4 bg-surface-alt rounded-[10px] border-[1.5px] border-dashed" style="border-color:{{ $color }}40">
                            <div class="text-[11px] font-bold text-faint uppercase tracking-wider mb-2.5">ตัวอย่างแสดงผล</div>
                            <div class="flex items-center gap-2.5 mb-1.5">
                                <span class="text-white text-[11px] font-bold px-2 py-[3px] rounded" style="background:{{ $color }}">{{ $previewLabel }}</span>
                                <span class="text-[11px] text-faint">คุณลักษณะพื้นฐาน</span>
                            </div>
                            <div class="text-base font-extrabold text-ink">{{ $name ?: 'ชื่อสเปค' }}</div>
                            @if ((float) $budget > 0)<div class="text-sm text-price font-bold mt-1">วงเงิน {{ number_format((float) $budget) }} บาท</div>@endif
                            <div class="text-[13px] text-muted mt-1">{{ $totalSpecCount }} ข้อกำหนด</div>
                        </div>
                    @else

                        @foreach ($groups as $group)
                            <div class="mb-7 pb-5 border-b border-line-soft">
                                <div class="text-sm font-extrabold text-navy mb-3 pl-2.5" style="border-left:3px solid {{ $color }}">{{ $group['label'] }}</div>
                                @foreach ($group['fields'] as $field)
                                    <div class="mb-3.5">
                                      
                                        <textarea wire:model="specs.{{ $field }}" rows="2" placeholder="ระบุข้อกำหนดสำหรับ {{ $field }}" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed"></textarea>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>

            <div class="px-[26px] py-3.5 border-t border-line flex justify-between items-center">
                <div>@if ($errors->any())<span class="text-[#DC2626] text-[13px] font-semibold">กรุณากรอกข้อมูลที่จำเป็น</span>@endif</div>
                <div class="flex gap-2.5">
                    <button wire:click="close" class="px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold">ยกเลิก</button>
                    <button wire:click="save" class="flex items-center gap-1.5 px-[22px] py-[9px] border-none text-white rounded-lg text-sm font-bold" style="background:{{ $color }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ $editingId ? 'บันทึกการแก้ไข' : 'สร้างสเปค' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

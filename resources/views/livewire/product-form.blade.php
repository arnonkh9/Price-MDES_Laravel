<div>
@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75">
        <div class="bg-surface rounded-2xl w-[880px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
          
        <div class="px-4 md:px-[26px] py-4 md:py-5 border-b border-line flex justify-between items-start">
                <div>
                    <h2 class="text-[19px] font-extrabold text-ink mb-[3px]">{{ $editingId ? 'แก้ไขข้อมูลสินค้า' : 'เพิ่มสินค้าใหม่' }}</h2>
                    <p class="text-[13px] text-faint m-0">{{ $editingId ? 'กำลังแก้ไข: '.$model : 'กรอกข้อมูลสินค้าและสเปค' }}</p>
                </div>
                <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-line px-4 md:px-[26px] overflow-x-auto shrink-0">
                <button wire:click="$set('section','basic')" class="px-[18px] py-[11px] text-sm font-semibold border-b-2 whitespace-nowrap {{ $section === 'basic' ? 'text-navy border-navy' : 'text-faint border-transparent' }}">ข้อมูลพื้นฐาน</button>
                @foreach ($groups as $g)
                    <button wire:click="$set('section','{{ $g['id'] }}')" class="px-[18px] py-[11px] text-sm font-semibold border-b-2 whitespace-nowrap {{ $section === $g['id'] ? 'text-navy border-navy' : 'text-faint border-transparent' }}">{{ $g['label'] }}</button>
                @endforeach
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto px-4 md:px-[26px] py-4 md:py-5 min-w-0">
                    @if ($section === 'basic')
                        <div class="text-sm font-extrabold text-navy mb-4 pb-2 border-b-[1.5px] border-[#EFF6FF]">ข้อมูลพื้นฐาน</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">ประเภทสินค้า *</label>
                                <select wire:model.live="category" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                    @foreach ($categories as $c)
                                    <option value="{{ $c->slug }}">{{ $c->label }}</option>@endforeach
                                </select>
                                
                            </div>
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">แบรนด์ *</label>
                                <select wire:model="brand" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                    <option value="">— เลือกแบรนด์ —</option>
                                    @foreach ($brandOptions as $b)
                                        <option value="{{ $b }}">{{ $b }}</option>
                                    @endforeach
                                </select>
                                @error('brand')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">รุ่น / โมเดล *</label>
                            <input wire:model="model" placeholder="เช่น Vivobook 16 (X1607CA-MB535WA)" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                            @error('model')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">ราคา (บาท)</label>
                                <input type="number" wire:model="price" placeholder="0" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                            </div>

                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">วันที่อ้างอิง</label>
                                <input wire:model="price_date" placeholder="2569-05-21" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                            </div>
                            <div class="mb-3.5">
                                <label class="block text-xs font-bold text-ink mb-[5px]">แหล่งที่มา/อ้างอิง</label>
                                <select wire:model="price_source" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                    <option value="">— เลือก —</option>
                                    <option value="Excel">เอกสาร</option>
                                    <option value="กรอกด้วยมือ">ผู้ผลิต</option>
                                    <option value="ดาวน์โหลดจากเว็บ">จากเว็บ</option>
                                    <option value="API / ระบบอัตโนมัติ">อื่น ๆ</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">Link / URL อ้างอิง</label>
                            <input wire:model="price_url" placeholder="https://example.com/product" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">ไฟล์แนบ (PDF / รูปภาพ)</label>
                            <div class="mb-2.5">
                                <input type="file" wire:model="uploadedFiles" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-navy file:text-white hover:file:bg-blue-700">
                                @error('uploadedFiles.*')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                                <div class="text-[11px] text-faint mt-1.5">รองรับ PDF และรูปภาพ (jpg, png, webp) ขนาดไฟล์สูงสุด 10 MB</div>
                            </div>

                            {{-- Existing attachments --}}
                            @if (count($existingAttachments) > 0)
                                <div class="mt-3 mb-3.5">
                                    <div class="text-[11px] font-bold text-ink mb-2 uppercase">ไฟล์ที่อัพโหลดแล้ว</div>
                                    <div class="space-y-1.5">
                                        @foreach ($existingAttachments as $attachment)
                                            @php
                                                $ext = strtolower(pathinfo($attachment['file_path'] ?? '', PATHINFO_EXTENSION));
                                                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                            @endphp
                                            <div class="flex items-center justify-between p-2 bg-surface-alt rounded border-[1px] border-line">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    @if ($isImg)
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($attachment['file_path']) }}"
                                                             class="w-10 h-10 object-cover rounded shrink-0 border border-line" alt="">
                                                    @else
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#DC2626] shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-[12px] text-ink truncate">{{ $attachment['original_name'] ?? 'ไฟล์แนบ' }}</div>
                                                        <div class="text-[10px] text-faint">{{ $attachment['formatted_size'] ?? ($attachment['file_size'] / 1024 / 1024 > 0 ? round($attachment['file_size'] / 1024 / 1024, 2) . ' MB' : round($attachment['file_size'] / 1024, 2) . ' KB') }}</div>
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="removeAttachment({{ $attachment['id'] }})" class="p-1.5 rounded text-[#DC2626] hover:bg-[#FEE2E2] dark:hover:bg-red-950/40 ml-2 shrink-0">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                    @else
                    
                        @php $group = collect($groups)->firstWhere('id', $section); @endphp
                        <div class="text-sm font-extrabold text-navy mb-4 pb-2 border-b-[1.5px] border-[#EFF6FF]">{{ $group['label'] }}</div>
                       
                        @foreach ($group['fields'] as $field)
                            <div class="mb-3.5">
                                <textarea wire:model="specs.{{ $field }}" rows="2" placeholder="คุณลักษณะพื้นฐาน {{ $field }}" 
                                class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed"></textarea>
                            </div>
                        @endforeach
                    @endif
            </div>

            <div class="px-[26px] py-3.5 border-t border-line flex justify-between items-center">
                <div>
                    @if ($errors->any())
                        <span class="flex items-center gap-1.5 text-[#DC2626] text-[13px] font-semibold">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            กรุณากรอกข้อมูลที่จำเป็น
                        </span>
                    @endif
                </div>
                <div class="flex gap-2.5">
                    <button wire:click="close" class="px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold">ยกเลิก</button>
                    <button wire:click="save" class="flex items-center gap-1.5 px-[22px] py-[9px] border-none bg-navy text-white rounded-lg text-sm font-bold">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ $editingId ? 'บันทึกการแก้ไข' : 'เพิ่มสินค้า' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

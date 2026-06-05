@php $vColors = ['#2563EB', '#059669', '#DC2626', '#D97706', '#7C3AED']; @endphp
<div>
@if ($show)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-5 bg-slate-900/55 dark:bg-black/75">
        <div class="bg-surface rounded-2xl w-[780px] max-w-full max-h-[92vh] flex flex-col" style="box-shadow:0 24px 80px rgba(0,0,0,0.25)">
            <div class="px-[26px] py-5 border-b border-line flex justify-between items-start">
                <div>
                    <h2 class="text-[18px] font-extrabold text-ink mb-[3px]">{{ $editingId ? 'แก้ไขการเปรียบเทียบ' : 'สร้างการเปรียบเทียบใหม่' }}</h2>
                    <p class="text-[13px] text-faint m-0">บันทึกข้อมูลและราคาจากผู้ผลิต (3–5 ราย)</p>
                </div>
                <button wire:click="close" class="p-2 border-[1.5px] border-line bg-surface rounded-lg text-muted flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-line px-[26px] overflow-x-auto shrink-0">
                <button wire:click="$set('tab','info')" class="px-[18px] py-[11px] text-sm font-semibold border-b-2 whitespace-nowrap {{ $tab === 'info' ? 'text-navy border-navy' : 'text-faint border-transparent' }}">ข้อมูลทั่วไป</button>
                @foreach ($vendors as $i => $v)
                    @php $vc = $vColors[$i] ?? '#64748B'; $vn = $vendors[$i]['name'] ?? ''; @endphp
                    <button wire:click="$set('tab','v{{ $i }}')" class="px-[18px] py-[11px] text-sm font-semibold border-b-2 whitespace-nowrap {{ $tab === 'v'.$i ? 'border-current' : 'text-faint border-transparent' }}"
                        style="{{ $tab === 'v'.$i ? 'color:'.$vc : '' }}">
                        ผู้ผลิตที่ {{ $i + 1 }}@if ($vn)<span class="text-[11px] text-faint ml-1">({{ \Illuminate\Support\Str::limit($vn, 8) }})</span>@endif
                    </button>
                @endforeach
                @if (count($vendors) < 5)
                    <button wire:click="addVendor" type="button" title="เพิ่มผู้ผลิต"
                        class="px-[14px] py-[11px] text-sm font-bold text-navy whitespace-nowrap border-b-2 border-transparent hover:text-blue-700">
                        + เพิ่มผู้ผลิต
                    </button>
                @endif
            </div>

            <div class="overflow-y-auto px-[26px] py-5 flex-1 min-w-0 h-full">
                @if ($tab === 'info')

                    <div class="grid grid-cols-2 gap-3">
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">ประเภทสินค้า</label>
                            <select wire:model.live="category" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                @foreach ($categories as $c)<option value="{{ $c->slug }}">{{ $c->label }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">สถานะ</label>
                            <select wire:model="status" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                <option value="draft">ร่าง</option>
                                <option value="final">สรุปแล้ว</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">ปี พ.ศ.</label>
                            <select wire:model.live="year" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                <option value="">— เลือกปี —</option>
                                @foreach ($years as $y)<option value="{{ $y }}">ปี {{ $y }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">เดือน</label>
                            <select wire:model="month" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                                <option value="">— ไม่ระบุ —</option>
                                @foreach ($months as $i => $mn)
                                    @php $val = str_pad($i + 1, 2, '0', STR_PAD_LEFT); @endphp
                                    <option value="{{ $val }}">{{ $mn }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3.5">
                        <label class="block text-xs font-bold text-ink mb-[5px]">คุณลักษณะพื้นฐานอ้างอิง (ถ้ามี)</label>
                        <select wire:model="specTemplateId"
                            @disabled($year === '' || empty($filteredCharacteristics))
                            class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-surface-alt">
                            <option value="">
                                @if ($year === '')
                                    — กรุณาเลือกปีก่อน —
                                @elseif (empty($filteredCharacteristics))
                                    — ไม่มีคุณลักษณะสำหรับประเภทนี้ —
                                @else
                                    — ไม่ผูกกับคุณลักษณะพื้นฐาน —
                                @endif
                            </option>
                            @if ($year !== '' && !empty($filteredCharacteristics))
                                @foreach ($filteredCharacteristics as $char)
                                    <option value="{{ $char['id'] }}">{{ $char['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3.5">
                        <label class="block text-xs font-bold text-ink mb-[5px]">ชื่อการเปรียบเทียบ *</label>
                        <input wire:model="name" placeholder="เช่น เปรียบเทียบ Notebook สำนักงาน ปี 2569" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                        @error('name')<div class="text-xs text-[#DC2626] mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3.5">
                        <label class="block text-xs font-bold text-ink mb-[5px]">หมายเหตุ</label>
                        <textarea wire:model="notes" rows="3" placeholder="หมายเหตุเพิ่มเติม..." class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed"></textarea>
                    </div>
                @else
                    @php $i = (int) substr($tab, 1); $locked = ! empty($vendors[$i]['product_id']); @endphp
                    <div wire:key="vendor-panel-{{ $i }}">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b-[1.5px] border-[#EFF6FF]">
                        <span class="text-sm font-extrabold text-navy">ข้อมูลบริษัท / ผู้ขาย (ผู้ผลิตที่ {{ $i + 1 }})</span>
                        @if (count($vendors) > 3)
                            <button type="button" wire:click="removeVendor({{ $i }})"
                                wire:confirm="ต้องการลบผู้ผลิตรายนี้ออกจากการเปรียบเทียบใช่ไหม?"
                                class="flex items-center gap-1 text-[12px] font-semibold text-[#DC2626] hover:underline">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                ลบผู้ผลิตนี้
                            </button>
                        @endif
                    </div>
                    @php $others = collect($vendors)->forget($i)->pluck('product_id')->filter()->all(); @endphp
                    <div class="mb-3.5 bg-surface-alt border-[1.5px] border-line rounded-lg p-3">
                        <label class="block text-xs font-bold text-ink mb-[5px]">เลือกจากสินค้าในระบบ (ดึงข้อมูลอัตโนมัติ)</label>
                        <select wire:model.live="vendors.{{ $i }}.product_id" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm bg-surface">
                            <option value="">— เลือกสินค้า / กรอกเอง —</option>
                            @foreach ($this->filteredProducts as $p)
                                @if ($p->id === ($vendors[$i]['product_id'] ?? '') || ! in_array($p->id, $others))
                                    <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->model }} — {{ number_format((float) $p->price) }} ฿</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-[11px] text-faint mt-1">เลือกแล้วระบบจะเติม แบรนด์ / รุ่น / ราคา / สเปค ให้อัตโนมัติ — ชื่อบริษัทและราคาเสนอจะล็อกตามข้อมูลสินค้า</p>
                        @if ($locked)
                            <div class="flex gap-3 mt-1.5">
                                <button type="button" wire:click="clearVendorProduct({{ $i }})"
                                    class="text-[11px] font-semibold text-[#DC2626] hover:underline">✕ ล้าง / เปลี่ยนสินค้า</button>
                                <button type="button" wire:click="refreshVendorProduct({{ $i }})"
                                    class="text-[11px] font-semibold text-[#2563EB] hover:underline">🔄 อ่านข้อมูลสินค้าใหม่</button>
                            </div>
                        @endif
                    </div>
                    <div class="mb-3.5">
                        <label class="block text-xs font-bold text-ink mb-[5px]">ชื่อบริษัท / ร้านค้า @if ($locked)<span class="text-[10px] font-normal text-faint">(ล็อกจากสินค้า)</span>@endif</label>
                        <input wire:model="vendors.{{ $i }}.name" placeholder="เช่น บริษัท เอบีซี จำกัด" @readonly($locked)
                            class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm {{ $locked ? 'bg-line-soft text-muted cursor-not-allowed' : '' }}">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">แบรนด์</label>
                            <input wire:model="vendors.{{ $i }}.brand" placeholder="เช่น ASUS" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                        </div>
                        <div class="mb-3.5">
                            <label class="block text-xs font-bold text-ink mb-[5px]">ราคาเสนอ (บาท) @if ($locked)<span class="text-[10px] font-normal text-faint">(ล็อก = ราคากลาง)</span>@endif</label>
                            <input type="number" wire:model="vendors.{{ $i }}.price" placeholder="0" @readonly($locked)
                                class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm {{ $locked ? 'bg-line-soft text-muted cursor-not-allowed' : '' }}">
                        </div>
                    </div>
                    <div class="mb-3.5">
                        <label class="block text-xs font-bold text-ink mb-[5px]">รุ่น / โมเดล</label>
                        <input wire:model="vendors.{{ $i }}.model" placeholder="เช่น Vivobook 16 (X1607CA)" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-sm">
                    </div>

                    <div class="text-sm font-extrabold text-navy mb-3 mt-5 pb-2 border-b-[1.5px] border-[#EFF6FF]">คุณลักษณะพื้นฐานที่เสนอ</div>
                    <div class="text-xs text-faint bg-surface-alt px-3 py-2 rounded-lg mb-3.5">เลือกสินค้าด้านบนเพื่อดึงสเปคอัตโนมัติ หรือเพิ่มรายการสเปคเองด้านล่าง</div>
                    @forelse ($fieldKeys as $field)
                        <div class="mb-3.5">
                            <label class="flex items-center justify-between text-xs font-bold text-ink mb-[5px]">
                                <span>{{ $field }}</span>
                                <button type="button" wire:click="removeSpecField({{ $i }}, @js($field))" title="ลบรายการนี้"
                                    class="text-faint hover:text-[#DC2626] transition-colors">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </label>
                            <textarea wire:model="vendors.{{ $i }}.specs.{{ $field }}" rows="2" placeholder="สเปคที่เสนอสำหรับ {{ $field }}" class="w-full px-3 py-[9px] border-[1.5px] border-line rounded-lg text-[13px] resize-y leading-relaxed"></textarea>
                        </div>
                    @empty
                        <div class="text-xs text-faint text-center py-4">ยังไม่มีรายการสเปค — เลือกสินค้าหรือเพิ่มรายการด้านล่าง</div>
                    @endforelse

                    {{-- เพิ่มรายการสเปคเอง --}}
                    <div class="flex gap-2 mt-2 pt-3 border-t border-line-soft">
                        <input wire:model="newSpecField.{{ $i }}" wire:keydown.enter.prevent="addSpecField({{ $i }})" type="text"
                            placeholder="ชื่อรายการสเปค เช่น Processor" class="flex-1 px-3 py-[7px] border-[1.5px] border-line rounded-lg text-[13px]">
                        <button type="button" wire:click="addSpecField({{ $i }})"
                            class="px-3 py-[7px] border-[1.5px] border-navy text-navy bg-surface rounded-lg text-[13px] font-semibold whitespace-nowrap hover:bg-navy hover:text-white transition-colors">
                            + เพิ่มรายการ
                        </button>
                    </div>
                    </div>
                @endif
            </div>

            <div class="px-[26px] py-3.5 border-t border-line flex justify-between items-center">
                <div>@if ($errors->any())<span class="text-[#DC2626] text-[13px] font-semibold">กรุณากรอกข้อมูลที่จำเป็น</span>@endif</div>
                <div class="flex gap-2.5">
                    <button wire:click="close" class="px-[22px] py-[9px] border-[1.5px] border-line bg-surface rounded-lg text-sm text-ink font-semibold">ยกเลิก</button>
                    <button wire:click="save" class="flex items-center gap-1.5 px-[22px] py-[9px] border-none bg-navy text-white rounded-lg text-sm font-bold">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ $editingId ? 'บันทึกการแก้ไข' : 'สร้างการเปรียบเทียบ' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

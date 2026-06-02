<div class="p-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-[22px] font-extrabold text-ink">จัดการสิทธิ์การใช้งาน</h1>
            <p class="text-sm text-faint mt-0.5">กำหนดสิทธิ์ต่อ action (ดู / เพิ่ม / แก้ไข / ลบ / Import / Export) ต่อแต่ละ Role — admin ได้ทุกสิทธิ์เสมอ</p>
        </div>
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
            class="flex items-center gap-2 px-4 py-2 bg-navy text-white rounded-lg text-sm font-bold disabled:opacity-60">
            <svg wire:loading.remove wire:target="save" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            <svg wire:loading wire:target="save" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="animate-spin"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            บันทึกสิทธิ์
        </button>
    </div>

    {{-- Role Tabs --}}
    <div class="flex gap-1 mb-0 border-b border-line">
        @foreach ($roles as $role)
            <button wire:click="setTab('{{ $role->slug }}')"
                class="px-4 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 transition
                    {{ $activeTab === $role->slug
                        ? 'bg-surface border-line text-ink'
                        : 'bg-surface-alt border-transparent text-muted hover:text-ink' }}">
                {{ $role->name }}
                @if ($role->slug === 'admin')
                    <span class="ml-1 text-[10px] px-1 py-0.5 rounded font-bold" style="background:#EFF6FF;color:#1D4ED8">🔒</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Matrix per active role --}}
    @foreach ($roles as $role)
        @if ($activeTab === $role->slug)
            <div class="border border-t-0 border-line rounded-b-xl rounded-tr-xl bg-surface overflow-x-auto" wire:key="tab-{{ $role->slug }}">
                <table class="w-full text-sm">
                    <thead class="bg-surface-alt border-b border-line">
                        <tr>
                            <th class="px-4 py-3 font-bold text-ink text-left min-w-[200px]">ส่วนการใช้งาน</th>
                            @foreach ($actions as $actionKey => $actionLabel)
                                <th class="px-3 py-3 font-bold text-ink text-center w-20">{{ $actionLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menuKeys as $menuKey => $menuLabel)
                            <tr class="border-b border-line hover:bg-surface-alt/50 transition" wire:key="row-{{ $role->slug }}-{{ $menuKey }}">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-ink text-[13px]">{{ $menuLabel }}</div>
                                    <div class="text-[11px] text-faint font-mono">{{ $menuKey }}</div>
                                </td>
                                @foreach ($actions as $actionKey => $actionLabel)
                                    @php
                                        $isImportCol = $actionKey === 'can_import';
                                        $isExportCol = $actionKey === 'can_export';
                                        $notApplicable = ($isImportCol && !in_array($menuKey, $importable))
                                                      || ($isExportCol && !in_array($menuKey, $exportable));
                                    @endphp
                                    <td class="px-3 py-3 text-center" wire:key="cell-{{ $role->id }}-{{ $menuKey }}-{{ $actionKey }}">
                                        @if ($role->slug === 'admin' || $notApplicable)
                                            {{-- Admin หรือ action ไม่รองรับ section นี้ --}}
                                            <div class="flex items-center justify-center">
                                                <input type="checkbox"
                                                    @checked(!$notApplicable)
                                                    disabled
                                                    class="w-4 h-4 rounded {{ $notApplicable ? 'opacity-20' : 'opacity-50' }} cursor-not-allowed accent-[#005fbf]" />
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center">
                                                <input type="checkbox"
                                                    wire:model="matrix.{{ $role->id }}.{{ $menuKey }}.{{ $actionKey }}"
                                                    class="w-4 h-4 rounded cursor-pointer accent-[#005fbf]" />
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

    <div class="mt-4 text-[12px] text-faint flex items-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        การเปลี่ยนแปลงจะมีผลทันทีหลังกดบันทึก — เซลล์สีเทา (—) = action นั้นไม่รองรับสำหรับ section นี้
    </div>
</div>

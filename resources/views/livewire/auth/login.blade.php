<div class="min-h-screen flex flex-col bg-canvas font-sans">
    {{-- ═══ HEADER BAR ═══ --}}
    <header class="w-full px-6 py-3.5 flex items-center gap-3.5" style="background:linear-gradient(135deg,#1B3A6B 0%,#1e4d99 60%,#2563EB 100%)">
        <div class="flex items-center gap-3">
            <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                <rect width="48" height="48" rx="11" fill="white" fill-opacity="0.15"/>
                <path d="M10 30 L16 18 L22 26 L28 14 L34 22 L38 18" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="38" cy="18" r="3" fill="#60A5FA"/>
            </svg>
            <div>
                <div class="text-white font-extrabold text-lg leading-tight">ระบบ IT PRICE</div>
                <div class="text-white/50 text-[11px]">Price Reference Management</div>
            </div>
        </div>
    </header>

    {{-- ═══ MAIN CONTENT ═══ --}}
    <div class="flex-1 flex flex-col md:flex-row gap-6 p-5 md:p-8 max-w-[1200px] mx-auto w-full">

        {{-- ── LEFT: Login Form ── --}}
        <div class="w-full md:w-[380px] shrink-0">
            <div class="bg-surface rounded-2xl p-8 md:p-10" style="box-shadow:0 4px 32px rgba(0,0,0,0.08)">
                <h2 class="text-[24px] font-extrabold text-ink mb-1">เข้าสู่ระบบ</h2>
                <p class="text-faint text-sm mb-7">กรุณาใส่ชื่อผู้ใช้และรหัสผ่าน</p>

                <form wire:submit="login">
                    {{-- Username --}}
                    <div class="mb-4">
                        <label class="block text-[13px] font-bold text-ink mb-[6px]">User name</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-faint pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <input type="text" wire:model="username" placeholder="กรอกชื่อผู้ใช้" autofocus
                                class="w-full py-[10px] pr-3 pl-10 border-[1.5px] border-line rounded-lg text-sm outline-none text-ink placeholder:text-faint/60">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label class="block text-[13px] font-bold text-ink mb-[6px]">Password</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-faint pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input type="password" wire:model="password" placeholder="กรอกรหัสผ่าน"
                                class="w-full py-[10px] pr-3 pl-10 border-[1.5px] border-line rounded-lg text-sm outline-none text-ink placeholder:text-faint/60">
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <label class="flex items-center gap-2 mb-5 cursor-pointer select-none">
                        <input type="checkbox" wire:model="remember"
                            class="w-4 h-4 rounded border-line text-navy accent-[#1B3A6B] cursor-pointer">
                        <span class="text-sm text-muted">จดจำฉัน</span>
                    </label>

                    {{-- Error --}}
                    @error('username')
                        <div class="flex items-center gap-2 text-[#DC2626] text-sm bg-[#FEF2F2] border border-[#FECACA] rounded-lg px-3.5 py-2.5 mb-4">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Submit --}}
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-[11px] bg-navy text-white border-none rounded-lg text-[15px] font-bold cursor-pointer disabled:opacity-70 transition-all">
                        <span wire:loading.remove wire:target="login">ลงชื่อเข้าใช้</span>
                        <span wire:loading wire:target="login">กำลังเข้าสู่ระบบ...</span>
                    </button>
                </form>

                {{-- Test accounts (local dev only) --}}

            </div>
        </div>

        {{-- ── RIGHT: Welcome + Module Cards ── --}}
        <div class="flex-1 hidden md:flex flex-col">
            <div class="mb-6">
                <h1 class="text-[28px] font-extrabold text-ink leading-tight mb-1.5">ยินดีต้อนรับเข้าสู่ระบบ</h1>
                <p class="text-muted text-[15px]">ระบบจัดการข้อมูลราคากลางอุปกรณ์ IT สำหรับการจัดซื้อจัดจ้าง</p>
            </div>

            <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 flex-1 content-start">
                {{-- Card 1: สินค้าราคากลาง --}}
                <div class="bg-surface rounded-xl p-5 border border-line hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 rounded-bl-[40px] opacity-[0.07]" style="background:#059669"></div>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background:#ECFDF5">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/>
                        </svg>
                    </div>
                    <div class="text-sm font-extrabold text-ink mb-1">สินค้าราคากลาง</div>
                    <p class="text-[12px] text-muted leading-relaxed">ค้นหา เพิ่ม แก้ไขข้อมูลสินค้าและราคาอ้างอิง</p>
                    <div class="mt-3 flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:#ECFDF5;color:#059669">หลัก</span>
                    </div>
                </div>

                {{-- Card 2: เทียบราคา 3 เจ้า --}}
                <div class="bg-surface rounded-xl p-5 border border-line hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 rounded-bl-[40px] opacity-[0.07]" style="background:#2563EB"></div>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background:#EFF6FF">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-6"/><path d="M21 3l-9 9"/><path d="M3 3l9 9"/>
                        </svg>
                    </div>
                    <div class="text-sm font-extrabold text-ink mb-1">เทียบราคา 3 เจ้า</div>
                    <p class="text-[12px] text-muted leading-relaxed">บันทึกและเปรียบเทียบราคาจากผู้ขาย 3 ราย</p>
                    <div class="mt-3 flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:#EFF6FF;color:#2563EB">รายงาน</span>
                    </div>
                </div>

                {{-- Card 3: คุณลักษณะพื้นฐาน --}}
                <div class="bg-surface rounded-xl p-5 border border-line hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 rounded-bl-[40px] opacity-[0.07]" style="background:#7C3AED"></div>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background:#F5F3FF">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                            <path d="M9 12h6"/><path d="M9 16h6"/>
                        </svg>
                    </div>
                    <div class="text-sm font-extrabold text-ink mb-1">คุณลักษณะพื้นฐาน</div>
                    <p class="text-[12px] text-muted leading-relaxed">จัดการ TOR / สเปคอ้างอิงสำหรับการจัดซื้อ</p>
                    <div class="mt-3 flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:#F5F3FF;color:#7C3AED">สเปค</span>
                    </div>
                </div>

                {{-- Card 4: เปรียบเทียบสินค้า --}}
                <div class="bg-surface rounded-xl p-5 border border-line hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 rounded-bl-[40px] opacity-[0.07]" style="background:#0891B2"></div>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background:#ECFEFF">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0891B2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="18" rx="1"/>
                        </svg>
                    </div>
                    <div class="text-sm font-extrabold text-ink mb-1">เปรียบเทียบสินค้า</div>
                    <p class="text-[12px] text-muted leading-relaxed">วางสินค้าเทียบกัน Side-by-side พร้อม baseline</p>
                    <div class="mt-3 flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:#ECFEFF;color:#0891B2">วิเคราะห์</span>
                    </div>
                </div>

                {{-- Card 5: จัดการหมวดหมู่ --}}
                <div class="bg-surface rounded-xl p-5 border border-line hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 rounded-bl-[40px] opacity-[0.07]" style="background:#EA580C"></div>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background:#FFF7ED">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#EA580C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </div>
                    <div class="text-sm font-extrabold text-ink mb-1">จัดการหมวดหมู่</div>
                    <p class="text-[12px] text-muted leading-relaxed">เพิ่ม ลบ แก้ไขหมวดหมู่สินค้า IT</p>
                    <div class="mt-3 flex items-center gap-1.5">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:#FFF7ED;color:#EA580C">ตั้งค่า</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ FOOTER ═══ --}}
    <footer class="text-center py-4 text-[13px] text-faint border-t border-line bg-surface/60">
        ระบบจัดการข้อมูลราคากลาง &middot; Price Reference Management
    </footer>
</div>

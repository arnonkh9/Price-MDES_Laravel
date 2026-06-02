@php
    $user = auth()->user();
    $isProducts = request()->routeIs('products');
@endphp

<div class="no-print h-[60px] bg-surface border-b border-line flex items-center px-5 gap-3 fixed top-0 left-0 right-0 z-50 transition-[left] duration-200"
     :class="sidebarCollapsed ? 'md:left-[64px]' : 'md:left-[240px]'"
     style="box-shadow:0 1px 4px rgba(0,0,0,0.04)">
    {{-- Hamburger (mobile only) --}}
    <button @click="sidebarOpen = !sidebarOpen"
            class="md:hidden p-2 text-muted border-[1.5px] border-line bg-surface rounded-lg flex shrink-0">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    @if ($isProducts)
        {{-- Search --}}
        <form method="GET" action="{{ route('products') }}" class="flex items-center gap-[9px] bg-surface-alt border-[1.5px] border-line rounded-[10px] px-3 max-w-[150px] md:max-w-[200px] lg:max-w-[380px] flex-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" class="shrink-0">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหา Brand, Model, สเปค..."
                   class="border-none bg-transparent outline-none text-sm py-2.5 flex-1 text-ink min-w-0">
            <input type="hidden" name="category" value="all">
        </form>
    @endif

    <div class="flex items-center gap-2 ml-auto">
        @if ($isProducts)
            @if ($user->hasPermission('products', 'import'))
                <a href="{{ route('products', ['action' => 'import']) }}" wire:navigate title="นำเข้าจาก Excel"
                   class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] border-line bg-surface text-muted rounded-lg text-[13px] font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span class="hidden lg:inline">นำเข้า</span>
                </a>
            @endif

            @if ($user->hasPermission('products', 'export'))
                <a href="{{ route('products.export') }}" title="ส่งออก CSV"
                   class="flex items-center gap-1.5 px-[13px] py-[7px] border-[1.5px] border-line bg-surface text-muted rounded-lg text-[13px] font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span class="hidden lg:inline">ส่งออก</span>
                </a>
            @endif

            @if ($user->hasPermission('products', 'add'))
                <a href="{{ route('products', ['action' => 'new']) }}" wire:navigate
                   class="flex items-center gap-1.5 px-4 py-2 border-none bg-navy text-white rounded-lg text-[13px] font-bold">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    เพิ่มสินค้า
                </a>
            @endif
        @endif

        {{-- Global search button --}}
        <a href="{{ route('search') }}" wire:navigate title="ค้นหา"
           class="p-[7px] rounded-lg border-[1.5px] border-line bg-surface text-muted hover:text-ink transition shrink-0 {{ request()->routeIs('search') ? 'border-blue-400 text-blue-500' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </a>

        {{-- Dark mode toggle --}}
        <button @click="toggleDark()" title="สลับโหมดสี"
            class="p-[7px] rounded-lg border-[1.5px] border-line bg-surface text-muted hover:text-ink transition shrink-0">
            {{-- Sun icon (shown in dark mode → click to go light) --}}
            <svg x-show="darkMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            {{-- Moon icon (shown in light mode → click to go dark) --}}
            <svg x-show="!darkMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>

        {{-- User menu (always visible) --}}
        <div class="relative" x-data="{ open:false }">
            <button @click="open=!open" class="flex items-center gap-[9px] px-2.5 py-[5px] border-[1.5px] border-line rounded-[10px] bg-surface cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-extrabold text-sm shrink-0">{{ mb_substr($user->name, 0, 1) }}</div>
                <div class="text-left hidden sm:block">
                    <div class="text-[13px] font-bold text-ink leading-tight">{{ $user->name }}</div>
                    <div class="text-[11px] text-faint">{{ $user->department }}</div>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-cloak @click.outside="open=false" x-transition
                 class="absolute top-[calc(100%+8px)] right-0 bg-surface-raised border-[1.5px] border-line rounded-xl p-2 w-[200px] z-[200]" style="box-shadow:0 8px 24px rgba(0,0,0,0.1)">
                <div class="px-2.5 pt-2 pb-2.5">
                    <div class="text-sm font-bold text-ink">{{ $user->name }}</div>
                    <div class="text-xs text-muted">{{ $user->roleName() }}</div>
                </div>
                <div class="h-px bg-line-soft my-1"></div>
                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 w-full px-2.5 py-[9px] border-none bg-transparent text-ink cursor-pointer text-[13px] rounded-lg font-semibold hover:bg-surface-alt transition">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    ข้อมูลส่วนตัว
                </a>
                <div class="h-px bg-line-soft my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 w-full px-2.5 py-[9px] border-none bg-transparent text-[#DC2626] cursor-pointer text-[13px] rounded-lg font-semibold hover:bg-[#FEF2F2] dark:hover:bg-red-950/50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        ออกจากระบบ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

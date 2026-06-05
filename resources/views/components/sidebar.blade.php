@php
    use App\Models\Product;
    use App\Models\CharacteristicsTemplate;
    use App\Models\Comparison;
    use App\Support\CompareCart;
    use App\Support\Specs;

    $categories = Specs::categories();
    $colors = Specs::colorMap();
    $counts = Product::selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category')->toArray();
    $specCount = CharacteristicsTemplate::count();
    $cmpCount = Comparison::count();
    $compareCount = CompareCart::count();
    $cartActive = CompareCart::active();

    $curCat = request('category');
    $isList = request()->routeIs('products');
@endphp

<div class="no-print h-screen flex flex-col fixed left-0 top-0 overflow-y-auto z-[100] -translate-x-full md:translate-x-0 transition-[width,transform] duration-200 overflow-x-hidden border-r border-line"
     style="background-color: var(--color-sidebar);"
     :class="[{ 'translate-x-0': sidebarOpen }, sidebarCollapsed ? 'w-[64px]' : 'w-[240px]']">
    {{-- Brand --}}
    <div class="py-5 flex items-center gap-3 border-b border-line shrink-0 relative transition-[padding] duration-200"
         :class="sidebarCollapsed ? 'px-0 justify-center' : 'px-[18px]'">
        <svg width="34" height="34" viewBox="0 0 48 48" fill="none" class="shrink-0">
            <rect width="48" height="48" rx="11" fill="#0D9488" fill-opacity="0.12"/>
            <path d="M10 30 L16 18 L22 26 L28 14 L34 22 L38 18" stroke="#0D9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="38" cy="18" r="3" fill="#14B8A6"/>
        </svg>
        <div x-show="!sidebarCollapsed" x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="text-ink font-extrabold text-sm leading-tight whitespace-nowrap">Price Reference</div>
            <div class="text-faint text-[10px] mt-0.5 whitespace-nowrap">Price Reference System</div>
        </div>

    </div>

    {{-- User profile card --}}
    @php $u = auth()->user(); @endphp
    <a href="{{ route('profile') }}" wire:navigate
       class="shrink-0 flex items-center gap-2.5 mx-2.5 mt-3 rounded-xl border border-line bg-surface-alt hover:bg-surface transition cursor-pointer"
       :class="sidebarCollapsed ? 'justify-center px-0 py-2' : 'px-2.5 py-2.5'">
        <div class="w-9 h-9 rounded-full bg-navy text-white flex items-center justify-center font-extrabold text-sm shrink-0">{{ mb_substr($u->name, 0, 1) }}</div>
        <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
            <div class="text-[13px] font-bold text-ink leading-tight truncate">{{ $u->name }}</div>
            <div class="text-[11px] text-muted truncate">{{ $u->roleName() }}</div>
        </div>
        <svg x-show="!sidebarCollapsed" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-faint shrink-0"><polyline points="9 18 15 12 9 6"/></svg>
    </a>

    <nav class="px-2.5 py-2.5 flex-1">

         {{-- Desktop collapse toggle button --}}
        <button @click="toggleSidebar()" title="พับ/ขยายเมนู"
                class="hidden md:flex absolute -right-3 top-1/2 -translate-y-1/2
                       w-6 h-6 rounded-full items-center justify-center transition z-10 shrink-0 bg-surface border border-line text-muted shadow-sm hover:text-ink">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                 :class="sidebarCollapsed ? 'rotate-180' : ''" class="transition-transform duration-200">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
         {{-- Desktop collapse toggle button --}}
         
        <div x-show="!sidebarCollapsed" class="text-faint text-[12px] font-bold tracking-[0.1em] uppercase px-2 pt-3.5 pb-[5px]">เมนูหลัก</div>
        <div x-show="sidebarCollapsed" class="pt-3.5 pb-[5px]"></div>

        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <x-slot:icon>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>

        </x-slot:icon>
            แดชบอร์ด
        </x-nav-link>
        <x-nav-link :href="route('products')" :active="request()->routeIs('products')">
            <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-check-fill" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10.5 3.5a2.5 2.5 0 0 0-5 0V4h5zm1 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0m-.646 5.354a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/>
</svg>

        </x-slot:icon>
            สินค้า
        </x-nav-link>
        @if (auth()->user()->canSeeMenu('reports'))
            <x-nav-link :href="route('reports')" :active="request()->routeIs('reports')">
                <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg></x-slot:icon>
                รายงาน
            </x-nav-link>
        @endif
        <div x-data="{ open: {{ $isList ? 'true' : 'false' }} }">


            <div x-show="open && !sidebarCollapsed"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">

            </div>
        </div>

        @if (auth()->user()->canSeeMenu('specs') || auth()->user()->canSeeMenu('comparisons') || auth()->user()->canSeeMenu('guidelines') || auth()->user()->canSeeMenu('recommendations'))
            <div x-show="!sidebarCollapsed" class="text-faint text-[12px] font-bold tracking-[0.1em] uppercase px-2 pt-3.5 pb-[5px]">คุณลักษณะพื้นฐาน</div>
            <div x-show="sidebarCollapsed" class="pt-2"></div>
        @endif
        @if (auth()->user()->canSeeMenu('specs'))
            <x-nav-link :href="route('specs')" :active="request()->routeIs('specs')" :badge="$specCount ?: null">
                <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
                </svg>
            </x-slot:icon>
                จัดการคุณลักษณะพื้นฐาน
            </x-nav-link>
        @endif
        @if (auth()->user()->canSeeMenu('comparisons'))
            <x-nav-link :href="route('comparisons')" :active="request()->routeIs('comparisons')" :badge="$cmpCount ?: null">
                <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="3" y1="20" x2="21" y2="20"/></svg></x-slot:icon>
                เปรียบเทียบกับราคา
            </x-nav-link>
        @endif
        @if (auth()->user()->canSeeMenu('guidelines'))
            <x-nav-link :href="route('guidelines')" :active="request()->routeIs('guidelines')">
                <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></x-slot:icon>
                แนวทางการพิจารณา
            </x-nav-link>
        @endif
        @if (auth()->user()->canSeeMenu('recommendations'))
            <x-nav-link :href="route('recommendations')" :active="request()->routeIs('recommendations')">
                <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></x-slot:icon>
                ข้อแนะนำประกอบ
            </x-nav-link>
        @endif

        @if ($cartActive && auth()->user()->canSeeMenu('compare'))
            <div x-show="!sidebarCollapsed" class="text-faint text-[10px] font-bold tracking-[0.1em] uppercase px-2 pt-3.5 pb-[5px]">เปรียบเทียบ</div>
            <div x-show="sidebarCollapsed" class="pt-2"></div>
            <x-nav-link :href="route('compare')" :active="request()->routeIs('compare')" :badge="$compareCount ?: null" badgeColor="#EF4444">
                <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></x-slot:icon>
                เปรียบเทียบสินค้า
            </x-nav-link>
        @endif

        @php
            $user = auth()->user();
            $canSeeUsers       = $user->canSeeMenu('users');
            $canSeeCategories  = $user->canSeeMenu('categories');
            $canSeeBrands      = $user->canSeeMenu('brands');
            $canSeeRoles       = $user->canSeeMenu('roles');
            $canSeePermissions = $user->canSeeMenu('permissions');
            $hasAdminSection   = $canSeeUsers || $canSeeCategories || $canSeeBrands || $canSeeRoles || $canSeePermissions || true; // audit-log visible to all
        @endphp
        @if ($hasAdminSection)
            <div x-show="!sidebarCollapsed" class="text-faint text-[12px] font-bold tracking-[0.1em] uppercase px-2 pt-3.5 pb-[5px]">จัดการระบบ</div>
            <div x-show="sidebarCollapsed" class="pt-2"></div>
            @if ($canSeeUsers)
                <x-nav-link :href="route('users')" :active="request()->routeIs('users')">
                    <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></x-slot:icon>
                    จัดการผู้ใช้
                </x-nav-link>
            @endif
            @if ($canSeeCategories)
                <x-nav-link :href="route('categories')" :active="request()->routeIs('categories')">
                    <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></x-slot:icon>
                    จัดการหมวดหมู่
                </x-nav-link>
            @endif
            @if ($canSeeBrands)
                <x-nav-link :href="route('brands')" :active="request()->routeIs('brands')">
                    <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg></x-slot:icon>
                    จัดการแบรนด์
                </x-nav-link>
            @endif
            @if ($canSeeRoles)
                <x-nav-link :href="route('roles')" :active="request()->routeIs('roles')">
                    <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></x-slot:icon>
                    จัดการ Role
                </x-nav-link>
            @endif
            @if ($canSeePermissions)
                <x-nav-link :href="route('permissions')" :active="request()->routeIs('permissions')">
                    <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></x-slot:icon>
                    จัดการสิทธิ์เมนู
                </x-nav-link>
            @endif
            <x-nav-link :href="route('audit-log')" :active="request()->routeIs('audit-log')">
                <x-slot:icon><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></x-slot:icon>
                ประวัติการแก้ไข
            </x-nav-link>
        @endif
    </nav>

    <div class="py-3.5 border-t border-line flex justify-center transition-[padding] duration-200"
         :class="sidebarCollapsed ? 'px-0' : 'px-[18px] justify-start'">
        <div x-show="!sidebarCollapsed" class="text-faint text-[11px]">v1.0</div>
        <div x-show="sidebarCollapsed" class="text-faint text-[11px]">•</div>
    </div>
</div>

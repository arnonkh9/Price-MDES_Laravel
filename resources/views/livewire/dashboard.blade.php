@php
    $fmt = fn ($n) => $n ? number_format((float) $n) : '—';
    $actionLabel = fn ($a) => match($a) {
        'add'    => ['เพิ่ม', 'bg-emerald-100 text-emerald-700'],
        'edit'   => ['แก้ไข', 'bg-blue-100 text-blue-700'],
        'delete' => ['ลบ', 'bg-red-100 text-red-700'],
        default  => [$a, 'bg-surface-alt text-muted'],
    };
@endphp

<div class="px-4 md:px-7 pt-4 md:pt-7 pb-10">

    {{-- Header --}}
    <div class="flex items-baseline gap-4 mb-6">
        <h2 class="text-[22px] font-extrabold text-ink m-0">ภาพรวมระบบ</h2>
        @php
            $thMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
            $todayStr = now()->day . ' ' . $thMonths[now()->month - 1] . ' ' . (now()->year + 543);
        @endphp
        <span class="text-[13px] text-faint">ข้อมูล ณ วันที่ {{ $todayStr }}</span>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi-card color="#1B3A6B" bg="#EFF6FF" :num="$total" unit="รายการ" label="สินค้าทั้งหมด">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </x-kpi-card>
        <x-kpi-card color="#059669" bg="#F0FFF4" :num="$fmt($avg)" unit="บาท" label="ราคากลางเฉลี่ย">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </x-kpi-card>
        <x-kpi-card color="#7C3AED" bg="#FAF5FF" :num="$catCount" unit="หมวด" label="ประเภทสินค้า">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </x-kpi-card>
        <x-kpi-card color="#D97706" bg="#FFFBEB" :num="$editCount" unit="ครั้ง" label="การแก้ไขทั้งหมด">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </x-kpi-card>
    </div>

    {{-- Chart data passed via window variables (avoids Alpine expression parser choking on JSON) --}}
    <script>
        window._dashBarData   = @json($barChartData);
        window._dashTrendData = @json($trendChartData);
    </script>

    {{-- Row 1: Category bar chart + Price trend --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

        {{-- Bar chart: Products per category --}}
        <div class="bg-surface rounded-[14px] border border-line overflow-hidden">
            <div class="px-5 pt-[18px] pb-3.5 border-b border-line-soft">
                <h3 class="text-sm font-extrabold text-ink m-0">จำนวนสินค้าตามหมวดหมู่</h3>
            </div>
            <div class="px-4 py-4">
                @if (collect($barChartData['labels'])->isNotEmpty())
                    <canvas style="max-height:280px"
                        x-data="{
                            _chart: null,
                            init() {
                                const isDark = document.documentElement.classList.contains('dark');
                                const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
                                const textColor = isDark ? '#94a3b8' : '#64748b';
                                this._chart = new Chart(this.$el, {
                                    type: 'bar',
                                    data: window._dashBarData,
                                    options: {
                                        indexAxis: 'y',
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' รายการ' } }
                                        },
                                        scales: {
                                            x: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                                            y: { grid: { display: false }, ticks: { color: textColor, font: { size: 12 } } }
                                        }
                                    }
                                });
                            },
                            destroy() { this._chart?.destroy(); }
                        }"
                    ></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-[200px] text-muted text-sm gap-2">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="12" width="4" height="9"/><rect x="10" y="6" width="4" height="15"/><rect x="17" y="3" width="4" height="18"/></svg>
                        <span>ยังไม่มีข้อมูลสินค้า</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Line chart: Price trend by year --}}
        <div class="bg-surface rounded-[14px] border border-line overflow-hidden">
            <div class="px-5 pt-[18px] pb-3.5 border-b border-line-soft">
                <h3 class="text-sm font-extrabold text-ink m-0">แนวโน้มราคากลางเฉลี่ยตามปี</h3>
            </div>
            <div class="px-4 py-4">
                @if (count($trendChartData['labels']) > 0)
                    <canvas style="max-height:280px"
                        x-data="{
                            _chart: null,
                            init() {
                                const isDark = document.documentElement.classList.contains('dark');
                                const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
                                const textColor = isDark ? '#94a3b8' : '#64748b';
                                this._chart = new Chart(this.$el, {
                                    type: 'line',
                                    data: window._dashTrendData,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: { callbacks: { label: ctx => ' ' + ctx.raw.toLocaleString() + ' บาท' } }
                                        },
                                        scales: {
                                            x: { grid: { color: gridColor }, ticks: { color: textColor } },
                                            y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => v.toLocaleString() } }
                                        }
                                    }
                                });
                            },
                            destroy() { this._chart?.destroy(); }
                        }"
                    ></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-[200px] text-muted text-sm gap-2">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>ยังไม่มีข้อมูลราคา</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Row 2: Recent products + Activity feed --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Recent products --}}
        <div class="bg-surface rounded-[14px] border border-line overflow-hidden">
            <div class="px-5 pt-[18px] pb-3.5 border-b border-line-soft flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-ink m-0">รายการล่าสุด</h3>
                <a href="{{ route('products') }}" wire:navigate class="text-xs text-blue-600 hover:underline">ดูทั้งหมด →</a>
            </div>
            <div class="flex flex-col">
                @forelse ($recent as $i => $p)
                    @php $color = $colors[$p->category] ?? '#64748B'; @endphp
                    <a href="{{ route('products', ['view' => $p->id]) }}" wire:navigate
                       class="flex items-center gap-3.5 px-5 py-3 border-b border-line-soft cursor-pointer hover:bg-surface-alt">
                        <div class="text-lg font-extrabold w-7 shrink-0" style="color:{{ $color }}">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-[3px]">
                                <span class="text-[11px] font-bold px-[7px] py-0.5 rounded" style="background:{{ $color }}18;color:{{ $color }}">{{ \App\Support\Specs::label($p->category) }}</span>
                                <span class="text-xs text-muted">{{ $p->brand }}</span>
                            </div>
                            <div class="text-[13px] font-bold text-ink overflow-hidden text-ellipsis whitespace-nowrap">{{ $p->model }}</div>
                        </div>
                        <div class="text-sm font-extrabold text-price shrink-0">{{ $fmt($p->price) }} ฿</div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center h-[140px] text-muted text-sm gap-2">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
                        <span>ยังไม่มีสินค้า</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Activity feed --}}
        <div class="bg-surface rounded-[14px] border border-line overflow-hidden">
            <div class="px-5 pt-[18px] pb-3.5 border-b border-line-soft flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-ink m-0">กิจกรรมล่าสุด</h3>
                <a href="{{ route('audit-log') }}" wire:navigate class="text-xs text-blue-600 hover:underline">ดูทั้งหมด →</a>
            </div>
            <div class="flex flex-col divide-y divide-line-soft">
                @forelse ($activities as $act)
                    @php [$actionText, $actionClass] = $actionLabel($act['action']); @endphp
                    <div class="flex items-start gap-3 px-5 py-3">
                        {{-- type icon --}}
                        <div class="mt-0.5 shrink-0 w-7 h-7 rounded-full flex items-center justify-center
                            {{ $act['type'] === 'product' ? 'bg-blue-50 text-blue-500' : 'bg-purple-50 text-purple-500' }}">
                            @if ($act['type'] === 'product')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded {{ $actionClass }}">{{ $actionText }}</span>
                                <span class="text-[12px] font-semibold text-ink truncate">{{ $act['detail'] }}</span>
                            </div>
                            <div class="text-[11px] text-faint mt-0.5">
                                {{ $act['user'] ?? '-' }} · {{ $act['date'] }}
                                @if ($act['type'] === 'spec')
                                    <span class="ml-1 text-purple-400">คุณลักษณะ</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-[140px] text-muted text-sm gap-2">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>ยังไม่มีกิจกรรม</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

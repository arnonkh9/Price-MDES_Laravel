<div class="p-4 md:p-6 max-w-[1400px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-ink">รายงาน</h1>
            <p class="text-sm text-faint mt-0.5">{{ $report['title'] }}</p>
        </div>
        @if ($canExport)
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export.pdf', ['type' => $reportType, 'year' => $year, 'category' => $category]) }}"
                   class="flex items-center gap-1.5 px-[14px] py-[9px] border-[1.5px] border-line bg-surface text-ink rounded-lg text-sm font-bold hover:bg-surface-alt">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    PDF
                </a>
                <a href="{{ route('reports.export.excel', ['type' => $reportType, 'year' => $year, 'category' => $category]) }}"
                   class="flex items-center gap-1.5 px-[14px] py-[9px] border-none bg-price text-white rounded-lg text-sm font-bold hover:opacity-90">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Excel
                </a>
            </div>
        @endif
    </div>

    {{-- Report type tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($types as $key => $meta)
            <button wire:click="setType('{{ $key }}')"
                    class="px-4 py-2 rounded-lg text-sm font-bold border-[1.5px] transition-colors
                           {{ $reportType === $key ? 'bg-navy text-white border-navy' : 'bg-surface text-ink border-line hover:bg-surface-alt' }}">
                {{ $meta['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3 mb-5 bg-surface border border-line rounded-xl p-4">
        <div>
            <label class="block text-xs font-bold text-muted mb-1">ปี (พ.ศ.)</label>
            <select wire:model.live="year" class="px-3 py-2 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                <option value="all">— ทุกปี —</option>
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        @if (in_array($reportType, ['price', 'comparison', 'spec']))
            <div>
                <label class="block text-xs font-bold text-muted mb-1">หมวดหมู่</label>
                <select wire:model.live="category" class="px-3 py-2 border-[1.5px] border-line rounded-lg text-sm bg-surface">
                    <option value="all">— ทุกหมวดหมู่ —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        @foreach ($report['kpis'] as $kpi)
            <div class="bg-surface rounded-[14px] p-4 border border-line">
                <div class="text-[22px] font-extrabold text-ink leading-none mb-1.5">{{ $kpi['value'] }}</div>
                <div class="text-xs text-faint font-medium">{{ $kpi['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Chart --}}
    @if (! empty($report['chart']))
        <div class="bg-surface rounded-[14px] border border-line overflow-hidden mb-5">
            <div class="px-5 pt-[18px] pb-3.5 border-b border-line-soft">
                <h3 class="text-sm font-extrabold text-ink m-0">{{ $report['chart']['datasets'][0]['label'] ?? 'กราฟสรุป' }}</h3>
            </div>
            <div class="px-4 py-4">
                <script>window._reportChart = @json($report['chart']);</script>
                <canvas wire:ignore style="max-height:320px"
                    x-data="{
                        _chart: null,
                        init() {
                            const isDark = document.documentElement.classList.contains('dark');
                            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
                            const textColor = isDark ? '#94a3b8' : '#64748b';
                            this._chart = new Chart(this.$el, {
                                type: window._reportChart.type,
                                data: window._reportChart,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
                                        y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => Number(v).toLocaleString() } }
                                    }
                                }
                            });
                        },
                        destroy() { this._chart?.destroy(); }
                    }"
                ></canvas>
            </div>
        </div>
    @endif

    {{-- Data table --}}
    <div class="bg-surface rounded-[14px] border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-alt border-b border-line">
                    <tr>
                        @foreach ($report['columns'] as $col)
                            <th class="px-4 py-3 font-bold text-muted text-{{ $col['align'] ?? 'left' }} whitespace-nowrap">{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    @forelse ($report['rows'] as $row)
                        <tr class="hover:bg-[#00000005] dark:hover:bg-white/5">
                            @foreach ($report['columns'] as $col)
                                <td class="px-4 py-2.5 text-ink text-{{ $col['align'] ?? 'left' }} align-top">{{ $row[$col['key']] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columns']) }}" class="px-4 py-8 text-center text-faint">ไม่มีข้อมูลตามเงื่อนไขที่เลือก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

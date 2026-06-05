@props([
    'color' => '#0D9488',   // icon badge + accent color
    'bg' => '#F0FDFA',       // soft gradient end tint
    'num' => '',
    'unit' => '',
    'label' => '',
    'trend' => null,         // e.g. '+5' (omit → no trend row)
    'trendPct' => null,      // e.g. '10' (percent number, no sign)
    'trendLabel' => 'เดือนนี้',
    'trendDir' => 'up',      // 'up' | 'down'
])

<div class="rounded-[16px] p-5 border border-line bg-gradient-to-br from-surface flex flex-col gap-4 transition hover:shadow-md"
     style="--tw-gradient-to: {{ $bg }} var(--tw-gradient-to-position);">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 text-white shadow-sm" style="background:{{ $color }}">
            {{ $slot }}
        </div>
        <div class="text-[13px] font-semibold text-muted leading-tight">{{ $label }}</div>
    </div>

    <div class="text-[26px] font-extrabold leading-none text-ink">
        {{ $num }} <span class="text-sm font-semibold text-muted">{{ $unit }}</span>
    </div>

    @if (! is_null($trend))
        @php $up = $trendDir !== 'down'; @endphp
        <div class="flex items-center gap-1.5 text-[12px]">
            @if (! is_null($trendPct))
                <span class="font-bold {{ $up ? 'text-emerald-600' : 'text-red-500' }}">{{ $trendPct }}%</span>
                <span class="{{ $up ? 'text-emerald-600' : 'text-red-500' }}">{{ $up ? '▲' : '▼' }}</span>
            @endif
            <span class="text-muted font-medium">{{ $trend }} {{ $trendLabel }}</span>
        </div>
    @endif
</div>

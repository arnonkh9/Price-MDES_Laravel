@props(['color' => '#1B3A6B', 'bg' => '#EFF6FF', 'num' => '', 'unit' => '', 'label' => ''])

<div class="bg-surface rounded-[14px] p-5 border border-line flex gap-4 items-center">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $bg }};color:{{ $color }}">
        {{ $slot }}
    </div>
    <div>
        <div class="text-[26px] font-extrabold leading-none mb-1" style="color:{{ $color }}">
            {{ $num }} <span class="text-sm font-semibold">{{ $unit }}</span>
        </div>
        <div class="text-xs text-faint font-medium">{{ $label }}</div>
    </div>
</div>

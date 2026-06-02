@props([
    'href' => '#',
    'active' => false,
    'badge' => null,
    'badgeColor' => null,
    'icon' => null,
])

<a href="{{ $href }}" wire:navigate
   class="w-full flex items-center gap-[9px] px-2.5 py-2 rounded-lg cursor-pointer text-[13px] text-left transition mb-px
          {{ $active ? 'bg-white/[0.14] text-white' : 'text-white/65 hover:bg-white/[0.07]' }}"
   :class="sidebarCollapsed ? 'justify-center !px-0 !gap-0' : ''">
    <span class="shrink-0 flex items-center">{{ $icon }}</span>
    <span x-show="!sidebarCollapsed" class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">{{ $slot }}</span>
    @if (! is_null($badge))
        <span x-show="!sidebarCollapsed"
              class="text-[11px] text-white px-[7px] py-px rounded-[10px] font-bold shrink-0"
              style="background:{{ $badgeColor ?? 'rgba(255,255,255,0.15)' }}">{{ $badge }}</span>
    @endif
</a>

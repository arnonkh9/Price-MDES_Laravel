@props([
    'href' => '#',
    'active' => false,
    'badge' => null,
    'badgeColor' => null,
    'icon' => null,
])

<a href="{{ $href }}" wire:navigate
   class="w-full flex items-center gap-[9px] px-2.5 py-2 rounded-lg cursor-pointer text-[13px] text-left transition mb-px font-medium
          {{ $active ? 'bg-navy text-white shadow-sm' : 'text-muted hover:bg-surface-alt hover:text-ink' }}"
   :class="sidebarCollapsed ? 'justify-center !px-0 !gap-0' : ''">
    <span class="shrink-0 flex items-center">{{ $icon }}</span>
    <span x-show="!sidebarCollapsed" class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">{{ $slot }}</span>
    @if (! is_null($badge))
        <span x-show="!sidebarCollapsed"
              class="text-[11px] px-[7px] py-px rounded-[10px] font-bold shrink-0 {{ $active ? 'text-white bg-white/25' : 'text-navy' }}"
              style="{{ $badgeColor ? 'background:'.$badgeColor.';color:#fff' : ($active ? '' : 'background:var(--color-sidebar-active-soft)') }}">{{ $badge }}</span>
    @endif
</a>

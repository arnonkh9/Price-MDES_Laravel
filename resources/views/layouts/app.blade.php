<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ระบบราคากลาง' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function(){
            var t=localStorage.getItem('theme');
            if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches))
                document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans bg-canvas text-ink">
    <div x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        darkMode: document.documentElement.classList.contains('dark'),
        toggleDark() {
            this.darkMode = !this.darkMode;
            document.documentElement.classList.toggle('dark', this.darkMode);
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
        }
    }" class="flex min-h-screen">
        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
             class="fixed inset-0 bg-black/40 z-[90] md:hidden" x-transition.opacity></div>

        <x-sidebar />

        <div class="flex-1 flex flex-col min-h-screen print-full transition-[margin] duration-200"
             :class="sidebarCollapsed ? 'md:ml-[64px]' : 'md:ml-[240px]'">
            <x-header />
            <main class="mt-[60px] flex-1 overflow-y-auto print-full">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Global toast --}}
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:toast.window="msg=$event.detail.message; type=$event.detail.type||'success'; show=true; clearTimeout(window.__t); window.__t=setTimeout(()=>show=false,3000)"
         x-show="show" x-cloak x-transition.opacity
         class="no-print fixed bottom-6 right-6 z-[500] flex items-center gap-2 text-white px-5 py-3 rounded-[10px] text-sm font-bold animate-slide-up"
         :style="`background:${type==='warn'?'#D97706':type==='info'?'#0369A1':'#1B3A6B'}`">
        <span x-text="msg"></span>
    </div>

    @livewireScripts
    <script>
        {{-- Fix Livewire update URI for XAMPP subdirectory deployment.
             getUpdateUri() strips the full root (including path prefix), returning
             /livewire/update instead of /price-MDES_V1/public/livewire/update.
             Patch the DOM attribute before the first Livewire request fires. --}}
        (function(){
            var base = '{{ rtrim(parse_url(config("app.url"), PHP_URL_PATH) ?? "", "/") }}';
            if (!base) return;
            var s = document.querySelector('script[data-update-uri]');
            if (!s) return;
            var uri = s.getAttribute('data-update-uri');
            if (uri && !uri.startsWith('http') && !uri.startsWith(base)) {
                s.setAttribute('data-update-uri', base + uri);
            }
        })();
    </script>
</body>
</html>

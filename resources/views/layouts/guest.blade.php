<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Price Reference Management' }}</title>
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
    {{ $slot }}
    @livewireScripts
    <script>
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

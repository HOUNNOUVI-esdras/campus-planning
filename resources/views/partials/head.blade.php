<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<script>
    function applyAppearance(appearance) {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', appearance === 'dark' || (appearance === 'system' && prefersDark));
    }

    function setAppearance(appearance) {
        localStorage.setItem('appearance', appearance);
        applyAppearance(appearance);
        document.querySelectorAll('[data-appearance]').forEach((button) => {
            button.setAttribute('aria-pressed', button.dataset.appearance === appearance);
        });
    }

    applyAppearance(localStorage.getItem('appearance') || 'system');
</script>

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])

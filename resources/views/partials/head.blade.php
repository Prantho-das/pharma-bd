<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<!-- Google Fonts: Noto Sans & Noto Sans Bengali -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&family=Noto+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles

<style>
    /* Global Font: Noto Sans & Noto Sans Bengali */
    html, body, button, input, select, textarea, span, p, h1, h2, h3, h4, h5, h6, table, th, td, a {
        font-family: 'Noto Sans', 'Noto Sans Bengali', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    }

    /* Clean inputs & controls without breaking micro/table utilities */
    input[type="text"]:not([class*="w-"]),
    input[type="number"]:not([class*="w-"]),
    input[type="email"]:not([class*="w-"]),
    input[type="password"]:not([class*="w-"]),
    input[type="search"]:not([class*="w-"]),
    input[type="date"]:not([class*="w-"]),
    input[type="month"]:not([class*="w-"]),
    input[type="datetime-local"]:not([class*="w-"]),
    select:not([class*="w-"]),
    textarea:not([class*="w-"]) {
        width: 100%;
    }

    input[type="text"],
    input[type="number"],
    input[type="email"],
    input[type="password"],
    input[type="search"],
    input[type="date"],
    input[type="month"],
    input[type="datetime-local"],
    select,
    textarea {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background-color: #ffffff;
        font-size: 0.8125rem;
        color: #0f172a;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="search"]:focus,
    input[type="date"]:focus,
    input[type="month"]:focus,
    input[type="datetime-local"]:focus,
    select:focus,
    textarea:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.20) !important;
        background-color: #ffffff !important;
    }

    input::placeholder,
    textarea::placeholder {
        color: #94a3b8;
    }

    input[type="checkbox"],
    input[type="radio"] {
        display: inline-block;
        width: 1.05rem;
        height: 1.05rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 0.25rem;
        accent-color: #0284c7;
        cursor: pointer;
        margin: 0;
    }

    input[type="radio"] {
        border-radius: 9999px;
    }
</style>
<script>
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
</script>

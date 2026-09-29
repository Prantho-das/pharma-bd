<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #thermal-receipt, #thermal-receipt * {
                visibility: visible;
            }
            #thermal-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                margin: 0;
                padding: 5mm;
            }
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden bg-slate-100 text-slate-800 antialiased font-sans">
    {{ $slot }}
    @livewireScripts
</body>
</html>

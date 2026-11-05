<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="SiMarket adalah tempat jual beli barang baru dan preloved untuk komunitas sekolah.">

        <title>{{ $title ?? 'SiMarket — Find It. Trade It. Own It.' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        {{ $slot ?? '' }}
        @yield('content')

        @livewireScripts
    </body>
</html>

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Halaman berisi data langsung: jangan tampilkan snapshot lama dari cache Turbo. --}}
    <meta name="turbo-cache-control" content="no-cache">
    <meta name="theme-color" content="#2A1F19">
    <title>@hasSection('title')@yield('title') · @endif{{ $namaToko ?? config('app.name') }}</title>
    <link rel="icon" href="{{ asset('img/altari.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full @yield('body-class')">
    <a href="#konten" class="sr-only z-[100] rounded-lg bg-ink-900 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Lewati ke konten utama</a>

    @yield('body')

    @include('partials.toast')
    @include('partials.confirm')
</body>
</html>

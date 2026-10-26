@extends('layouts.base')

@php
    $admin = auth()->user()->isAdmin();
    $menu = [
        'Utama' => [
            ['dashboard', 'Dashboard', 'dashboard', $admin],
            ['kasir.index', 'Kasir', 'kasir', true],
            ['kasir.tutup', 'Tutup Kasir', 'clipboard', true],
        ],
        'Transaksi' => [
            ['penjualan.index', 'Riwayat Penjualan', 'receipt', $admin],
            ['pengeluaran.index', 'Pengeluaran', 'wallet', true],
            ['modal.index', 'Modal & Kas Masuk', 'coins', $admin],
        ],
        'Inventori' => [
            ['produk.index', 'Produk & Resep', 'package', $admin],
            ['kategori.index', 'Kategori', 'tag', $admin],
            ['stok.index', 'Stok & Mutasi', 'boxes', $admin],
        ],
        'Analitik' => [
            ['laporan.index', 'Laporan Eksekutif', 'chart', $admin],
        ],
        'Pengaturan' => [
            ['user.index', 'Pengguna', 'users', $admin],
            ['setting.index', 'Toko & Nota', 'settings', $admin],
        ],
    ];
@endphp

@section('body')
<div x-data="{ nav: false }" class="min-h-full lg:flex">
    {{-- Sidebar --}}
    <div x-show="nav" x-cloak x-transition.opacity class="fixed inset-0 z-30 bg-ink-950/50 lg:hidden" @click="nav = false"></div>
    <aside class="no-print fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-ink-900 text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:translate-x-0"
           :class="nav && 'translate-x-0'" aria-label="Navigasi utama">
        <div class="flex items-center justify-between px-6 pb-2 pt-6">
            <a href="{{ route('dashboard') }}" class="block">
                <span class="block font-display text-2xl font-bold leading-none text-brand-200">{{ $namaToko ?? 'Altari' }}</span>
                <span class="mt-1 block text-xs text-ink-400">Point of Sale</span>
            </a>
            <button type="button" class="btn-icon text-ink-300 hover:bg-white/10 hover:text-white lg:hidden" @click="nav = false" aria-label="Tutup menu">
                <x-icon name="x" />
            </button>
        </div>

        <nav class="scroll-thin flex-1 overflow-y-auto px-3 pb-4">
            @foreach ($menu as $grup => $items)
                @php $items = array_filter($items, fn ($i) => $i[3]); @endphp
                @if ($items)
                    <p class="nav-group">{{ $grup }}</p>
                    @foreach ($items as [$route, $label, $icon])
                        @php
                            $aktif = request()->routeIs($route)
                                || (! str_starts_with($route, 'kasir.') && request()->routeIs(\Illuminate\Support\Str::before($route, '.') . '.*'));
                        @endphp
                        <a href="{{ route($route) }}" class="nav-link" @if ($aktif) aria-current="page" @endif>
                            <x-icon :name="$icon" class="h-[18px] w-[18px] shrink-0" />
                            {{ $label }}
                        </a>
                    @endforeach
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-xl px-3 py-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-bold">
                    {{ \Illuminate\Support\Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-ink-400">{{ $admin ? 'Administrator' : 'Kasir' }}</p>
                </div>
            </div>
            <div class="mt-1 grid grid-cols-2 gap-1">
                <a href="{{ route('user.profil') }}" class="nav-link justify-center text-xs" @if (request()->routeIs('user.profil')) aria-current="page" @endif>
                    <x-icon name="user" class="h-4 w-4" /> Profil
                </a>
                <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                    @csrf
                    <button type="submit" class="nav-link w-full justify-center text-xs">
                        <x-icon name="logout" class="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="no-print sticky top-0 z-20 border-b border-ink-100 bg-cream/90 backdrop-blur">
            <div class="flex items-center gap-3 px-4 py-3 lg:px-8 lg:py-4">
                <button type="button" class="btn-icon lg:hidden" @click="nav = true" aria-label="Buka menu">
                    <x-icon name="menu" />
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="truncate font-display text-xl font-bold lg:text-2xl">@yield('title')</h1>
                    @hasSection('subtitle')
                        <p class="hidden truncate text-sm text-ink-500 sm:block">@yield('subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @yield('actions')
                    @unless (request()->routeIs('kasir.*'))
                        <a href="{{ route('kasir.index') }}" class="btn-brand hidden sm:inline-flex">
                            <x-icon name="kasir" class="h-4 w-4" /> Buka Kasir
                        </a>
                    @endunless
                </div>
            </div>
        </header>

        <main id="konten" class="print-full mx-auto w-full max-w-[1400px] flex-1 px-4 py-6 lg:px-8 lg:py-8" tabindex="-1">
            @yield('content')
        </main>
    </div>
</div>
@endsection

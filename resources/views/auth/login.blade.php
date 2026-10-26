@extends('layouts.base')

@section('title', 'Masuk')

@section('body')
<main id="konten" class="grid min-h-full lg:grid-cols-2">
    <section class="relative hidden overflow-hidden bg-ink-900 p-12 text-white lg:flex lg:flex-col lg:justify-between" aria-hidden="true">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-brand-400/10 blur-3xl"></div>
        <p class="relative font-display text-3xl font-bold text-brand-200">{{ $namaToko ?? 'Altari' }}</p>
        <div class="relative max-w-md">
            <p class="font-display text-4xl font-semibold leading-tight">Kasir cepat, stok rapi, laporan jelas.</p>
            <p class="mt-4 text-ink-300">Satu aplikasi untuk mencatat pesanan, memantau bahan, dan membaca kinerja usaha setiap hari.</p>
        </div>
        <p class="relative text-sm text-ink-400">&copy; {{ date('Y') }} {{ $namaToko ?? 'Altari' }}</p>
    </section>

    <section class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
            <p class="mb-8 font-display text-3xl font-bold text-brand-700 lg:hidden">{{ $namaToko ?? 'Altari' }}</p>
            <h1 class="font-display text-3xl font-bold">Selamat datang</h1>
            <p class="mt-2 text-sm text-ink-500">Masuk untuk mulai melayani pelanggan.</p>

            @if (session('status'))
                <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" data-turbo="false" x-data="{ lihat: false, kirim: false }" @submit="kirim = true">
                @csrf
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="input @error('email') input-error @enderror" placeholder="nama@altari"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')
                        <p id="email-error" class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="label">Password</label>
                    <div class="relative">
                        <input id="password" :type="lihat ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password"
                               class="input pr-12 @error('password') input-error @enderror">
                        <button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-ink-400 hover:text-ink-700"
                                @click="lihat = !lihat" :aria-label="lihat ? 'Sembunyikan password' : 'Tampilkan password'" :aria-pressed="lihat.toString()">
                            <x-icon name="eye" class="h-5 w-5" />
                        </button>
                    </div>
                    @error('password')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-ink-600">
                    <input type="checkbox" name="remember" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                    Ingat saya di perangkat ini
                </label>
                <button type="submit" class="btn-primary w-full py-3" :disabled="kirim">
                    <span x-text="kirim ? 'Memproses…' : 'Masuk'">Masuk</span>
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </button>
            </form>
        </div>
    </section>
</main>
@endsection

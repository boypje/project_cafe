@extends('layouts.base')

@section('title', 'Masuk')

@php $toko = \App\Models\Setting::toko(); @endphp

@section('body')
<main id="konten" class="grid min-h-full lg:grid-cols-[1.1fr_1fr]">
    <figure class="relative hidden overflow-hidden bg-ink-900 lg:block">
        <img src="{{ asset('img/warung-altari.webp') }}" alt="Tampak depan Warung Altari"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-black/[.65]" aria-hidden="true"></div>
        <figcaption class="absolute inset-0 flex flex-col justify-end p-12 text-white">
            <p class="font-display text-5xl font-bold">{{ $toko->nama_usaha }}</p>
            @if ($toko->alamat)
                <p class="mt-3 max-w-md text-base text-white/75">{{ $toko->alamat }}{{ $toko->kota ? ', ' . $toko->kota : '' }}</p>
            @endif
        </figcaption>
    </figure>

    <section class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="relative mb-8 h-40 overflow-hidden rounded-2xl lg:hidden" aria-hidden="true">
                <img src="{{ asset('img/warung-altari.webp') }}" alt="" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-black/[.65]"></div>
                <p class="absolute bottom-4 left-5 font-display text-2xl font-bold text-white">{{ $toko->nama_usaha }}</p>
            </div>
            <h1 class="font-display text-4xl font-bold">Masuk</h1>
            <p class="mt-1 text-sm text-ink-500">Gunakan akun kasir atau admin.</p>

            @if (session('status'))
                <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" data-turbo="false" x-data="{ lihat: false, kirim: false }" @submit="kirim = true">
                @csrf
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="input @error('email') input-error @enderror"
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
                <button type="submit" class="btn-primary w-full py-3 text-base" :disabled="kirim">
                    <span x-text="kirim ? 'Memproses…' : 'Masuk'">Masuk</span>
                </button>
            </form>
        </div>
    </section>
</main>
@endsection

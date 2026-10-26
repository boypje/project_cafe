@extends('layouts.panel')

@section('title', 'Dashboard')
@section('subtitle')
    {{ now()->translatedFormat('l, d F Y') }} · ringkasan hari ini & bulan berjalan
@endsection

@section('actions')
    <a href="{{ route('laporan.index') }}" class="btn-secondary hidden md:inline-flex"><x-icon name="chart" class="h-4 w-4" /> Laporan lengkap</a>
@endsection

@section('content')
<div x-data="dashboard('{{ route('dashboard.data') }}')">
    <template x-if="loading && !d">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">@for ($i = 0; $i < 4; $i++)<div class="skeleton h-32 rounded-2xl"></div>@endfor</div>
            <div class="skeleton h-80 rounded-2xl"></div>
        </div>
    </template>

    <div x-show="d" x-cloak class="space-y-6">
        {{-- KPI hari ini vs kemarin --}}
        <section aria-labelledby="judul-hari-ini">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="judul-hari-ini" class="text-sm font-semibold uppercase tracking-wide text-ink-500">Hari ini <span class="font-normal normal-case">vs kemarin</span></h2>
                <button type="button" class="btn-ghost btn-sm" @click="muat()" :disabled="loading"><span :class="loading && 'animate-spin'"><x-icon name="refresh" class="h-4 w-4" /></span> Perbarui</button>
            </div>
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                @foreach ([['bersih', 'Penjualan bersih', 'rupiah'], ['transaksi', 'Transaksi', 'angka'], ['aov', 'Rata-rata transaksi', 'rupiah'], ['pengunjung', 'Pengunjung', 'angka']] as [$k, $label, $fmt])
                    <div class="card card-pad">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm text-ink-500">{{ $label }}</p>
                            @include('partials.delta', ['expr' => "d && delta(d.hari_ini.$k, d.kemarin.$k)"])
                        </div>
                        <p class="mt-2 font-display text-2xl font-bold tabular-nums lg:text-3xl" x-text="d && {{ $fmt }}(d.hari_ini.{{ $k }})"></p>
                        <p class="mt-1 text-xs text-ink-400">Kemarin <span x-text="d && {{ $fmt }}(d.kemarin.{{ $k }})"></span></p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Bulan berjalan --}}
        <section class="card overflow-hidden" aria-labelledby="judul-bulan">
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-ink-100 px-6 py-4">
                <h2 id="judul-bulan" class="card-title">Bulan berjalan</h2>
                <p class="text-xs text-ink-500">Dibandingkan periode tanggal yang sama bulan lalu</p>
            </div>
            <div class="grid grid-cols-2 divide-ink-100 md:grid-cols-3 xl:grid-cols-6 xl:divide-x">
                @foreach ([['bersih', 'Penjualan bersih', false], ['laba_kotor', 'Laba kotor', false], ['pengeluaran', 'Pengeluaran', true], ['laba_bersih', 'Laba bersih', false], ['transaksi', 'Transaksi', false], ['diskon', 'Diskon', true]] as [$k, $label, $kebalik])
                    <div class="px-6 py-5">
                        <p class="text-xs text-ink-500">{{ $label }}</p>
                        <p class="mt-1 text-lg font-bold tabular-nums" x-text="d && ({{ $k === 'transaksi' ? 'angka' : 'rupiah' }})(d.bulan_ini.{{ $k }})"></p>
                        @include('partials.delta', ['expr' => "d && delta(d.bulan_ini.$k, d.bulan_lalu.$k)", 'kebalik' => $kebalik])
                    </div>
                @endforeach
            </div>
            <p x-show="d && d.bulan_ini.cakupan_hpp < 90" class="flex items-center gap-2 border-t border-amber-100 bg-amber-50 px-6 py-2.5 text-xs text-amber-800">
                <x-icon name="alert" class="h-4 w-4 shrink-0" />
                <span>Laba belum akurat: baru <strong x-text="d && d.bulan_ini.cakupan_hpp + '%'"></strong> omzet yang produknya memiliki HPP. <a href="{{ route('produk.index') }}" class="font-semibold underline">Lengkapi HPP produk</a>.</span>
            </p>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="card card-pad xl:col-span-2" aria-labelledby="judul-tren">
                <h2 id="judul-tren" class="card-title">Tren 30 hari</h2>
                <p class="text-xs text-ink-500">Penjualan bersih & laba kotor harian</p>
                <div class="mt-4 h-72"><canvas x-ref="tren" role="img" aria-label="Grafik penjualan dan laba 30 hari terakhir"></canvas></div>
            </section>
            <section class="card card-pad" aria-labelledby="judul-metode">
                <h2 id="judul-metode" class="card-title">Metode bayar</h2>
                <p class="text-xs text-ink-500">Bulan ini</p>
                <div class="mt-4 h-72"><canvas x-ref="metode" role="img" aria-label="Komposisi metode pembayaran bulan ini"></canvas></div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="card xl:col-span-2" aria-labelledby="judul-top">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <h2 id="judul-top" class="card-title">Produk terlaris bulan ini</h2>
                    <a href="{{ route('laporan.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">Analisis produk →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>#</th><th>Produk</th><th class="num">Qty</th><th class="num">Omzet</th><th class="num">Kontribusi</th></tr></thead>
                        <tbody>
                            <template x-for="(p, i) in d?.produk_bulan_ini || []" :key="p.id_produk">
                                <tr>
                                    <td class="w-10 text-ink-400" x-text="i + 1"></td>
                                    <td><p class="font-semibold" x-text="p.nama"></p><p class="text-xs text-ink-400" x-text="p.kategori"></p></td>
                                    <td class="num" x-text="angka(p.qty)"></td>
                                    <td class="num font-semibold" x-text="rupiah(p.omzet)"></td>
                                    <td class="num">
                                        <div class="ml-auto flex w-28 items-center gap-2">
                                            <div class="h-1.5 flex-1 rounded-full bg-ink-100"><div class="h-1.5 rounded-full bg-brand-500" :style="`width:${Math.min(p.kontribusi, 100)}%`"></div></div>
                                            <span class="w-10 text-xs" x-text="p.kontribusi + '%'"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="d && !d.produk_bulan_ini.length"><tr><td colspan="5" class="py-8 text-center text-ink-500">Belum ada penjualan bulan ini.</td></tr></template>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card card-pad" aria-labelledby="judul-jam">
                <h2 id="judul-jam" class="card-title">Jam ramai hari ini</h2>
                <p class="text-xs text-ink-500">Jumlah transaksi per jam</p>
                <div class="mt-4 h-64"><canvas x-ref="jam" role="img" aria-label="Transaksi per jam hari ini"></canvas></div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="card" aria-labelledby="judul-stok">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <h2 id="judul-stok" class="card-title">Peringatan stok</h2>
                    <a href="{{ route('stok.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">Kelola stok →</a>
                </div>
                <p x-show="d && !d.stok.length" class="flex items-center gap-2 px-6 py-6 text-sm text-emerald-700"><x-icon name="check-circle" class="h-5 w-5" /> Semua stok aman.</p>
                <ul class="divide-y divide-ink-50">
                    <template x-for="s in d?.stok || []" :key="s.id_produk">
                        <li class="flex items-center gap-3 px-6 py-3 text-sm">
                            <span :class="s.status === 'habis' ? 'badge-bad' : 'badge-warn'" x-text="s.status === 'habis' ? 'Habis' : 'Menipis'"></span>
                            <span class="flex-1 font-medium" x-text="s.nama"></span>
                            <span class="text-right">
                                <strong class="tabular-nums" x-text="`${s.stok} ${s.satuan}`"></strong>
                                <span class="block text-xs text-ink-400" x-show="s.hari_tersisa !== null" x-text="`± ${s.hari_tersisa} hari lagi`"></span>
                            </span>
                        </li>
                    </template>
                </ul>
            </section>

            <section class="card" aria-labelledby="judul-terbaru">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <h2 id="judul-terbaru" class="card-title">Transaksi terbaru</h2>
                    <a href="{{ route('penjualan.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">Semua →</a>
                </div>
                <ul class="divide-y divide-ink-50">
                    <template x-for="t in d?.terbaru || []" :key="t.id">
                        <li class="flex items-center gap-3 px-6 py-3 text-sm">
                            <span class="w-20 text-xs tabular-nums text-ink-400" x-text="t.waktu"></span>
                            <span class="flex-1"><span class="font-semibold" x-text="t.nomor"></span> <span class="text-xs text-ink-400" x-text="`· ${t.kasir || '-'}`"></span></span>
                            <span class="badge-muted" x-text="t.metode"></span>
                            <span class="w-24 text-right font-semibold tabular-nums" x-text="rupiah(t.total)"></span>
                        </li>
                    </template>
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection

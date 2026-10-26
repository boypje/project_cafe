@extends('layouts.panel')

@section('title', 'Tutup Kasir')
@section('subtitle', 'Rekap harian: setoran tunai, menu terjual, dan pemakaian bahan')

@section('actions')
    <button type="button" class="btn-secondary" onclick="window.print()"><x-icon name="printer" class="h-4 w-4" /> Cetak</button>
@endsection

@section('content')
<div x-data="tutup('{{ route('kasir.tutup.data') }}')">
    <div class="no-print mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="t-tgl" class="label">Tanggal</label>
            <input id="t-tgl" type="date" x-model="tanggal" :max="util.ymd()" class="input py-2">
        </div>
        @if ($users->isNotEmpty())
            <div>
                <label for="t-user" class="label">Kasir</label>
                <select id="t-user" x-model="user" class="input py-2">
                    <option value="">Semua kasir</option>
                    @foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="mb-4 hidden print:block">
        <p class="font-display text-2xl font-bold">{{ $setting->nama_usaha }} — Tutup Kasir</p>
        <p class="text-sm" x-text="tgl(tanggal, true)"></p>
    </div>

    <template x-if="loading && !d">
        <div class="grid gap-4 sm:grid-cols-4">@for ($i = 0; $i < 4; $i++)<div class="skeleton h-24"></div>@endfor</div>
    </template>

    <template x-if="d">
        <div class="space-y-6" :class="loading && 'opacity-60 transition'">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="card card-pad"><p class="text-sm text-ink-500">Transaksi</p><p class="mt-1 font-display text-3xl font-bold" x-text="angka(d.ringkasan.transaksi)"></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Pengunjung</p><p class="mt-1 font-display text-3xl font-bold" x-text="angka(d.ringkasan.pengunjung)"></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Item terjual</p><p class="mt-1 font-display text-3xl font-bold" x-text="angka(d.ringkasan.item)"></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Total penjualan</p><p class="mt-1 font-display text-3xl font-bold text-brand-700" x-text="rupiah(d.ringkasan.total)"></p></div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="card card-pad">
                    <h2 class="card-title mb-4">Setoran</h2>
                    <dl class="space-y-2.5 text-sm">
                        <template x-for="m in d.metode" :key="m.metode">
                            <div class="flex justify-between"><dt class="text-ink-600" x-text="`Penjualan ${m.metode} (${m.transaksi} trx)`"></dt><dd class="font-semibold tabular-nums" x-text="rupiah(m.total)"></dd></div>
                        </template>
                        <div class="flex justify-between"><dt class="text-ink-600">Pengeluaran tunai</dt><dd class="font-semibold tabular-nums text-red-600" x-text="'− ' + rupiah(d.setoran.pengeluaran_tunai)"></dd></div>
                        <div class="flex items-baseline justify-between rounded-xl bg-emerald-50 px-4 py-3 text-emerald-900">
                            <dt class="font-semibold">Uang tunai disetor</dt><dd class="font-display text-2xl font-bold tabular-nums" x-text="rupiah(d.setoran.setor_tunai)"></dd>
                        </div>
                        <div class="flex justify-between"><dt class="text-ink-600">Non-tunai (masuk rekening)</dt><dd class="font-semibold tabular-nums" x-text="rupiah(d.setoran.non_tunai)"></dd></div>
                        <div class="flex justify-between border-t border-ink-100 pt-2 text-ink-500"><dt>Diskon diberikan</dt><dd class="tabular-nums" x-text="rupiah(d.ringkasan.diskon)"></dd></div>
                        <div class="flex justify-between text-ink-500" x-show="Number(d.ringkasan.pajak)"><dt>Pajak terkumpul</dt><dd class="tabular-nums" x-text="rupiah(d.ringkasan.pajak)"></dd></div>
                    </dl>
                </section>

                <section class="card card-pad">
                    <h2 class="card-title mb-4">Pengeluaran</h2>
                    <p x-show="!d.pengeluaran.length" class="text-sm text-ink-500">Tidak ada pengeluaran.</p>
                    <ul class="divide-y divide-ink-50 text-sm">
                        <template x-for="(p, i) in d.pengeluaran" :key="i">
                            <li class="flex justify-between gap-3 py-2"><span><span x-text="p.deskripsi"></span> <span class="text-xs text-ink-400" x-text="`· ${p.kategori} · ${p.metode}`"></span></span><span class="font-semibold tabular-nums" x-text="rupiah(p.nominal)"></span></li>
                        </template>
                    </ul>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
                <section class="card">
                    <h2 class="card-title border-b border-ink-100 p-5">Menu terjual</h2>
                    <p x-show="!d.items.length" class="p-5 text-sm text-ink-500">Belum ada penjualan.</p>
                    <template x-for="g in kelompok" :key="g.nama">
                        <div>
                            <div class="flex justify-between bg-ink-50 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-ink-500">
                                <span x-text="g.nama"></span><span x-text="`${g.qty} item · ${rupiah(g.total)}`"></span>
                            </div>
                            <ul class="divide-y divide-ink-50 text-sm">
                                <template x-for="i in g.items" :key="i.id_produk">
                                    <li class="grid grid-cols-[1fr_60px_110px] gap-3 px-5 py-2"><span x-text="i.nama || '(dihapus)'"></span><span class="text-right font-semibold tabular-nums" x-text="`${i.qty}×`"></span><span class="text-right tabular-nums text-ink-600" x-text="rupiah(i.total)"></span></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </section>
                <section class="card">
                    <div class="border-b border-ink-100 p-5">
                        <h2 class="card-title">Pemakaian bahan</h2>
                        <p class="text-xs text-ink-500">Termasuk komponen dari paket</p>
                    </div>
                    <p x-show="!d.bahan.length" class="p-5 text-sm text-ink-500">Tidak ada.</p>
                    <ul class="divide-y divide-ink-50 text-sm">
                        <template x-for="b in d.bahan" :key="b.nama">
                            <li class="flex items-center justify-between gap-3 px-5 py-2">
                                <span x-text="b.nama"></span>
                                <span class="text-right"><strong class="tabular-nums" x-text="b.qty"></strong> <span class="text-xs" :class="b.sisa <= 0 ? 'text-red-600' : 'text-ink-400'" x-text="`sisa ${b.sisa}`"></span></span>
                            </li>
                        </template>
                    </ul>
                </section>
            </div>
        </div>
    </template>
</div>
@endsection

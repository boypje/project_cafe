@extends('layouts.panel')

@section('title', 'Laporan Eksekutif')
@section('subtitle', 'Kinerja penjualan, profitabilitas, menu, pola waktu, kas, dan persediaan')

@php
    $tabs = [
        'ringkasan' => 'Ringkasan & Laba Rugi',
        'penjualan' => 'Penjualan',
        'produk' => 'Menu & Produk',
        'pola' => 'Pola Waktu',
        'kasir' => 'Kasir',
        'keuangan' => 'Kas & Biaya',
        'stok' => 'Persediaan',
    ];
    $presets = ['hari' => 'Hari ini', '7' => '7 hari', '30' => '30 hari', 'bulan' => 'Bulan ini', 'bulanLalu' => 'Bulan lalu', 'kuartal' => '90 hari', 'tahun' => 'Tahun ini'];
@endphp

@section('content')
<div x-data="laporan('{{ route('laporan.data') }}')">
    {{-- Filter periode --}}
    <div class="no-print card mb-6 p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-wrap gap-1.5" role="group" aria-label="Periode cepat">
                @foreach ($presets as $k => $v)
                    <button type="button" class="chip py-1.5" :class="preset === '{{ $k }}' && 'chip-active'" :aria-pressed="(preset === '{{ $k }}').toString()" @click="pilihPreset('{{ $k }}')">{{ $v }}</button>
                @endforeach
            </div>
            <div class="ml-auto flex flex-wrap items-end gap-2">
                <div><label for="l-awal" class="label">Dari</label><input id="l-awal" type="date" x-model="awal" @change="ubahTanggal()" class="input py-2"></div>
                <div><label for="l-akhir" class="label">Sampai</label><input id="l-akhir" type="date" x-model="akhir" @change="ubahTanggal()" class="input py-2"></div>
                <button type="button" class="btn-secondary" @click="ekspor()" :disabled="!d"><x-icon name="download" class="h-4 w-4" /> CSV</button>
                <button type="button" class="btn-secondary" @click="cetak()" :disabled="!d"><x-icon name="printer" class="h-4 w-4" /> Cetak</button>
            </div>
        </div>
    </div>

    <div class="mb-4 hidden print:block">
        <p class="font-display text-2xl font-bold">{{ $setting->nama_usaha }} — Laporan Eksekutif</p>
    </div>

    <div x-show="d" class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-500">
        <span><strong class="text-ink-900" x-text="d && `${tanggal(d.periode.awal)} – ${tanggal(d.periode.akhir)}`"></strong> · <span x-text="d?.periode.hari"></span> hari</span>
        <span>Pembanding: <span x-text="d && `${tanggal(d.pembanding.awal)} – ${tanggal(d.pembanding.akhir)}`"></span></span>
        <span x-show="loading" class="flex items-center gap-1 text-brand-700"><x-icon name="refresh" class="h-4 w-4 animate-spin" /> memuat…</span>
    </div>

    <p x-show="d && r.cakupan_hpp < 90" x-cloak class="mb-4 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
        <span>HPP baru tercatat untuk <strong x-text="r.cakupan_hpp + '%'"></strong> omzet periode ini, sehingga laba kotor & margin terlihat lebih tinggi dari kenyataan.
            Isi HPP di <a href="{{ route('produk.index') }}" class="font-semibold underline">Produk & Resep</a> (transaksi lama memakai HPP terbaru).</span>
    </p>

    <div class="no-print mb-6 tabs" role="tablist" aria-label="Bagian laporan">
        @foreach ($tabs as $k => $v)
            <button type="button" role="tab" class="tab" id="tab-{{ $k }}" aria-controls="panel-{{ $k }}" :aria-selected="(tab === '{{ $k }}').toString()" @click="tab = '{{ $k }}'">{{ $v }}</button>
        @endforeach
    </div>

    <template x-if="loading && !d">
        <div class="space-y-4"><div class="grid grid-cols-2 gap-4 xl:grid-cols-4">@for ($i = 0; $i < 8; $i++)<div class="skeleton h-28 rounded-2xl"></div>@endfor</div><div class="skeleton h-80 rounded-2xl"></div></div>
    </template>

    <div x-show="d" x-cloak :class="loading && 'opacity-60 transition-opacity'">
        {{-- ================= RINGKASAN ================= --}}
        <section id="panel-ringkasan" role="tabpanel" aria-labelledby="tab-ringkasan" x-show="tab === 'ringkasan'" class="space-y-6">
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                <template x-for="k in kpi" :key="k.k">
                    <div class="card card-pad">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm text-ink-500" x-text="k.label"></p>
                            <span class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[11px] font-semibold"
                                  :class="delta(k.k) === null || Math.abs(delta(k.k)) < 0.05 ? 'bg-ink-100 text-ink-500' : ((delta(k.k) >= 0) !== !!k.kebalik ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700')"
                                  x-text="delta(k.k) === null ? 'baru' : (delta(k.k) >= 0 ? '▲ ' : '▼ ') + angka(Math.abs(delta(k.k)), 1) + '%'"></span>
                        </div>
                        <p class="mt-2 font-display text-2xl font-bold tabular-nums" :class="r[k.k] < 0 && 'text-red-600'" x-text="k.fmt(r[k.k])"></p>
                        <p class="mt-1 text-xs text-ink-400" x-text="k.info"></p>
                        <p class="text-xs text-ink-400">Sebelumnya <span x-text="k.fmt(rl[k.k])"></span></p>
                    </div>
                </template>
            </div>

            {{-- Sorotan --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-ink-900 p-5 text-white">
                    <p class="text-xs uppercase tracking-wide text-ink-300">Hari terbaik</p>
                    <p class="mt-1 font-display text-xl font-bold" x-text="hariTerbaik ? tanggal(hariTerbaik.tanggal) : '-'"></p>
                    <p class="text-sm text-brand-200" x-text="hariTerbaik ? rupiah(hariTerbaik.bersih) + ' · ' + hariTerbaik.transaksi + ' trx' : ''"></p>
                </div>
                <div class="rounded-2xl bg-ink-900 p-5 text-white">
                    <p class="text-xs uppercase tracking-wide text-ink-300">Jam tersibuk</p>
                    <p class="mt-1 font-display text-xl font-bold" x-text="jamSibuk === null ? '-' : `${String(jamSibuk).padStart(2, '0')}:00 – ${String(jamSibuk + 1).padStart(2, '0')}:00`"></p>
                    <p class="text-sm text-brand-200" x-text="jamSibuk === null ? '' : d.pola.jam[jamSibuk].transaksi + ' transaksi'"></p>
                </div>
                <div class="rounded-2xl bg-ink-900 p-5 text-white">
                    <p class="text-xs uppercase tracking-wide text-ink-300">Menu andalan</p>
                    <p class="mt-1 truncate font-display text-xl font-bold" x-text="d?.produk.produk[0]?.nama || '-'"></p>
                    <p class="text-sm text-brand-200" x-text="d?.produk.produk[0] ? d.produk.produk[0].kontribusi + '% omzet · ' + d.produk.produk[0].qty + ' terjual' : ''"></p>
                </div>
                <div class="rounded-2xl bg-ink-900 p-5 text-white">
                    <p class="text-xs uppercase tracking-wide text-ink-300">Rata-rata harian</p>
                    <p class="mt-1 font-display text-xl font-bold" x-text="d ? rupiah(r.bersih / d.periode.hari) : '-'"></p>
                    <p class="text-sm text-brand-200" x-text="d ? angka(r.transaksi / d.periode.hari, 1) + ' transaksi / hari' : ''"></p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="card card-pad xl:col-span-2">
                    <h2 class="card-title">Tren penjualan, biaya & laba</h2>
                    <div class="mt-4 h-80"><canvas x-ref="tren" role="img" aria-label="Grafik penjualan bersih, pengeluaran, dan laba bersih harian"></canvas></div>
                </div>
                <div class="card card-pad">
                    <h2 class="card-title">Laba rugi</h2>
                    <p class="text-xs text-ink-500">Periode terpilih</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt>Penjualan kotor</dt><dd class="tabular-nums" x-text="rupiah(r.kotor)"></dd></div>
                        <div class="flex justify-between text-ink-500"><dt>Diskon</dt><dd class="tabular-nums" x-text="'(' + rupiah(r.diskon) + ')'"></dd></div>
                        <div class="flex justify-between border-t border-ink-100 pt-2 font-semibold"><dt>Penjualan bersih</dt><dd class="tabular-nums" x-text="rupiah(r.bersih)"></dd></div>
                        <div class="flex justify-between text-ink-500"><dt>HPP</dt><dd class="tabular-nums" x-text="'(' + rupiah(r.hpp) + ')'"></dd></div>
                        <div class="flex justify-between border-t border-ink-100 pt-2 font-semibold"><dt>Laba kotor <span class="font-normal text-ink-400" x-text="`· ${angka(r.margin, 1)}%`"></span></dt><dd class="tabular-nums" x-text="rupiah(r.laba_kotor)"></dd></div>
                        <div class="flex justify-between text-ink-500"><dt>Pengeluaran operasional</dt><dd class="tabular-nums" x-text="'(' + rupiah(r.pengeluaran) + ')'"></dd></div>
                        <div class="flex justify-between rounded-xl px-3 py-2.5 text-base font-bold" :class="r.laba_bersih >= 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-700'">
                            <dt>Laba bersih <span class="text-xs font-medium" x-text="r.bersih ? `· ${angka(r.laba_bersih / r.bersih * 100, 1)}%` : ''"></span></dt>
                            <dd class="tabular-nums" x-text="rupiah(r.laba_bersih)"></dd>
                        </div>
                        <div class="flex justify-between pt-1 text-xs text-ink-400"><dt>Pajak dipungut (titipan)</dt><dd class="tabular-nums" x-text="rupiah(r.pajak)"></dd></div>
                    </dl>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card card-pad"><h2 class="card-title">Metode pembayaran</h2><div class="mt-4 h-64"><canvas x-ref="metode" role="img" aria-label="Komposisi metode pembayaran"></canvas></div></div>
                <div class="card card-pad"><h2 class="card-title">Penjualan per kategori</h2><div class="mt-4 h-64"><canvas x-ref="kategori" role="img" aria-label="Komposisi omzet per kategori"></canvas></div></div>
            </div>
        </section>

        {{-- ================= PENJUALAN ================= --}}
        <section id="panel-penjualan" role="tabpanel" aria-labelledby="tab-penjualan" x-show="tab === 'penjualan'" class="space-y-6">
            <div class="card card-pad">
                <h2 class="card-title">Transaksi & pengunjung</h2>
                <div class="mt-4 h-72"><canvas x-ref="trx" role="img" aria-label="Grafik jumlah transaksi dan pengunjung"></canvas></div>
            </div>

            <div class="card" x-show="d?.bulanan.length > 1">
                <h2 class="card-title border-b border-ink-100 p-5">Ringkasan bulanan</h2>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Bulan</th><th class="num">Transaksi</th><th class="num">Pengunjung</th><th class="num">Penjualan bersih</th><th class="num">Rata-rata trx</th><th class="num">Laba kotor</th><th class="num">Pengeluaran</th><th class="num">Laba bersih</th></tr></thead>
                        <tbody>
                            <template x-for="b in d?.bulanan || []" :key="b.bulan">
                                <tr>
                                    <td class="font-semibold" x-text="bulan(b.bulan)"></td>
                                    <td class="num" x-text="angka(b.transaksi)"></td>
                                    <td class="num" x-text="angka(b.pengunjung)"></td>
                                    <td class="num font-semibold" x-text="rupiah(b.bersih)"></td>
                                    <td class="num" x-text="rupiah(b.aov)"></td>
                                    <td class="num" x-text="rupiah(b.laba_kotor)"></td>
                                    <td class="num" x-text="rupiah(b.pengeluaran)"></td>
                                    <td class="num font-semibold" :class="b.laba_bersih < 0 && 'text-red-600'" x-text="rupiah(b.laba_bersih)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title border-b border-ink-100 p-5">Rincian harian</h2>
                <div class="scroll-thin max-h-[560px] overflow-auto">
                    <table class="tbl">
                        <thead><tr><th>Tanggal</th><th class="num">Transaksi</th><th class="num">Pengunjung</th><th class="num">Penjualan bersih</th><th class="num">HPP</th><th class="num">Laba kotor</th><th class="num">Pengeluaran</th><th class="num">Laba bersih</th></tr></thead>
                        <tbody>
                            <template x-for="h in d?.harian || []" :key="h.tanggal">
                                <tr :class="!h.transaksi && 'text-ink-300'">
                                    <td class="whitespace-nowrap"><span x-text="HARI[new Date(h.tanggal + 'T00:00').getDay()].slice(0, 3)"></span>, <span x-text="tanggal(h.tanggal)"></span></td>
                                    <td class="num" x-text="angka(h.transaksi)"></td>
                                    <td class="num" x-text="angka(h.pengunjung)"></td>
                                    <td class="num font-semibold" x-text="rupiah(h.bersih)"></td>
                                    <td class="num" x-text="rupiah(h.hpp)"></td>
                                    <td class="num" x-text="rupiah(h.laba_kotor)"></td>
                                    <td class="num" x-text="rupiah(h.pengeluaran)"></td>
                                    <td class="num font-semibold" :class="h.laba_bersih < 0 && 'text-red-600'" x-text="rupiah(h.laba_bersih)"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-ink-50 font-bold">
                                <td class="px-4 py-3">Total</td>
                                <td class="num px-4" x-text="angka(r.transaksi)"></td>
                                <td class="num px-4" x-text="angka(r.pengunjung)"></td>
                                <td class="num px-4" x-text="rupiah(r.bersih)"></td>
                                <td class="num px-4" x-text="rupiah(r.hpp)"></td>
                                <td class="num px-4" x-text="rupiah(r.laba_kotor)"></td>
                                <td class="num px-4" x-text="rupiah(r.pengeluaran)"></td>
                                <td class="num px-4" x-text="rupiah(r.laba_bersih)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>

        {{-- ================= PRODUK ================= --}}
        <section id="panel-produk" role="tabpanel" aria-labelledby="tab-produk" x-show="tab === 'produk'" class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <template x-for="k in kelasAbc" :key="k.kelas">
                    <div class="card card-pad">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl font-display text-xl font-bold"
                                  :class="{ 'bg-emerald-50 text-emerald-700': k.kelas === 'A', 'bg-amber-50 text-amber-700': k.kelas === 'B', 'bg-ink-100 text-ink-600': k.kelas === 'C' }" x-text="k.kelas"></span>
                            <div>
                                <p class="font-semibold" x-text="`${k.jumlah} menu`"></p>
                                <p class="text-xs text-ink-500" x-text="{ A: 'Penyumbang ±80% omzet — jaga ketersediaan', B: '±15% omzet berikutnya — dorong promosi', C: 'Sisa ±5% — evaluasi / sederhanakan menu' }[k.kelas]"></p>
                            </div>
                        </div>
                        <p class="mt-3 font-display text-xl font-bold" x-text="rupiah(k.omzet)"></p>
                    </div>
                </template>
            </div>

            <div class="card card-pad">
                <h2 class="card-title">12 menu teratas</h2>
                <div class="mt-4 h-96"><canvas x-ref="top" role="img" aria-label="Omzet dan laba menu teratas"></canvas></div>
            </div>

            <div class="card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 p-5">
                    <h2 class="card-title">Kinerja seluruh menu</h2>
                    <label class="flex items-center gap-2 text-sm">Urutkan
                        <select x-model="urutProduk" class="rounded-lg border-ink-200 py-1.5 text-sm">
                            <option value="omzet">Omzet</option><option value="qty">Qty terjual</option><option value="laba">Laba</option><option value="margin">Margin</option>
                        </select>
                    </label>
                </div>
                <div class="scroll-thin max-h-[600px] overflow-auto">
                    <table class="tbl">
                        <thead><tr><th>Menu</th><th>Kelas</th><th class="num">Qty</th><th class="num">Omzet</th><th class="num">Kontribusi</th><th class="num">HPP</th><th class="num">Laba</th><th class="num">Margin</th></tr></thead>
                        <tbody>
                            <template x-for="p in produkUrut" :key="p.id_produk">
                                <tr>
                                    <td><p class="font-semibold" x-text="p.nama"></p><p class="text-xs text-ink-400" x-text="p.kategori"></p></td>
                                    <td><span :class="{ 'badge-ok': p.kelas === 'A', 'badge-warn': p.kelas === 'B', 'badge-muted': p.kelas === 'C' }" x-text="p.kelas"></span></td>
                                    <td class="num" x-text="angka(p.qty)"></td>
                                    <td class="num font-semibold" x-text="rupiah(p.omzet)"></td>
                                    <td class="num" x-text="p.kontribusi + '%'"></td>
                                    <td class="num" :class="!p.hpp && 'text-ink-300'" x-text="p.hpp ? rupiah(p.hpp) : '—'"></td>
                                    <td class="num" x-text="p.hpp ? rupiah(p.laba) : '—'"></td>
                                    <td class="num font-semibold" :class="!p.hpp ? 'text-ink-300' : (p.margin < 30 ? 'text-red-600' : (p.margin < 50 ? 'text-amber-700' : 'text-emerald-700'))" x-text="p.hpp ? p.margin + '%' : '—'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card">
                    <h2 class="card-title border-b border-ink-100 p-5">Per kategori</h2>
                    <table class="tbl">
                        <thead><tr><th>Kategori</th><th class="num">Qty</th><th class="num">Omzet</th><th class="num">Kontribusi</th></tr></thead>
                        <tbody>
                            <template x-for="k in d?.produk.kategori || []" :key="k.kategori">
                                <tr><td class="font-semibold" x-text="k.kategori"></td><td class="num" x-text="angka(k.qty)"></td><td class="num" x-text="rupiah(k.omzet)"></td><td class="num" x-text="k.kontribusi + '%'"></td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <div class="border-b border-ink-100 p-5">
                        <h2 class="card-title">Menu tidak terjual</h2>
                        <p class="text-xs text-ink-500">Tampil di kasir tetapi nol penjualan pada periode ini</p>
                    </div>
                    <p x-show="!d?.produk.tidak_laku.length" class="p-5 text-sm text-emerald-700">Semua menu terjual setidaknya sekali.</p>
                    <ul class="flex flex-wrap gap-2 p-5">
                        <template x-for="t in d?.produk.tidak_laku || []" :key="t.nama">
                            <li class="badge-muted px-3 py-1" x-text="t.nama"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ================= POLA WAKTU ================= --}}
        <section id="panel-pola" role="tabpanel" aria-labelledby="tab-pola" x-show="tab === 'pola'" class="space-y-6">
            <div class="card card-pad">
                <h2 class="card-title">Peta keramaian (jumlah transaksi)</h2>
                <p class="text-xs text-ink-500">Gunakan untuk jadwal shift, persiapan bahan, dan jam promo</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full border-separate border-spacing-1 text-center text-xs">
                        <thead>
                            <tr><th class="w-16"></th>
                                <template x-for="j in 24" :key="j"><th class="font-medium text-ink-400" x-text="String(j - 1).padStart(2, '0')"></th></template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(baris, h) in d?.pola.matriks || []" :key="h">
                                <tr>
                                    <th class="pr-2 text-left font-medium text-ink-500" x-text="HARI[h]"></th>
                                    <template x-for="(v, j) in baris" :key="j">
                                        <td class="h-8 min-w-[28px] rounded-md tabular-nums" :style="heat(v)" :title="`${HARI[h]} ${String(j).padStart(2, '0')}:00 — ${v} transaksi`" x-text="v || ''"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card card-pad"><h2 class="card-title">Penjualan per jam</h2><div class="mt-4 h-72"><canvas x-ref="jam" role="img" aria-label="Penjualan per jam"></canvas></div></div>
                <div class="card card-pad"><h2 class="card-title">Rata-rata per hari dalam minggu</h2><div class="mt-4 h-72"><canvas x-ref="hari" role="img" aria-label="Rata-rata penjualan per hari dalam minggu"></canvas></div></div>
            </div>
        </section>

        {{-- ================= KASIR ================= --}}
        <section id="panel-kasir" role="tabpanel" aria-labelledby="tab-kasir" x-show="tab === 'kasir'" class="space-y-6">
            <div class="card">
                <h2 class="card-title border-b border-ink-100 p-5">Kinerja kasir</h2>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Kasir</th><th class="num">Transaksi</th><th class="num">Penjualan bersih</th><th class="num">Porsi</th><th class="num">Rata-rata trx</th><th class="num">Pengunjung</th><th class="num">Diskon diberikan</th></tr></thead>
                        <tbody>
                            <template x-for="k in d?.kasir || []" :key="k.nama">
                                <tr>
                                    <td class="font-semibold" x-text="k.nama"></td>
                                    <td class="num" x-text="angka(k.transaksi)"></td>
                                    <td class="num font-semibold" x-text="rupiah(k.bersih)"></td>
                                    <td class="num" x-text="angka(k.bersih / (r.bersih || 1) * 100, 1) + '%'"></td>
                                    <td class="num" x-text="rupiah(k.aov)"></td>
                                    <td class="num" x-text="angka(k.pengunjung)"></td>
                                    <td class="num" :class="k.diskon && 'text-red-600'" x-text="rupiah(k.diskon)"></td>
                                </tr>
                            </template>
                            <template x-if="d && !d.kasir.length"><tr><td colspan="7" class="py-8 text-center text-ink-500">Tidak ada transaksi.</td></tr></template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- ================= KEUANGAN ================= --}}
        <section id="panel-keuangan" role="tabpanel" aria-labelledby="tab-keuangan" x-show="tab === 'keuangan'" class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="card card-pad"><p class="text-sm text-ink-500">Modal / kas masuk</p><p class="mt-1 font-display text-2xl font-bold" x-text="rupiah(d?.kas.modal)"></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Penjualan tunai</p><p class="mt-1 font-display text-2xl font-bold" x-text="rupiah(d?.kas.penjualan_tunai)"></p><p class="text-xs text-ink-400">Non-tunai <span x-text="rupiah(d?.kas.penjualan_non_tunai)"></span></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Pengeluaran tunai</p><p class="mt-1 font-display text-2xl font-bold text-red-600" x-text="rupiah(d?.kas.pengeluaran_tunai)"></p><p class="text-xs text-ink-400">Non-tunai <span x-text="rupiah(d?.kas.pengeluaran_non_tunai)"></span></p></div>
                <div class="card card-pad bg-ink-900 text-white"><p class="text-sm text-ink-300">Posisi kas tunai</p><p class="mt-1 font-display text-2xl font-bold" x-text="rupiah(d?.kas.kas_tunai)"></p><p class="text-xs text-ink-300">Arus kas bersih <span x-text="rupiah(d?.kas.arus_bersih)"></span></p></div>
            </div>
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card card-pad">
                    <h2 class="card-title">Struktur biaya</h2>
                    <div class="mt-4 h-64"><canvas x-ref="biaya" role="img" aria-label="Komposisi pengeluaran per kategori"></canvas></div>
                    <table class="tbl mt-4">
                        <thead><tr><th>Kategori</th><th class="num">Catatan</th><th class="num">Total</th><th class="num">% dari penjualan</th></tr></thead>
                        <tbody>
                            <template x-for="k in d?.pengeluaran.kategori || []" :key="k.kategori">
                                <tr><td class="font-semibold" x-text="k.kategori"></td><td class="num" x-text="k.jumlah"></td><td class="num" x-text="rupiah(k.total)"></td><td class="num" x-text="angka(k.total / (r.bersih || 1) * 100, 1) + '%'"></td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h2 class="card-title border-b border-ink-100 p-5">10 pengeluaran terbesar</h2>
                    <p x-show="!d?.pengeluaran.terbesar.length" class="p-5 text-sm text-ink-500">Tidak ada pengeluaran.</p>
                    <ul class="divide-y divide-ink-50">
                        <template x-for="(p, i) in d?.pengeluaran.terbesar || []" :key="i">
                            <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                                <span><span class="font-medium" x-text="p.deskripsi"></span><span class="block text-xs text-ink-400" x-text="`${tanggal(p.created_at)} · ${p.kategori} · ${p.metode}`"></span></span>
                                <span class="font-semibold tabular-nums" x-text="rupiah(p.nominal)"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ================= STOK ================= --}}
        <section id="panel-stok" role="tabpanel" aria-labelledby="tab-stok" x-show="tab === 'stok'" class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="card card-pad"><p class="text-sm text-ink-500">Nilai persediaan (HPP)</p><p class="mt-1 font-display text-2xl font-bold" x-text="rupiah(d?.stok.nilai_persediaan)"></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Item habis</p><p class="mt-1 font-display text-2xl font-bold text-red-600" x-text="d?.stok.habis"></p></div>
                <div class="card card-pad"><p class="text-sm text-ink-500">Item menipis</p><p class="mt-1 font-display text-2xl font-bold text-amber-700" x-text="d?.stok.menipis"></p></div>
            </div>
            <div class="card">
                <div class="border-b border-ink-100 p-5">
                    <h2 class="card-title">Pemakaian & ketahanan stok</h2>
                    <p class="text-xs text-ink-500">Pemakaian dihitung dari penjualan periode ini (paket diurai ke komponennya). Perkiraan habis = stok ÷ rata-rata pemakaian harian.</p>
                </div>
                <div class="scroll-thin max-h-[600px] overflow-auto">
                    <table class="tbl">
                        <thead><tr><th>Bahan / produk</th><th>Status</th><th class="num">Stok</th><th class="num">Minimum</th><th class="num">Terpakai</th><th class="num">Per hari</th><th class="num">Perkiraan habis</th><th class="num">Nilai</th></tr></thead>
                        <tbody>
                            <template x-for="s in d?.stok.items || []" :key="s.id_produk">
                                <tr>
                                    <td><p class="font-semibold" x-text="s.nama"></p><p class="text-xs text-ink-400" x-text="s.kategori"></p></td>
                                    <td><span :class="{ 'badge-ok': s.status === 'aman', 'badge-warn': s.status === 'menipis', 'badge-bad': s.status === 'habis' }" x-text="s.status"></span></td>
                                    <td class="num font-semibold" x-text="`${angka(s.stok)} ${s.satuan}`"></td>
                                    <td class="num text-ink-500" x-text="angka(s.minimum)"></td>
                                    <td class="num" x-text="angka(s.terpakai)"></td>
                                    <td class="num" x-text="angka(s.per_hari, 1)"></td>
                                    <td class="num" :class="s.hari_tersisa !== null && s.hari_tersisa <= 3 && 'font-semibold text-red-600'" x-text="s.hari_tersisa === null ? '—' : s.hari_tersisa + ' hari'"></td>
                                    <td class="num" x-text="rupiah(s.nilai)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

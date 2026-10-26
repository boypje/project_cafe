@extends('layouts.panel')

@section('title', 'Riwayat Penjualan')
@section('subtitle', 'Cari, lihat detail, cetak ulang, atau batalkan transaksi')

@section('content')
<div x-data="penjualan()">
    <div class="card">
        <div class="flex flex-wrap items-end gap-3 border-b border-ink-100 p-4">
            <div><label for="f-awal" class="label">Dari</label><input id="f-awal" type="date" x-model="filters.awal" class="input py-2"></div>
            <div><label for="f-akhir" class="label">Sampai</label><input id="f-akhir" type="date" x-model="filters.akhir" class="input py-2"></div>
            <div>
                <label for="f-metode" class="label">Metode</label>
                <select id="f-metode" x-model="filters.metode" class="input py-2">
                    <option value="">Semua</option>
                    @foreach ($metode as $m)<option>{{ $m }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="f-user" class="label">Kasir</label>
                <select id="f-user" x-model="filters.user" class="input py-2">
                    <option value="">Semua</option>
                    @foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                </select>
            </div>
            <div class="min-w-[180px] flex-1">
                <label for="f-cari" class="label">Cari</label>
                <input id="f-cari" type="search" x-model="q" class="input py-2" placeholder="No. transaksi, meja, pelanggan…">
            </div>
            <button type="button" class="btn-secondary" @click="ekspor()" :disabled="!rows.length"><x-icon name="download" class="h-4 w-4" /> CSV</button>
        </div>

        <div class="grid grid-cols-3 divide-x divide-ink-100 border-b border-ink-100 bg-brand-50/40 text-center">
            <div class="px-3 py-3"><p class="text-xs text-ink-500">Transaksi</p><p class="font-display text-xl font-bold" x-text="angka(meta.ringkasan?.transaksi)"></p></div>
            <div class="px-3 py-3"><p class="text-xs text-ink-500">Total diterima</p><p class="font-display text-xl font-bold text-brand-700" x-text="rupiah(meta.ringkasan?.bayar)"></p></div>
            <div class="px-3 py-3"><p class="text-xs text-ink-500">Total diskon</p><p class="font-display text-xl font-bold" x-text="rupiah(meta.ringkasan?.diskon)"></p></div>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>No. transaksi</th><th>Waktu</th><th>Kasir</th><th>Meja / pelanggan</th><th>Metode</th><th class="num">Item</th><th class="num">Diskon</th><th class="num">Total</th><th class="w-28"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="9"><div class="skeleton h-6"></div></td></tr></template>
                    <template x-for="r in rows" :key="r.id">
                        <tr class="cursor-pointer" @click="lihat(r)">
                            <td class="font-semibold" x-text="r.nomor"></td>
                            <td class="whitespace-nowrap text-ink-600" x-text="r.waktu"></td>
                            <td x-text="r.kasir || '-'"></td>
                            <td class="text-ink-600"><span x-show="r.meja" x-text="`Meja ${r.meja}`"></span><span x-show="r.meja && r.pelanggan"> · </span><span x-text="r.pelanggan || ''"></span><span x-show="!r.meja && !r.pelanggan" class="text-ink-300">—</span></td>
                            <td><span class="badge-muted" x-text="r.metode"></span></td>
                            <td class="num" x-text="r.item"></td>
                            <td class="num" :class="r.diskon ? 'text-red-600' : 'text-ink-300'" x-text="r.diskon ? rupiah(r.diskon) : '—'"></td>
                            <td class="num font-semibold" x-text="rupiah(r.bayar)"></td>
                            <td class="whitespace-nowrap text-right" @click.stop>
                                <button type="button" class="btn-icon" @click="lihat(r)" :aria-label="`Detail ${r.nomor}`"><x-icon name="eye" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon" @click="cetak(`/kasir/nota/${r.id}`)" :aria-label="`Cetak ${r.nomor}`"><x-icon name="printer" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon hover:text-red-600" @click="batalkan(r)" :aria-label="`Batalkan ${r.nomor}`"><x-icon name="trash" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !rows.length"><tr><td colspan="9" class="py-10 text-center text-ink-500">Tidak ada transaksi pada filter ini.</td></tr></template>
                </tbody>
            </table>
        </div>
        @include('partials.pager')
    </div>

    <x-dialog show="detailOpen" title="Detail transaksi" size="max-w-lg">
        <x-slot name="heading"><span x-text="detail?.nomor || 'Memuat…'"></span></x-slot>
        <div x-show="!detail" class="space-y-2"><div class="skeleton h-5"></div><div class="skeleton h-5 w-2/3"></div></div>
        <template x-if="detail">
            <div class="space-y-4 text-sm">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                    <dt class="text-ink-500">Waktu</dt><dd class="text-right" x-text="detail.waktu"></dd>
                    <dt class="text-ink-500">Kasir</dt><dd class="text-right" x-text="detail.kasir || '-'"></dd>
                    <dt class="text-ink-500">Meja / pelanggan</dt><dd class="text-right" x-text="[detail.meja && 'Meja ' + detail.meja, detail.pelanggan].filter(Boolean).join(' · ') || '-'"></dd>
                    <dt class="text-ink-500">Pengunjung</dt><dd class="text-right" x-text="detail.pengunjung"></dd>
                </dl>
                <ul class="divide-y divide-ink-100 rounded-xl border border-ink-100">
                    <template x-for="(i, n) in detail.items" :key="n">
                        <li class="flex justify-between gap-3 px-4 py-2.5">
                            <span><span class="font-medium" x-text="i.nama"></span> <span class="text-ink-500" x-text="`${i.jumlah} × ${rupiah(i.harga)}`"></span></span>
                            <span class="font-semibold tabular-nums" x-text="rupiah(i.subtotal)"></span>
                        </li>
                    </template>
                </ul>
                <dl class="space-y-1.5">
                    <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd x-text="rupiah(detail.total_harga)"></dd></div>
                    <div class="flex justify-between" x-show="detail.diskon"><dt class="text-ink-500">Diskon</dt><dd class="text-red-600" x-text="'− ' + rupiah(detail.diskon)"></dd></div>
                    <div class="flex justify-between" x-show="detail.pajak"><dt class="text-ink-500">Pajak</dt><dd x-text="rupiah(detail.pajak)"></dd></div>
                    <div class="flex justify-between border-t border-dashed border-ink-200 pt-2 text-base font-bold"><dt>Total</dt><dd class="text-brand-700" x-text="rupiah(detail.bayar)"></dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500" x-text="detail.metode"></dt><dd x-text="rupiah(detail.diterima)"></dd></div>
                </dl>
                <p x-show="detail.catatan" class="rounded-xl bg-ink-50 px-3 py-2 text-ink-600" x-text="'Catatan: ' + detail.catatan"></p>
            </div>
        </template>
        <x-slot name="footer">
            <button type="button" class="btn-ghost mr-auto text-red-600 hover:bg-red-50" @click="batalkan(detail)" :disabled="!detail"><x-icon name="trash" class="h-4 w-4" /> Batalkan</button>
            <button type="button" class="btn-secondary" @click="cetak(detail.nota_url)" :disabled="!detail"><x-icon name="printer" class="h-4 w-4" /> Cetak ulang</button>
        </x-slot>
    </x-dialog>
</div>
@endsection

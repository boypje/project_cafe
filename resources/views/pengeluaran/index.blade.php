@extends('layouts.panel')

@section('title', 'Pengeluaran')
@section('subtitle', 'Belanja bahan, gaji, sewa, dan biaya operasional lain')

@section('content')
<div x-data="crud({
        base: '/pengeluaran', label: 'pengeluaran', paginate: true,
        filters: { awal: util.ymd(new Date(new Date().getFullYear(), new Date().getMonth(), 1)), akhir: util.ymd(), kategori: '' },
        kosong: () => ({ tanggal: util.ymd(), deskripsi: '', kategori: 'Operasional', metode: 'Tunai', nominal: '' }),
        map: r => ({ tanggal: r.tanggal, deskripsi: r.deskripsi, kategori: r.kategori, metode: r.metode, nominal: r.nominal }),
     })">
    <div class="card">
        <div class="flex flex-wrap items-end gap-3 border-b border-ink-100 p-4">
            <div>
                <label for="f-awal" class="label">Dari</label>
                <input id="f-awal" type="date" x-model="filters.awal" class="input py-2">
            </div>
            <div>
                <label for="f-akhir" class="label">Sampai</label>
                <input id="f-akhir" type="date" x-model="filters.akhir" class="input py-2">
            </div>
            <div>
                <label for="f-kat" class="label">Kategori</label>
                <select id="f-kat" x-model="filters.kategori" class="input py-2">
                    <option value="">Semua</option>
                    @foreach ($kategori as $k)<option>{{ $k }}</option>@endforeach
                </select>
            </div>
            <div class="min-w-[180px] flex-1">
                <label for="f-cari" class="label">Cari</label>
                <input id="f-cari" type="search" x-model="q" class="input py-2" placeholder="Keterangan…">
            </div>
            <button type="button" class="btn-primary" @click="tambah()"><x-icon name="plus" class="h-4 w-4" /> Catat Pengeluaran</button>
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-1 bg-brand-50/50 px-4 py-3 text-sm">
            <span class="text-ink-500">Total periode: <strong class="text-base text-ink-900" x-text="$rp(meta.jumlah)"></strong></span>
            <span class="text-ink-500"><span x-text="total"></span> catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th>Metode</th><th class="num">Nominal</th><th>Dicatat</th><th class="w-24"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="7"><div class="skeleton h-6"></div></td></tr></template>
                    <template x-for="r in rows" :key="r.id">
                        <tr>
                            <td class="whitespace-nowrap"><span x-text="util.tanggal(r.tanggal)"></span> <span class="text-xs text-ink-400" x-text="r.jam"></span></td>
                            <td class="max-w-xs" x-text="r.deskripsi"></td>
                            <td><span class="badge-muted" x-text="r.kategori"></span></td>
                            <td x-text="r.metode"></td>
                            <td class="num font-semibold" x-text="$rp(r.nominal)"></td>
                            <td class="text-ink-500" x-text="r.user || '-'"></td>
                            <td class="whitespace-nowrap text-right">
                                <button type="button" class="btn-icon" @click="ubah(r)" aria-label="Ubah"><x-icon name="pencil" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon hover:text-red-600" @click="hapus(r)" aria-label="Hapus"><x-icon name="trash" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !rows.length"><tr><td colspan="7" class="py-10 text-center text-ink-500">Tidak ada pengeluaran pada periode ini.</td></tr></template>
                </tbody>
            </table>
        </div>
        @include('partials.pager')
    </div>

    <x-dialog show="open" title="Pengeluaran">
        <x-slot name="heading"><span x-text="editId ? 'Ubah pengeluaran' : 'Catat pengeluaran'"></span></x-slot>
        <form id="form-pengeluaran" @submit.prevent="simpan()" class="space-y-4">
            <div>
                <label for="p-desk" class="label">Keterangan</label>
                <input id="p-desk" x-model="form.deskripsi" class="input" :class="err('deskripsi') && 'input-error'" required maxlength="500" placeholder="Mis. Beli gas 3 kg">
                <p class="error-text" x-text="err('deskripsi')"></p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="p-nom" class="label">Nominal (Rp)</label>
                    <input id="p-nom" type="number" inputmode="numeric" min="1" x-model.number="form.nominal" class="input" :class="err('nominal') && 'input-error'" required>
                    <p class="error-text" x-text="err('nominal')"></p>
                </div>
                <div>
                    <label for="p-tgl" class="label">Tanggal</label>
                    <input id="p-tgl" type="date" x-model="form.tanggal" :max="util.ymd()" class="input" :class="err('tanggal') && 'input-error'" required>
                    <p class="error-text" x-text="err('tanggal')"></p>
                </div>
                <div>
                    <label for="p-kat" class="label">Kategori</label>
                    <input id="p-kat" list="daftar-kategori" x-model="form.kategori" class="input" required maxlength="50">
                    <datalist id="daftar-kategori">@foreach ($kategori as $k)<option value="{{ $k }}">@endforeach</datalist>
                </div>
                <div>
                    <label for="p-met" class="label">Dibayar dengan</label>
                    <select id="p-met" x-model="form.metode" class="input">
                        @foreach ($metode as $m)<option>{{ $m }}</option>@endforeach
                    </select>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="open = false">Batal</button>
            <button type="submit" form="form-pengeluaran" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan'"></button>
        </x-slot>
    </x-dialog>
</div>
@endsection

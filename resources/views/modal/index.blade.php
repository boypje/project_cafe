@extends('layouts.panel')

@section('title', 'Modal & Kas Masuk')
@section('subtitle', 'Modal awal laci kasir atau suntikan dana — dipakai di laporan arus kas')

@section('content')
<div x-data="crud({
        base: '/modal', label: 'catatan modal', cari: ['deskripsi'],
        kosong: () => ({ tanggal: util.ymd(), deskripsi: '', nominal: '' }),
        map: r => ({ tanggal: r.tanggal, deskripsi: r.deskripsi, nominal: r.nominal }),
     })">
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="card card-pad">
            <p class="text-sm text-ink-500">Total tercatat</p>
            <p class="mt-1 font-display text-3xl font-bold" x-text="$rp(rows.reduce((s, r) => s + r.nominal, 0))"></p>
        </div>
        <div class="card card-pad">
            <p class="text-sm text-ink-500">Jumlah catatan</p>
            <p class="mt-1 font-display text-3xl font-bold" x-text="rows.length"></p>
        </div>
    </div>

    <div class="card">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4">
            <div class="relative flex-1 sm:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" x-model="q" class="input pl-9" placeholder="Cari keterangan…" aria-label="Cari">
            </div>
            <button type="button" class="btn-primary ml-auto" @click="tambah()"><x-icon name="plus" class="h-4 w-4" /> Catat Modal</button>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Tanggal</th><th>Keterangan</th><th class="num">Nominal</th><th class="w-24"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="4"><div class="skeleton h-6"></div></td></tr></template>
                    <template x-for="r in bagian(tampil)" :key="r.id">
                        <tr>
                            <td class="whitespace-nowrap" x-text="util.tanggal(r.tanggal)"></td>
                            <td x-text="r.deskripsi"></td>
                            <td class="num font-semibold" x-text="$rp(r.nominal)"></td>
                            <td class="whitespace-nowrap text-right">
                                <button type="button" class="btn-icon" @click="ubah(r)" aria-label="Ubah"><x-icon name="pencil" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon hover:text-red-600" @click="hapus(r)" aria-label="Hapus"><x-icon name="trash" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !tampil.length"><tr><td colspan="4" class="py-10 text-center text-ink-500">Belum ada catatan modal.</td></tr></template>
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['page' => 'halAktif(tampil)', 'last' => 'util.jumlahHalaman(tampil, perHal)', 'count' => 'tampil.length', 'set' => '((n) => pindah(n, tampil))'])
    </div>

    <x-dialog show="open" title="Modal">
        <x-slot name="heading"><span x-text="editId ? 'Ubah catatan modal' : 'Catat modal'"></span></x-slot>
        <form id="form-modal" @submit.prevent="simpan()" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="m-tgl" class="label">Tanggal</label>
                    <input id="m-tgl" type="date" x-model="form.tanggal" class="input" required>
                </div>
                <div>
                    <label for="m-nom" class="label">Nominal (Rp)</label>
                    <input id="m-nom" type="number" inputmode="numeric" min="1" x-model.number="form.nominal" class="input" :class="err('nominal') && 'input-error'" required>
                    <p class="error-text" x-text="err('nominal')"></p>
                </div>
            </div>
            <div>
                <label for="m-desk" class="label">Keterangan</label>
                <input id="m-desk" x-model="form.deskripsi" class="input" required maxlength="500" placeholder="Mis. Modal awal laci kasir">
                <p class="error-text" x-text="err('deskripsi')"></p>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="open = false">Batal</button>
            <button type="submit" form="form-modal" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan'"></button>
        </x-slot>
    </x-dialog>
</div>
@endsection

@extends('layouts.panel')

@section('title', 'Kategori')
@section('subtitle', 'Kelompok menu yang tampil sebagai filter di layar kasir')

@section('content')
<div x-data="crud({ base: '/kategori', kosong: { nama: '' }, label: 'kategori', map: r => ({ nama: r.nama }) })">
    <div class="card">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4">
            <div class="relative flex-1 sm:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" x-model="q" class="input pl-9" placeholder="Cari kategori…" aria-label="Cari kategori">
            </div>
            <button type="button" class="btn-primary ml-auto" @click="tambah()"><x-icon name="plus" class="h-4 w-4" /> Tambah Kategori</button>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Nama kategori</th><th class="num">Jumlah produk</th><th class="w-24"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="3"><div class="skeleton h-6"></div></td></tr></template>
                    <template x-for="r in tampil" :key="r.id">
                        <tr>
                            <td class="font-semibold" x-text="r.nama"></td>
                            <td class="num" x-text="r.jumlah_produk"></td>
                            <td class="whitespace-nowrap text-right">
                                <button type="button" class="btn-icon" @click="ubah(r)" :aria-label="`Ubah ${r.nama}`"><x-icon name="pencil" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon hover:text-red-600" @click="hapus(r)" :aria-label="`Hapus ${r.nama}`"><x-icon name="trash" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !tampil.length"><tr><td colspan="3" class="py-10 text-center text-ink-500">Belum ada kategori.</td></tr></template>
                </tbody>
            </table>
        </div>
    </div>

    <x-dialog show="open" title="Kategori">
        <x-slot name="heading"><span x-text="editId ? 'Ubah kategori' : 'Kategori baru'"></span></x-slot>
        <form id="form-kategori" @submit.prevent="simpan()">
            <label for="k-nama" class="label">Nama kategori</label>
            <input id="k-nama" x-model="form.nama" class="input" :class="err('nama') && 'input-error'" required maxlength="100" placeholder="Mis. Minuman">
            <p class="error-text" x-text="err('nama')"></p>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="open = false">Batal</button>
            <button type="submit" form="form-kategori" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan'"></button>
        </x-slot>
    </x-dialog>
</div>
@endsection

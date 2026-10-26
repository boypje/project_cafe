@extends('layouts.panel')

@section('title', 'Produk & Resep')
@section('subtitle', 'Harga, HPP, stok, dan komposisi paket yang otomatis memotong stok bahan')

@section('content')
<div x-data="produk()">
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <button type="button" class="card card-pad text-left transition hover:border-ink-300" @click="status = ''">
            <p class="text-sm text-ink-500">Total produk</p>
            <p class="mt-1 font-display text-3xl font-bold" x-text="ringkas.total"></p>
        </button>
        <button type="button" class="card card-pad text-left transition hover:border-brand-300" @click="status = 'paket'" :class="status === 'paket' && 'ring-2 ring-brand-400'">
            <p class="text-sm text-ink-500">Paket / resep</p>
            <p class="mt-1 font-display text-3xl font-bold text-brand-700" x-text="ringkas.paket"></p>
        </button>
        <button type="button" class="card card-pad text-left transition hover:border-amber-300" @click="status = 'menipis'" :class="status === 'menipis' && 'ring-2 ring-amber-400'">
            <p class="text-sm text-ink-500">Stok menipis / habis</p>
            <p class="mt-1 font-display text-3xl font-bold text-amber-700" x-text="ringkas.menipis"></p>
        </button>
        <div class="card card-pad">
            <p class="text-sm text-ink-500">Belum ada HPP</p>
            <p class="mt-1 font-display text-3xl font-bold" :class="ringkas.tanpaHpp ? 'text-red-600' : ''" x-text="ringkas.tanpaHpp"></p>
            <p class="text-xs text-ink-400">Isi HPP agar laba akurat</p>
        </div>
    </div>

    <div class="card" x-init="$watch('kat', () => hal = 1); $watch('status', () => hal = 1)">
        <div class="flex flex-wrap items-end gap-3 border-b border-ink-100 p-4">
            <div class="min-w-[200px] flex-1 sm:max-w-xs">
                <label for="cari-produk" class="label">Cari</label>
                <input id="cari-produk" type="search" x-model="q" class="input py-2" placeholder="Nama produk…">
            </div>
            <div>
                <label for="f-kat" class="label">Kategori</label>
                <select id="f-kat" x-model="kat" class="input py-2">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)<option value="{{ $k->id_category }}">{{ $k->nama_kategori }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="f-status" class="label">Tampilkan</label>
                <select id="f-status" x-model="status" class="input py-2">
                    <option value="">Semua</option>
                    <option value="paket">Paket / resep</option>
                    <option value="menipis">Stok menipis</option>
                    <option value="nonaktif">Tidak tampil di kasir</option>
                </select>
            </div>
            <button type="button" class="btn-primary ml-auto" @click="tambah()"><x-icon name="plus" class="h-4 w-4" /> Tambah Produk</button>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Produk</th><th>Kategori</th><th class="num">Harga jual</th><th class="num">HPP</th><th class="num">Margin</th><th>Stok</th><th class="w-24"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="7"><div class="space-y-2 py-2"><div class="skeleton h-5"></div><div class="skeleton h-5 w-2/3"></div></div></td></tr>
                    </template>
                    <template x-for="p in bagian(daftar)" :key="p.id">
                        <tr :class="!p.tampil_kasir && 'opacity-60'">
                            <td>
                                <p class="font-semibold text-ink-900" x-text="p.nama"></p>
                                <p x-show="p.komponen.length" class="mt-0.5 text-xs text-ink-500">
                                    <x-icon name="layers" class="-mt-0.5 mr-0.5 inline h-3.5 w-3.5 text-brand-600" />
                                    <span x-text="p.komponen.map(k => (k.jumlah > 1 ? k.jumlah + '× ' : '') + k.nama).join(' + ')"></span>
                                </p>
                                <p x-show="!p.tampil_kasir" class="mt-0.5 text-xs text-ink-400">Disembunyikan dari kasir</p>
                            </td>
                            <td class="text-ink-600" x-text="p.kategori || '-'"></td>
                            <td class="num font-semibold" x-text="rupiah(p.harga_jual)"></td>
                            <td class="num" :class="!p.hpp && 'text-ink-300'">
                                <span x-text="p.hpp ? rupiah(p.hpp) : 'belum diisi'"></span>
                                <span x-show="p.komponen.length && !p.harga_pokok && p.hpp" class="block text-[11px] text-ink-400">dari resep</span>
                            </td>
                            <td class="num">
                                <span x-show="p.margin !== null" :class="p.margin < 30 ? 'text-red-600' : (p.margin < 50 ? 'text-amber-700' : 'text-emerald-700')" class="font-semibold" x-text="angka(p.margin, 1) + '%'"></span>
                                <span x-show="p.margin === null" class="text-ink-300">—</span>
                            </td>
                            <td><span :class="statusStok(p).kelas" x-text="statusStok(p).teks"></span></td>
                            <td class="whitespace-nowrap text-right">
                                <button type="button" class="btn-icon" @click="ubah(p)" :aria-label="`Ubah ${p.nama}`"><x-icon name="pencil" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon hover:text-red-600" @click="hapus(p)" :aria-label="`Hapus ${p.nama}`"><x-icon name="trash" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !daftar.length"><tr><td colspan="7" class="py-10 text-center text-ink-500">Tidak ada produk yang cocok.</td></tr></template>
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['page' => 'halAktif(daftar)', 'last' => 'util.jumlahHalaman(daftar, perHal)', 'count' => 'daftar.length', 'set' => '((n) => pindah(n, daftar))'])
    </div>

    <x-dialog show="open" title="Produk" size="max-w-2xl">
        <x-slot name="heading"><span x-text="editId ? 'Ubah produk' : 'Produk baru'"></span></x-slot>
        <form id="form-produk" @submit.prevent="simpan()" class="space-y-6">
            <section class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="p-nama" class="label">Nama produk</label>
                    <input id="p-nama" x-model="form.nama" class="input" :class="err('nama') && 'input-error'" required maxlength="255" placeholder="Mis. Paket Nasi + Bebek Endul">
                    <p class="error-text" x-text="err('nama')"></p>
                </div>
                <div>
                    <label for="p-kat" class="label">Kategori</label>
                    <select id="p-kat" x-model="form.id_category" class="input" :class="err('id_category') && 'input-error'" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)<option value="{{ $k->id_category }}">{{ $k->nama_kategori }}</option>@endforeach
                    </select>
                    <p class="error-text" x-text="err('id_category')"></p>
                </div>
                <div>
                    <label for="p-satuan" class="label">Satuan</label>
                    <input id="p-satuan" x-model="form.satuan" list="satuan-list" class="input" maxlength="20">
                    <datalist id="satuan-list"><option value="porsi"><option value="pcs"><option value="botol"><option value="gelas"><option value="bungkus"><option value="kg"></datalist>
                </div>
                <div>
                    <label for="p-jual" class="label">Harga jual (Rp)</label>
                    <input id="p-jual" type="number" inputmode="numeric" min="0" x-model.number="form.harga_jual" class="input" :class="err('harga_jual') && 'input-error'" required>
                    <p class="error-text" x-text="err('harga_jual')"></p>
                </div>
                <div>
                    <label for="p-pokok" class="label">HPP / harga pokok (Rp)</label>
                    <input id="p-pokok" type="number" inputmode="numeric" min="0" x-model.number="form.harga_pokok" class="input"
                           :placeholder="form.komponen.length ? `Otomatis dari resep: ${rupiah(hppKomponen)}` : 'Biaya per porsi'">
                    <p class="mt-1 text-xs" x-show="marginForm !== null" :class="marginForm < 30 ? 'text-red-600' : 'text-emerald-700'">
                        Margin <strong x-text="angka(marginForm, 1) + '%'"></strong>
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-brand-100 bg-brand-50/40 p-4">
                <div class="flex items-start gap-3">
                    <x-icon name="layers" class="mt-0.5 h-5 w-5 shrink-0 text-brand-700" />
                    <div class="flex-1">
                        <h3 class="font-semibold">Resep / komponen paket</h3>
                        <p class="text-xs text-ink-500">Jika diisi, setiap produk ini terjual akan memotong stok komponennya (mis. 1 Nasi + 1 Bebek Endul), bukan stok produk ini.</p>
                    </div>
                </div>
                <ul class="mt-3 space-y-2" x-show="form.komponen.length">
                    <template x-for="(k, i) in form.komponen" :key="k.id">
                        <li class="flex items-center gap-3 rounded-xl bg-white px-3 py-2 shadow-sm">
                            <span class="flex-1 text-sm font-medium" x-text="k.nama"></span>
                            <label class="sr-only" :for="`komp-${k.id}`">Jumlah</label>
                            <input :id="`komp-${k.id}`" type="number" min="1" max="99" x-model.number="k.jumlah" class="w-20 rounded-lg border-ink-200 py-1.5 text-center text-sm">
                            <span class="w-8 text-xs text-ink-400">× </span>
                            <button type="button" class="btn-icon h-8 w-8 hover:text-red-600" @click="hapusKomponen(i)" :aria-label="`Hapus komponen ${k.nama}`"><x-icon name="x" class="h-4 w-4" /></button>
                        </li>
                    </template>
                </ul>
                <div class="relative mt-3" x-data="{ buka: false }" @click.outside="buka = false">
                    <label for="cari-komp" class="sr-only">Tambah komponen</label>
                    <input id="cari-komp" x-model="cariKomponen" @focus="buka = true" @input="buka = true" class="input py-2" placeholder="+ Tambah komponen (ketik nama bahan/produk)…" autocomplete="off">
                    <ul x-show="buka && pilihanKomponen.length" x-cloak class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-ink-100 bg-white py-1 shadow-lift" role="listbox">
                        <template x-for="p in pilihanKomponen" :key="p.id">
                            <li><button type="button" class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-brand-50" @click="tambahKomponen(p); buka = false">
                                <span x-text="p.nama"></span><span class="text-xs text-ink-400" x-text="p.kelola_stok ? `stok ${p.stok}` : 'stok tidak dihitung'"></span>
                            </button></li>
                        </template>
                    </ul>
                </div>
                <p class="error-text" x-text="err('komponen.0.id')"></p>
            </section>

            <section class="grid gap-4 sm:grid-cols-3" x-show="!form.komponen.length">
                <div>
                    <label for="p-stok" class="label" x-text="editId ? 'Stok saat ini' : 'Stok awal'"></label>
                    <input id="p-stok" type="number" inputmode="numeric" x-model.number="form.stok" class="input" :disabled="!form.kelola_stok">
                    <p class="mt-1 text-[11px] text-ink-400" x-show="editId">Perubahan dicatat sebagai stok opname.</p>
                </div>
                <div>
                    <label for="p-min" class="label">Batas menipis</label>
                    <input id="p-min" type="number" inputmode="numeric" min="0" x-model.number="form.stok_minimum" class="input" :disabled="!form.kelola_stok">
                </div>
                <div class="flex items-end pb-2.5">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" x-model="form.kelola_stok" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500"> Hitung stok
                    </label>
                </div>
            </section>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" x-model="form.tampil_kasir" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                Tampilkan di layar kasir <span class="text-ink-400">(matikan untuk bahan yang tidak dijual satuan)</span>
            </label>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="open = false">Batal</button>
            <button type="submit" form="form-produk" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan produk'"></button>
        </x-slot>
    </x-dialog>
</div>
@endsection

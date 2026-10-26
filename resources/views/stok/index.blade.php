@extends('layouts.panel')

@section('title', 'Stok & Mutasi')
@section('subtitle', 'Catat barang masuk, barang rusak, dan stok opname — semua perubahan terekam')

@section('actions')
    <button type="button" class="btn-secondary hidden md:inline-flex" onclick="document.dispatchEvent(new CustomEvent('stok:ekspor'))"><x-icon name="download" class="h-4 w-4" /> Ekspor stok</button>
@endsection

@section('content')
<div x-data="stok({ produk: @js($produk), tipe: @js($tipe), urls: { mutasi: '{{ route('stok.mutasi') }}', store: '{{ route('stok.store') }}' } })"
     @stok:ekspor.document="ekspor()">

    <div class="mb-6 grid gap-3 sm:grid-cols-3">
        <button type="button" class="card card-pad group flex items-center gap-4 text-left transition hover:border-emerald-300" @click="buka('masuk')">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700"><x-icon name="download" /></span>
            <span><span class="block font-semibold">Stok Masuk</span><span class="text-sm text-ink-500">Belanja / produksi bahan</span></span>
        </button>
        <button type="button" class="card card-pad group flex items-center gap-4 text-left transition hover:border-red-300" @click="buka('keluar')">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-700"><x-icon name="trash" /></span>
            <span><span class="block font-semibold">Rusak / Terbuang</span><span class="text-sm text-ink-500">Kurangi stok non-penjualan</span></span>
        </button>
        <button type="button" class="card card-pad group flex items-center gap-4 text-left transition hover:border-brand-300" @click="buka('koreksi')">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-700"><x-icon name="clipboard" /></span>
            <span><span class="block font-semibold">Stok Opname</span><span class="text-sm text-ink-500">Samakan dengan hitungan fisik</span></span>
        </button>
    </div>

    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <section class="card" aria-labelledby="judul-posisi">
            <div class="border-b border-ink-100 p-4">
                <h2 id="judul-posisi" class="card-title">Posisi stok</h2>
                <p class="text-xs text-ink-500">Bahan & produk satuan (paket mengikuti komponennya)</p>
            </div>
            <ul class="scroll-thin max-h-[560px] divide-y divide-ink-50 overflow-y-auto">
                <template x-for="p in produk" :key="p.id">
                    <li class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                        <button type="button" class="truncate text-left font-medium hover:text-brand-700 hover:underline" @click="f.produk = String(p.id)" x-text="p.nama" :title="`Lihat mutasi ${p.nama}`"></button>
                        <span class="shrink-0 font-semibold tabular-nums" :class="p.stok <= 0 ? 'text-red-600' : 'text-ink-800'" x-text="`${angka(p.stok)} ${p.satuan || ''}`"></span>
                    </li>
                </template>
            </ul>
        </section>

        <section class="card min-w-0" aria-labelledby="judul-mutasi">
            <div class="flex flex-wrap items-end gap-3 border-b border-ink-100 p-4">
                <h2 id="judul-mutasi" class="card-title mr-auto w-full sm:w-auto">Riwayat mutasi</h2>
                <div><label for="m-awal" class="label">Dari</label><input id="m-awal" type="date" x-model="f.awal" class="input py-2"></div>
                <div><label for="m-akhir" class="label">Sampai</label><input id="m-akhir" type="date" x-model="f.akhir" class="input py-2"></div>
                <div>
                    <label for="m-produk" class="label">Produk</label>
                    <select id="m-produk" x-model="f.produk" class="input py-2">
                        <option value="">Semua</option>
                        <template x-for="p in produk" :key="p.id"><option :value="String(p.id)" x-text="p.nama"></option></template>
                    </select>
                </div>
                <div>
                    <label for="m-tipe" class="label">Jenis</label>
                    <select id="m-tipe" x-model="f.tipe" class="input py-2">
                        <option value="">Semua</option>
                        @foreach ($tipe as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th class="num">Perubahan</th><th class="num">Stok akhir</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        <template x-if="loading"><tr><td colspan="6"><div class="skeleton h-6"></div></td></tr></template>
                        <template x-for="(m, i) in rows" :key="i">
                            <tr>
                                <td class="whitespace-nowrap text-ink-500" x-text="m.waktu"></td>
                                <td class="font-medium" x-text="m.produk"></td>
                                <td><span :class="{ 'badge-ok': m.tipe === 'masuk' || m.tipe === 'batal', 'badge-bad': m.tipe === 'keluar', 'badge-brand': m.tipe === 'koreksi', 'badge-muted': m.tipe === 'penjualan' }" x-text="m.tipe_label"></span></td>
                                <td class="num font-semibold" :class="m.jumlah < 0 ? 'text-red-600' : 'text-emerald-700'" x-text="(m.jumlah > 0 ? '+' : '') + angka(m.jumlah)"></td>
                                <td class="num" x-text="angka(m.stok_akhir)"></td>
                                <td class="text-ink-500"><span x-text="m.keterangan || '-'"></span> <span x-show="m.user" class="text-xs text-ink-400" x-text="`· ${m.user}`"></span></td>
                            </tr>
                        </template>
                        <template x-if="!loading && !rows.length"><tr><td colspan="6" class="py-10 text-center text-ink-500">Belum ada mutasi pada filter ini. Mutasi tercatat otomatis sejak versi baru ini dipasang.</td></tr></template>
                    </tbody>
                </table>
            </div>
            @include('partials.pager')
        </section>
    </div>

    <x-dialog show="open" title="Mutasi stok" size="max-w-2xl">
        <x-slot name="heading"><span x-text="tipeLabel[tipe]"></span></x-slot>
        <form id="form-stok" @submit.prevent="simpan()" class="space-y-4">
            <p class="rounded-xl bg-ink-50 px-4 py-3 text-sm text-ink-600">
                <span x-show="tipe === 'masuk'">Masukkan jumlah yang <strong>ditambahkan</strong>. Isi harga pokok baru bila harga belanja berubah — HPP produk ikut diperbarui.</span>
                <span x-show="tipe === 'keluar'">Masukkan jumlah yang <strong>rusak/terbuang</strong>. Stok akan dikurangi.</span>
                <span x-show="tipe === 'koreksi'">Masukkan <strong>hasil hitung fisik</strong>. Selisih terhadap stok sistem dicatat otomatis.
                    <button type="button" class="font-semibold text-brand-700 underline" @click="opnameSemua()">Isi semua produk</button></span>
            </p>
            <div class="relative" x-data="{ buka: false }" @click.outside="buka = false">
                <label for="cari-stok" class="label">Tambah produk</label>
                <input id="cari-stok" x-ref="cariProduk" x-model="cari" @focus="buka = true" @input="buka = true" @keydown.enter.prevent="pilihan[0] && pilih(pilihan[0])" class="input" placeholder="Ketik nama lalu Enter…" autocomplete="off">
                <ul x-show="buka && pilihan.length" x-cloak class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-ink-100 bg-white py-1 shadow-lift">
                    <template x-for="p in pilihan" :key="p.id">
                        <li><button type="button" class="flex w-full justify-between px-3 py-2 text-left text-sm hover:bg-brand-50" @click="pilih(p)">
                            <span x-text="p.nama"></span><span class="text-xs text-ink-400" x-text="`stok ${p.stok}`"></span>
                        </button></li>
                    </template>
                </ul>
            </div>
            <div class="overflow-x-auto" x-show="items.length">
                <table class="tbl">
                    <thead><tr><th>Produk</th><th class="num">Stok sistem</th><th class="num" x-text="tipe === 'koreksi' ? 'Hitung fisik' : 'Jumlah'"></th><th class="num" x-show="tipe === 'masuk'">HPP baru</th><th class="num" x-show="tipe === 'koreksi'">Selisih</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="(i, idx) in items" :key="i.id">
                            <tr>
                                <td class="font-medium" x-text="i.nama"></td>
                                <td class="num text-ink-500" x-text="angka(i.stok)"></td>
                                <td class="num"><input type="number" inputmode="numeric" min="0" x-model="i.jumlah" class="w-24 rounded-lg border-ink-200 py-1.5 text-right text-sm" :aria-label="`Jumlah ${i.nama}`"></td>
                                <td class="num" x-show="tipe === 'masuk'"><input type="number" inputmode="numeric" min="0" x-model="i.harga_pokok" class="w-28 rounded-lg border-ink-200 py-1.5 text-right text-sm" placeholder="opsional" :aria-label="`HPP ${i.nama}`"></td>
                                <td class="num font-semibold" x-show="tipe === 'koreksi'" :class="selisih(i) < 0 ? 'text-red-600' : (selisih(i) > 0 ? 'text-emerald-700' : 'text-ink-400')" x-text="(selisih(i) > 0 ? '+' : '') + selisih(i)"></td>
                                <td><button type="button" class="btn-icon h-8 w-8" @click="items.splice(idx, 1)" :aria-label="`Hapus ${i.nama}`"><x-icon name="x" class="h-4 w-4" /></button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div>
                <label for="stok-ket" class="label">Keterangan</label>
                <input id="stok-ket" x-model="keterangan" class="input" maxlength="255" placeholder="Mis. Belanja pasar pagi / opname akhir bulan">
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="open = false">Batal</button>
            <button type="submit" form="form-stok" class="btn-primary" :disabled="saving || !items.length" x-text="saving ? 'Menyimpan…' : `Simpan ${items.length} item`"></button>
        </x-slot>
    </x-dialog>
</div>
@endsection

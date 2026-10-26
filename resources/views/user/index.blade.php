@extends('layouts.panel')

@section('title', 'Pengguna')
@section('subtitle', 'Akun admin dan kasir yang dapat masuk ke aplikasi')

@section('content')
<div x-data="crud({
        base: '/user', label: 'pengguna', cari: ['name', 'email'],
        kosong: { name: '', email: '', level: 2, password: '' },
        map: r => ({ name: r.name, email: r.email, level: r.level, password: '' }),
     })">
    <div class="card">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4">
            <div class="relative flex-1 sm:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" x-model="q" class="input pl-9" placeholder="Cari nama atau email…" aria-label="Cari pengguna">
            </div>
            <button type="button" class="btn-primary ml-auto" @click="tambah()"><x-icon name="plus" class="h-4 w-4" /> Tambah Pengguna</button>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th class="w-24"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="4"><div class="skeleton h-6"></div></td></tr></template>
                    <template x-for="r in bagian(tampil)" :key="r.id">
                        <tr>
                            <td class="font-semibold"><span x-text="r.name"></span> <span x-show="r.saya" class="badge-muted ml-1">Anda</span></td>
                            <td class="text-ink-600" x-text="r.email"></td>
                            <td><span :class="r.level === 1 ? 'badge-brand' : 'badge-muted'" x-text="r.level === 1 ? 'Admin' : 'Kasir'"></span></td>
                            <td class="whitespace-nowrap text-right">
                                <button type="button" class="btn-icon" @click="ubah(r)" :aria-label="`Ubah ${r.name}`"><x-icon name="pencil" class="h-4 w-4" /></button>
                                <button type="button" class="btn-icon hover:text-red-600" @click="hapus(r)" x-show="!r.saya" :aria-label="`Hapus ${r.name}`"><x-icon name="trash" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['page' => 'halAktif(tampil)', 'last' => 'util.jumlahHalaman(tampil, perHal)', 'count' => 'tampil.length', 'set' => '((n) => pindah(n, tampil))'])
    </div>

    <x-dialog show="open" title="Pengguna">
        <x-slot name="heading"><span x-text="editId ? 'Ubah pengguna' : 'Pengguna baru'"></span></x-slot>
        <form id="form-user" @submit.prevent="simpan()" class="space-y-4">
            <div>
                <label for="u-nama" class="label">Nama</label>
                <input id="u-nama" x-model="form.name" class="input" required maxlength="255">
                <p class="error-text" x-text="err('name')"></p>
            </div>
            <div>
                <label for="u-email" class="label">Email (untuk masuk)</label>
                <input id="u-email" type="email" x-model="form.email" class="input" :class="err('email') && 'input-error'" required autocomplete="off">
                <p class="error-text" x-text="err('email')"></p>
            </div>
            <fieldset>
                <legend class="label">Peran</legend>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer rounded-xl border-2 p-3 text-sm focus-within:ring-2 focus-within:ring-brand-500" :class="form.level == 2 ? 'border-brand-600 bg-brand-50' : 'border-ink-100'">
                        <input type="radio" class="sr-only" value="2" x-model.number="form.level"><strong class="block">Kasir</strong><span class="text-xs text-ink-500">Layar kasir, pengeluaran, tutup kasir</span>
                    </label>
                    <label class="cursor-pointer rounded-xl border-2 p-3 text-sm focus-within:ring-2 focus-within:ring-brand-500" :class="form.level == 1 ? 'border-brand-600 bg-brand-50' : 'border-ink-100'">
                        <input type="radio" class="sr-only" value="1" x-model.number="form.level"><strong class="block">Admin</strong><span class="text-xs text-ink-500">Akses penuh termasuk laporan</span>
                    </label>
                </div>
            </fieldset>
            <div>
                <label for="u-pass" class="label" x-text="editId ? 'Password baru (kosongkan bila tidak diganti)' : 'Password'"></label>
                <input id="u-pass" type="password" x-model="form.password" class="input" :class="err('password') && 'input-error'" :required="!editId" minlength="6" autocomplete="new-password">
                <p class="error-text" x-text="err('password')"></p>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="open = false">Batal</button>
            <button type="submit" form="form-user" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan'"></button>
        </x-slot>
    </x-dialog>
</div>
@endsection

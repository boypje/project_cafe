@extends('layouts.base')

@section('title', 'Kasir')
@section('body-class', 'overflow-hidden')

@php
    $ikonMetode = ['Tunai' => 'cash', 'Debit' => 'card', 'QRIS' => 'qr', 'Transfer' => 'bank'];
@endphp

@section('body')
<div x-data="pos({
        urls: { menu: '{{ route('kasir.menu') }}', checkout: '{{ route('kasir.checkout') }}', riwayat: '{{ route('kasir.riwayat') }}' },
        pajak: {{ (float) $setting->pajak_persen }},
        blokir: {{ $setting->blokir_stok_habis ? 'true' : 'false' }},
     })" class="flex h-full">

    <p class="sr-only" aria-live="polite" x-text="pengumuman"></p>

    {{-- ============ MENU ============ --}}
    <main id="konten" class="flex min-w-0 flex-1 flex-col" tabindex="-1">
        <header class="border-b border-ink-100 bg-white/70 backdrop-blur">
            <div class="flex flex-wrap items-center gap-3 px-4 py-4 lg:px-8 lg:py-5">
                <div class="mr-auto flex items-center gap-3">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('dashboard') }}" class="btn-icon" aria-label="Kembali ke dashboard" title="Dashboard">
                            <x-icon name="left" />
                        </a>
                    @endif
                    <div>
                        <p class="font-display text-2xl font-bold leading-none text-brand-700 lg:text-[28px]">{{ $setting->nama_usaha }}</p>
                        <p class="mt-1 text-xs text-ink-500">Kasir: {{ auth()->user()->name }} · <span x-text="jam"></span></p>
                    </div>
                </div>

                <div class="order-last w-full sm:order-none sm:w-80 lg:w-96">
                    <label for="cari-menu" class="sr-only">Cari menu</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-400" />
                        <input id="cari-menu" x-ref="cari" type="search" x-model="q" placeholder="Cari menu…" autocomplete="off"
                               @keydown.enter.prevent="tambahPertama()" @keydown.escape="q = ''"
                               class="input rounded-2xl border-ink-100 bg-cream py-3 pl-11 pr-14">
                        <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-ink-200 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-ink-400 sm:block">F2</kbd>
                    </div>
                </div>

                <nav class="flex items-center gap-1" aria-label="Menu kasir">
                    <button type="button" class="btn-ghost px-3" @click="bukaRiwayat()" title="Transaksi hari ini">
                        <x-icon name="history" class="h-5 w-5" /><span class="hidden xl:inline">Riwayat</span>
                    </button>
                    <a href="{{ route('pengeluaran.index') }}" class="btn-ghost px-3" title="Catat pengeluaran">
                        <x-icon name="wallet" class="h-5 w-5" /><span class="hidden xl:inline">Pengeluaran</span>
                    </a>
                    <a href="{{ route('kasir.tutup') }}" class="btn-ghost px-3" title="Tutup kasir">
                        <x-icon name="clipboard" class="h-5 w-5" /><span class="hidden xl:inline">Tutup Kasir</span>
                    </a>
                    @unless (auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                            @csrf
                            <button class="btn-ghost px-3" title="Keluar"><x-icon name="logout" class="h-5 w-5" /><span class="sr-only">Keluar</span></button>
                        </form>
                    @endunless
                </nav>
            </div>

            <div class="scroll-thin flex gap-2 overflow-x-auto px-4 pb-4 lg:px-8" role="tablist" aria-label="Kategori menu">
                <button type="button" role="tab" class="chip" :class="kat === 'semua' && 'chip-active'" :aria-selected="(kat === 'semua').toString()" @click="kat = 'semua'">
                    <x-icon name="grid" class="h-4 w-4" /> Semua Menu
                    <span class="text-xs opacity-60" x-text="produk.length"></span>
                </button>
                <template x-for="k in kategori" :key="k.id">
                    <button type="button" role="tab" class="chip" :class="kat === k.id && 'chip-active'" :aria-selected="(kat === k.id).toString()" @click="kat = k.id">
                        <span class="h-2 w-2 rounded-full" :class="warna(k.id)"></span>
                        <span x-text="k.nama"></span>
                        <span class="text-xs opacity-60" x-text="jumlahPerKategori(k.id)"></span>
                    </button>
                </template>
            </div>
        </header>

        <div class="scroll-thin flex-1 overflow-y-auto px-4 pb-28 pt-5 lg:px-8 lg:pb-8">
            {{-- skeleton --}}
            <div x-show="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @for ($i = 0; $i < 10; $i++)
                    <div class="skeleton h-36 rounded-2xl"></div>
                @endfor
            </div>

            <div x-show="!loading && daftar.length === 0" x-cloak class="flex flex-col items-center py-20 text-center text-ink-500">
                <x-icon name="search" class="mb-3 h-10 w-10 text-ink-300" />
                <p class="font-semibold text-ink-700">Menu tidak ditemukan</p>
                <p class="text-sm">Coba kata kunci lain atau pilih kategori “Semua Menu”.</p>
            </div>

            <ul x-show="!loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5" role="list">
                <template x-for="p in daftar" :key="p.id">
                    <li>
                        <button type="button" @click="tambah(p)"
                                :disabled="blokir && sisa(p) !== null && sisa(p) <= 0"
                                :aria-label="`Tambah ${p.nama}, ${rupiah(p.harga)}`"
                                class="group relative flex h-full w-full flex-col rounded-2xl border bg-white p-4 text-left shadow-soft transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lift disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                                :class="diKeranjang(p.id) ? 'border-brand-400 ring-1 ring-brand-400' : 'border-ink-100'">
                            <span x-show="diKeranjang(p.id)" x-cloak
                                  class="absolute -right-2 -top-2 flex h-7 min-w-[28px] items-center justify-center rounded-full bg-ink-900 px-2 text-xs font-bold text-white shadow"
                                  :class="bump === p.id && 'animate-bump'" x-text="diKeranjang(p.id)"></span>

                            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-ink-400">
                                <span class="h-1.5 w-1.5 rounded-full" :class="warna(p.kategori)"></span>
                                <span x-text="namaKategori(p.kategori)"></span>
                                <span x-show="p.paket" class="badge-brand ml-auto normal-case tracking-normal">Paket</span>
                            </span>
                            <span class="mt-2 font-display text-[17px] font-semibold leading-snug text-ink-900" x-text="p.nama"></span>
                            <span x-show="p.paket" class="mt-1 truncate text-xs text-ink-500" x-text="p.paket"></span>

                            <span class="mt-auto flex items-end justify-between gap-2 pt-4">
                                <span>
                                    <span class="block text-base font-bold text-brand-700" x-text="rupiah(p.harga)"></span>
                                    <template x-if="sisa(p) !== null">
                                        <span class="text-[11px] font-medium"
                                              :class="sisa(p) <= 0 ? 'text-red-600' : (sisa(p) <= p.minimum ? 'text-amber-700' : 'text-ink-400')"
                                              x-text="sisa(p) <= 0 ? 'Stok habis' : `Sisa ${sisa(p)}`"></span>
                                    </template>
                                </span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-icon name="plus" class="h-5 w-5" />
                                </span>
                            </span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </main>

    {{-- ============ PESANAN ============ --}}
    <div x-show="keranjangMobile" x-cloak x-transition.opacity class="fixed inset-0 z-30 bg-ink-950/50 lg:hidden" @click="keranjangMobile = false"></div>
    <aside class="fixed inset-y-0 right-0 z-40 flex w-full max-w-md translate-x-full flex-col border-l border-ink-100 bg-white shadow-lift transition-transform duration-200 lg:static lg:z-auto lg:w-[400px] lg:max-w-none lg:translate-x-0 lg:shadow-none xl:w-[440px]"
           :class="keranjangMobile && '!translate-x-0'" aria-labelledby="judul-pesanan">
        <div class="flex items-start justify-between gap-3 px-6 pb-4 pt-6">
            <div>
                <h2 id="judul-pesanan" class="flex items-center gap-2 font-display text-2xl font-bold">
                    <x-icon name="note" class="h-6 w-6 text-brand-700" /> Detail Pesanan
                </h2>
                <p class="mt-1 text-sm text-ink-500"><span x-text="jumlahItem"></span> item</p>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" class="btn-ghost btn-sm text-red-600 hover:bg-red-50 hover:text-red-700" x-show="cart.length" @click="kosongkan()">Kosongkan</button>
                <button type="button" class="btn-icon lg:hidden" @click="keranjangMobile = false" aria-label="Tutup pesanan"><x-icon name="x" /></button>
            </div>
        </div>

        <div class="grid grid-cols-[88px_1fr_76px] gap-3 border-y border-ink-100 bg-brand-50/50 px-6 py-4">
            <div>
                <label for="meja" class="label">Meja</label>
                <input id="meja" x-model="order.meja" class="input px-2 text-center font-semibold" placeholder="No" maxlength="20">
            </div>
            <div>
                <label for="pelanggan" class="label">Nama pelanggan</label>
                <input id="pelanggan" x-model="order.pelanggan" class="input" placeholder="Opsional" maxlength="100" autocomplete="off">
            </div>
            <div>
                <label for="pengunjung" class="label">Orang</label>
                <input id="pengunjung" type="number" inputmode="numeric" min="1" max="500" x-model.number="order.pengunjung" class="input px-2 text-center font-semibold">
            </div>
        </div>

        <div class="scroll-thin flex-1 overflow-y-auto px-4 py-3">
            <div x-show="!cart.length" class="flex h-full flex-col items-center justify-center px-6 py-10 text-center text-ink-400">
                <x-icon name="utensils" class="mb-3 h-10 w-10 text-ink-200" />
                <p class="font-semibold text-ink-600">Belum ada pesanan</p>
                <p class="mt-1 text-sm">Pilih menu di sebelah kiri, atau ketik nama menu lalu tekan <kbd class="rounded border border-ink-200 px-1 text-xs">Enter</kbd>.</p>
            </div>
            <ul class="space-y-2" role="list" aria-label="Item pesanan">
                <template x-for="item in cart" :key="item.id">
                    <li class="animate-pop-in rounded-2xl border border-ink-100 bg-white p-3.5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold leading-snug text-ink-900" x-text="item.nama"></p>
                                <p x-show="item.paket" class="mt-0.5 truncate text-xs text-ink-400" x-text="item.paket"></p>
                                <p class="mt-1 text-sm font-bold text-brand-700" x-text="rupiah(item.harga * item.jumlah)"></p>
                            </div>
                            <button type="button" class="btn-icon h-8 w-8 text-ink-300 hover:bg-red-50 hover:text-red-600" @click="hapus(item)" :aria-label="`Hapus ${item.nama}`">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs text-ink-400" x-text="`${rupiah(item.harga)} / item`"></span>
                            <div class="flex items-center rounded-xl border border-ink-200 bg-cream">
                                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-l-xl text-ink-600 hover:bg-ink-100" @click="ubahJumlah(item, -1)" :aria-label="`Kurangi ${item.nama}`"><x-icon name="minus" class="h-4 w-4" /></button>
                                <input type="number" inputmode="numeric" min="1" max="999" class="h-9 w-12 border-0 bg-transparent p-0 text-center text-sm font-bold focus:ring-0"
                                       :value="item.jumlah" @change="setJumlah(item, $event.target.value)" @focus="$event.target.select()" :aria-label="`Jumlah ${item.nama}`">
                                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-r-xl text-ink-600 hover:bg-ink-100" @click="ubahJumlah(item, 1)" :aria-label="`Tambah ${item.nama}`"><x-icon name="plus" class="h-4 w-4" /></button>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <div class="border-t border-ink-100 bg-white px-6 pb-6 pt-4 shadow-[0_-12px_24px_-16px_rgba(42,31,25,.15)]">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-medium tabular-nums" x-text="rupiah(subtotal)"></dd></div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-ink-500"><label for="diskon">Diskon</label></dt>
                    <dd class="flex items-center gap-1.5">
                        <div class="flex rounded-lg border border-ink-200 p-0.5 text-xs font-semibold" role="group" aria-label="Jenis diskon">
                            <button type="button" class="rounded-md px-2 py-1" :class="order.diskonTipe === 'rp' ? 'bg-ink-900 text-white' : 'text-ink-500'" @click="order.diskonTipe = 'rp'" :aria-pressed="(order.diskonTipe === 'rp').toString()">Rp</button>
                            <button type="button" class="rounded-md px-2 py-1" :class="order.diskonTipe === '%' ? 'bg-ink-900 text-white' : 'text-ink-500'" @click="order.diskonTipe = '%'" :aria-pressed="(order.diskonTipe === '%').toString()">%</button>
                        </div>
                        <input id="diskon" type="number" inputmode="numeric" min="0" x-model="order.diskonInput" placeholder="0"
                               class="w-24 rounded-lg border-ink-200 px-2 py-1 text-right text-sm tabular-nums focus:border-brand-500 focus:ring-brand-500">
                    </dd>
                </div>
                <div class="flex justify-between" x-show="diskon > 0"><dt class="text-ink-500">Potongan</dt><dd class="font-medium tabular-nums text-red-600" x-text="'− ' + rupiah(diskon)"></dd></div>
                @if ($setting->pajak_persen > 0)
                    <div class="flex justify-between"><dt class="text-ink-500">Pajak ({{ rtrim(rtrim(number_format($setting->pajak_persen, 2, ',', ''), '0'), ',') }}%)</dt><dd class="font-medium tabular-nums" x-text="rupiah(pajak)"></dd></div>
                @endif
                <div class="flex items-baseline justify-between border-t border-dashed border-ink-200 pt-3">
                    <dt class="text-base font-bold">Total</dt>
                    <dd class="font-display text-3xl font-bold tabular-nums text-brand-700" x-text="rupiah(total)"></dd>
                </div>
            </dl>
            <button type="button" class="btn-primary mt-4 w-full rounded-2xl py-4 text-base" @click="proses()" :disabled="!cart.length">
                Proses Pesanan <x-icon name="arrow-right" class="h-5 w-5" />
                <kbd class="ml-1 hidden rounded border border-white/20 px-1.5 text-[10px] font-semibold text-white/70 sm:inline">F9</kbd>
            </button>
        </div>
    </aside>

    {{-- Bar bawah untuk layar kecil --}}
    <div class="fixed inset-x-0 bottom-0 z-20 border-t border-ink-100 bg-white/95 p-3 backdrop-blur lg:hidden" x-show="!keranjangMobile">
        <button type="button" class="btn-primary w-full justify-between rounded-2xl py-3.5" @click="keranjangMobile = true">
            <span class="flex items-center gap-2"><x-icon name="note" class="h-5 w-5" /> Pesanan (<span x-text="jumlahItem"></span>)</span>
            <span class="font-bold" x-text="rupiah(total)"></span>
        </button>
    </div>

    {{-- ============ PEMBAYARAN ============ --}}
    <x-dialog show="bayarOpen" title="Pembayaran" size="max-w-xl">
        <form id="form-bayar" @submit.prevent="bayar()" class="space-y-5">
            <div class="rounded-2xl bg-ink-900 px-5 py-4 text-white">
                <p class="text-sm text-ink-300">Total tagihan · <span x-text="jumlahItem"></span> item</p>
                <p class="font-display text-4xl font-bold tabular-nums" x-text="rupiah(total)"></p>
            </div>

            <fieldset>
                <legend class="label">Metode pembayaran</legend>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($metode as $m)
                        <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-3 text-sm font-semibold transition focus-within:ring-2 focus-within:ring-brand-500"
                               :class="order.metode === '{{ $m }}' ? 'border-brand-600 bg-brand-50 text-brand-800' : 'border-ink-100 text-ink-600 hover:border-ink-200'">
                            <input type="radio" class="sr-only" name="metode" value="{{ $m }}" x-model="order.metode">
                            <x-icon :name="$ikonMetode[$m] ?? 'cash'" class="h-6 w-6" />
                            {{ $m }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div x-show="order.metode === 'Tunai'">
                <label for="diterima" class="label">Uang diterima</label>
                <input id="diterima" x-ref="inputDiterima" type="number" inputmode="numeric" min="0" x-model="order.diterima"
                       class="input py-3 text-right text-2xl font-bold tabular-nums" placeholder="0">
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <template x-for="n in nominalCepat" :key="n">
                        <button type="button" class="btn-secondary py-2 tabular-nums" @click="order.diterima = n" x-text="n === total ? 'Uang pas' : rupiah(n)"></button>
                    </template>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-2xl px-5 py-4"
                     :class="kembali >= 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-700'" aria-live="polite">
                    <span class="text-sm font-semibold" x-text="kembali >= 0 ? 'Kembalian' : 'Kurang'"></span>
                    <span class="font-display text-3xl font-bold tabular-nums" x-text="rupiah(Math.abs(kembali))"></span>
                </div>
            </div>

            <div>
                <label for="catatan" class="label">Catatan (opsional)</label>
                <input id="catatan" x-model="order.catatan" class="input" maxlength="255" placeholder="Mis. tanpa sambal, bungkus">
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-600">
                <input type="checkbox" x-model="cetakOtomatis" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                Cetak nota otomatis setelah bayar
            </label>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn-secondary" @click="bayarOpen = false">Kembali</button>
            <button type="submit" form="form-bayar" class="btn-brand min-w-[200px] py-3 text-base" :disabled="!bisaBayar">
                <span x-show="!memproses">Bayar <span x-text="rupiah(total)"></span></span>
                <span x-show="memproses" x-cloak>Memproses…</span>
            </button>
        </x-slot>
    </x-dialog>

    {{-- ============ BERHASIL ============ --}}
    <x-dialog show="hasil" title="Pembayaran berhasil" size="max-w-md" close="selesai()">
        <template x-if="hasil">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                    <x-icon name="check-circle" class="h-9 w-9" />
                </div>
                <p class="mt-3 text-sm text-ink-500"><span x-text="hasil.nomor"></span> · <span x-text="hasil.metode"></span> · <span x-text="hasil.item"></span> item</p>
                <p class="mt-1 font-display text-3xl font-bold tabular-nums" x-text="rupiah(hasil.bayar)"></p>
                <div x-show="hasil.metode === 'Tunai'" class="mt-5 rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-800">
                    <p class="text-sm font-semibold">Kembalian</p>
                    <p class="font-display text-4xl font-bold tabular-nums" x-text="rupiah(hasil.kembali)"></p>
                    <p class="mt-1 text-xs">Diterima <span x-text="rupiah(hasil.diterima)"></span></p>
                </div>
            </div>
        </template>
        <x-slot name="footer">
            <button type="button" class="btn-secondary flex-1" @click="cetakNota()">
                <x-icon name="printer" class="h-4 w-4" /> Cetak Nota <kbd class="text-[10px] text-ink-400">P</kbd>
            </button>
            <button type="button" x-ref="tombolBaru" class="btn-primary flex-1" @click="selesai()">
                Pesanan Baru <kbd class="text-[10px] text-white/60">Enter</kbd>
            </button>
        </x-slot>
    </x-dialog>

    {{-- ============ RIWAYAT HARI INI ============ --}}
    <x-dialog show="riwayatOpen" title="Transaksi hari ini" size="max-w-2xl">
        <div x-show="!riwayat.length" class="py-10 text-center text-sm text-ink-500">Belum ada transaksi hari ini.</div>
        <ul class="divide-y divide-ink-100" role="list">
            <template x-for="r in riwayat" :key="r.id">
                <li class="flex items-center gap-4 py-3">
                    <div class="w-14 text-sm font-semibold tabular-nums text-ink-500" x-text="r.jam"></div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold" x-text="r.nomor"></p>
                        <p class="truncate text-xs text-ink-500">
                            <span x-text="`${r.item} item · ${r.metode}`"></span>
                            <span x-show="r.meja" x-text="` · Meja ${r.meja}`"></span>
                            <span x-show="r.pelanggan" x-text="` · ${r.pelanggan}`"></span>
                            @if (auth()->user()->isAdmin()) <span x-text="` · ${r.kasir}`"></span> @endif
                        </p>
                    </div>
                    <p class="font-bold tabular-nums" x-text="rupiah(r.total)"></p>
                    <button type="button" class="btn-icon" @click="cetakNota(r.nota_url)" :aria-label="`Cetak ulang ${r.nomor}`"><x-icon name="printer" class="h-4 w-4" /></button>
                </li>
            </template>
        </ul>
        <x-slot name="footer">
            <p class="mr-auto text-sm text-ink-500">Total: <strong class="text-ink-900" x-text="rupiah(riwayat.reduce((s, r) => s + r.total, 0))"></strong></p>
            <button type="button" class="btn-secondary" @click="riwayatOpen = false">Tutup</button>
        </x-slot>
    </x-dialog>
</div>
@endsection

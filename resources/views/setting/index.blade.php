@extends('layouts.panel')

@section('title', 'Toko & Nota')
@section('subtitle', 'Identitas usaha, pajak, dan aturan kasir')

@section('content')
<div class="max-w-3xl" x-data="{
        form: {
            nama_usaha: @js($setting->nama_usaha), alamat: @js($setting->alamat), kota: @js($setting->kota),
            telepon: @js($setting->telepon), pajak_persen: @js((float) $setting->pajak_persen),
            footer_nota: @js($setting->footer_nota), blokir_stok_habis: @js((bool) $setting->blokir_stok_habis),
        },
        logo: @js($setting->path_logo), errors: {}, saving: false,
        async simpan() {
            this.saving = true; this.errors = {};
            const fd = new FormData();
            Object.entries(this.form).forEach(([k, v]) => fd.append(k, typeof v === 'boolean' ? (v ? 1 : 0) : (v ?? '')));
            if (this.$refs.logo.files[0]) fd.append('logo', this.$refs.logo.files[0]);
            try {
                const res = await util.api('{{ route('setting.update') }}', { method: 'POST', body: fd });
                this.logo = res.path_logo; this.$refs.logo.value = '';
                this.$store.toast.push(res.message);
            } catch (e) { this.errors = e.errors || {}; this.$store.toast.push(e.message, 'error'); }
            finally { this.saving = false; }
        },
     }">
    <form @submit.prevent="simpan()" class="space-y-6">
        <section class="card card-pad space-y-4">
            <h2 class="card-title">Identitas usaha</h2>
            <div class="flex items-center gap-4">
                <img :src="logo || '{{ asset('img/altari.png') }}'" alt="Logo usaha" class="h-16 w-16 rounded-2xl border border-ink-100 bg-white object-contain p-1">
                <div>
                    <label for="s-logo" class="label">Logo</label>
                    <input id="s-logo" x-ref="logo" type="file" accept="image/*" class="text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold">
                    <p class="error-text" x-text="errors.logo?.[0]"></p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="s-nama" class="label">Nama usaha</label>
                    <input id="s-nama" x-model="form.nama_usaha" class="input" required maxlength="255">
                </div>
                <div class="sm:col-span-2">
                    <label for="s-alamat" class="label">Alamat</label>
                    <input id="s-alamat" x-model="form.alamat" class="input" maxlength="500">
                </div>
                <div>
                    <label for="s-kota" class="label">Kota</label>
                    <input id="s-kota" x-model="form.kota" class="input" maxlength="20">
                </div>
                <div>
                    <label for="s-telp" class="label">Telepon</label>
                    <input id="s-telp" x-model="form.telepon" class="input" maxlength="50" inputmode="tel">
                </div>
            </div>
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="card-title">Kasir & nota</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="s-pajak" class="label">Pajak / PB1 (%)</label>
                    <input id="s-pajak" type="number" step="0.01" min="0" max="100" x-model.number="form.pajak_persen" class="input">
                    <p class="mt-1 text-xs text-ink-400">Isi 0 bila harga sudah termasuk pajak.</p>
                </div>
                <div>
                    <label for="s-footer" class="label">Pesan di bawah nota</label>
                    <input id="s-footer" x-model="form.footer_nota" class="input" maxlength="255" placeholder="Terima kasih atas kunjungan Anda">
                </div>
            </div>
            <label class="flex items-start gap-3 rounded-xl border border-ink-100 p-4">
                <input type="checkbox" x-model="form.blokir_stok_habis" class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm"><strong class="block">Tolak penjualan bila stok tidak cukup</strong>
                    <span class="text-ink-500">Jika mati, kasir tetap bisa menjual dan stok boleh minus (muncul peringatan).</span></span>
            </label>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan pengaturan'"></button>
        </div>
    </form>
</div>
@endsection

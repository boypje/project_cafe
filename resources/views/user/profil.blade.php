@extends('layouts.panel')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-xl" x-data="{
        form: { name: @js($profil->name), old_password: '', password: '', password_confirmation: '' },
        errors: {}, saving: false,
        async simpan() {
            this.saving = true; this.errors = {};
            try {
                const res = await util.api('{{ route('user.update_profil') }}', { method: 'POST', body: this.form });
                this.$store.toast.push(res.message);
                this.form.old_password = this.form.password = this.form.password_confirmation = '';
            } catch (e) { this.errors = e.errors || {}; this.$store.toast.push(e.message, 'error'); }
            finally { this.saving = false; }
        },
     }">
    <form @submit.prevent="simpan()" class="card card-pad space-y-5">
        <div>
            <label for="pr-nama" class="label">Nama</label>
            <input id="pr-nama" x-model="form.name" class="input" required maxlength="255">
        </div>
        <div>
            <p class="label">Email</p>
            <p class="text-sm text-ink-700">{{ $profil->email }}</p>
        </div>
        <fieldset class="space-y-4 border-t border-ink-100 pt-5">
            <legend class="font-semibold">Ganti password <span class="text-sm font-normal text-ink-400">(opsional)</span></legend>
            <div>
                <label for="pr-lama" class="label">Password lama</label>
                <input id="pr-lama" type="password" x-model="form.old_password" class="input" :class="errors.old_password && 'input-error'" autocomplete="current-password">
                <p class="error-text" x-text="errors.old_password?.[0]"></p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="pr-baru" class="label">Password baru</label>
                    <input id="pr-baru" type="password" x-model="form.password" class="input" minlength="6" autocomplete="new-password">
                    <p class="error-text" x-text="errors.password?.[0]"></p>
                </div>
                <div>
                    <label for="pr-konf" class="label">Ulangi password baru</label>
                    <input id="pr-konf" type="password" x-model="form.password_confirmation" class="input" autocomplete="new-password">
                </div>
            </div>
        </fieldset>
        <div class="flex justify-end">
            <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan…' : 'Simpan profil'"></button>
        </div>
    </form>
</div>
@endsection

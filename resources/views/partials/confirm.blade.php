<div x-data x-show="$store.confirm.open" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4"
     @keydown.escape.window="$store.confirm.open && $store.confirm.jawab(false)">
    <div class="absolute inset-0 bg-ink-950/50 backdrop-blur-[2px]" x-show="$store.confirm.open" x-transition.opacity @click="$store.confirm.jawab(false)"></div>
    <div x-show="$store.confirm.open" x-transition x-trap.noscroll="$store.confirm.open"
         role="alertdialog" aria-modal="true" aria-labelledby="confirm-judul" aria-describedby="confirm-pesan"
         class="relative w-full max-w-sm rounded-3xl bg-white p-6 shadow-lift">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full"
             :class="$store.confirm.bahaya ? 'bg-red-50 text-red-600' : 'bg-brand-50 text-brand-700'">
            <x-icon name="alert" />
        </div>
        <h2 id="confirm-judul" class="font-display text-xl font-semibold" x-text="$store.confirm.judul"></h2>
        <p id="confirm-pesan" class="mt-2 text-sm text-ink-600" x-text="$store.confirm.pesan"></p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="btn-secondary" @click="$store.confirm.jawab(false)">Batal</button>
            <button type="button" :class="$store.confirm.bahaya ? 'btn-danger' : 'btn-primary'" @click="$store.confirm.jawab(true)" x-text="$store.confirm.tombol"></button>
        </div>
    </div>
</div>

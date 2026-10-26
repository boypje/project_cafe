<div x-data class="pointer-events-none fixed inset-x-0 bottom-4 z-[60] flex flex-col items-center gap-2 px-4 sm:bottom-auto sm:left-auto sm:right-4 sm:top-4 sm:items-end"
     role="status" aria-live="polite">
    <template x-for="t in $store.toast.items" :key="t.id">
        <div class="pointer-events-auto flex w-full max-w-sm animate-slide-up items-start gap-3 rounded-2xl border bg-white px-4 py-3 text-sm shadow-lift"
             :class="{
                'border-emerald-200': t.tipe === 'success',
                'border-red-200': t.tipe === 'error',
                'border-amber-200': t.tipe === 'warning',
             }">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-white"
                  :class="{ 'bg-emerald-600': t.tipe === 'success', 'bg-red-600': t.tipe === 'error', 'bg-amber-500': t.tipe === 'warning' }">
                <x-icon name="check" class="h-3.5 w-3.5" x-show="t.tipe === 'success'" />
                <x-icon name="x" class="h-3.5 w-3.5" x-show="t.tipe === 'error'" />
                <x-icon name="alert" class="h-3.5 w-3.5" x-show="t.tipe === 'warning'" />
            </span>
            <p class="flex-1 font-medium text-ink-800" x-text="t.pesan"></p>
            <button type="button" class="-mr-1 text-ink-400 hover:text-ink-700" @click="$store.toast.close(t.id)" aria-label="Tutup notifikasi">
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>

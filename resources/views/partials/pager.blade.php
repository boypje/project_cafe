<nav class="flex items-center justify-between gap-3 border-t border-ink-100 px-4 py-3 text-sm" aria-label="Halaman" x-show="lastPage > 1">
    <p class="text-ink-500">Halaman <strong x-text="page"></strong> dari <span x-text="lastPage"></span> · <span x-text="total"></span> data</p>
    <div class="flex gap-1">
        <button type="button" class="btn-secondary btn-sm" @click="goto(page - 1)" :disabled="page <= 1"><x-icon name="left" class="h-4 w-4" /> Sebelumnya</button>
        <button type="button" class="btn-secondary btn-sm" @click="goto(page + 1)" :disabled="page >= lastPage">Berikutnya <x-icon name="right" class="h-4 w-4" /></button>
    </div>
</nav>

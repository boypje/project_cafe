@php
    // Ekspresi Alpine yang bisa diganti saat include:
    // $page = halaman aktif, $last = jumlah halaman, $count = jumlah data, $set = fungsi pindah halaman.
    $page = $page ?? 'page';
    $last = $last ?? 'lastPage';
    $count = $count ?? 'total';
    $set = $set ?? 'goto';
@endphp
<nav class="flex flex-wrap items-center justify-between gap-3 border-t border-ink-100 px-4 py-3 text-sm" aria-label="Navigasi halaman" x-show="{{ $last }} > 1">
    <p class="text-ink-500">Halaman <strong class="text-ink-800" x-text="{{ $page }}"></strong> dari <span x-text="{{ $last }}"></span> · <span x-text="{{ $count }}"></span> data</p>
    <div class="flex items-center gap-1">
        <button type="button" class="btn-icon h-8 w-8" @click="{{ $set }}({{ $page }} - 1)" :disabled="{{ $page }} <= 1" aria-label="Halaman sebelumnya">
            <x-icon name="left" class="h-4 w-4" />
        </button>
        <template x-for="(n, i) in util.nomorHalaman({{ $page }}, {{ $last }})" :key="i">
            <span>
                <span x-show="n === '…'" class="px-1 text-ink-400">…</span>
                <button type="button" x-show="n !== '…'" @click="{{ $set }}(n)"
                        class="h-8 min-w-[32px] rounded-lg px-2 text-sm font-semibold tabular-nums transition"
                        :class="n === {{ $page }} ? 'bg-ink-900 text-white' : 'text-ink-600 hover:bg-ink-100'"
                        :aria-current="n === {{ $page }} ? 'page' : null" :aria-label="`Halaman ${n}`" x-text="n"></button>
            </span>
        </template>
        <button type="button" class="btn-icon h-8 w-8" @click="{{ $set }}({{ $page }} + 1)" :disabled="{{ $page }} >= {{ $last }}" aria-label="Halaman berikutnya">
            <x-icon name="right" class="h-4 w-4" />
        </button>
    </div>
</nav>

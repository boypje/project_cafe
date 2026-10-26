{{-- Badge perubahan %. $expr = ekspresi Alpine yang menghasilkan persen (null = tidak ada pembanding), $kebalik = naik itu buruk. --}}
<span x-data="{ get v() { return {{ $expr }} } }" class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[11px] font-semibold"
      :class="v === null || Math.abs(v) < 0.05 ? 'bg-ink-100 text-ink-500' : ((v >= 0) !== {{ ($kebalik ?? false) ? 'true' : 'false' }} ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700')">
    <template x-if="v !== null && v >= 0"><x-icon name="up" class="h-3 w-3" /></template>
    <template x-if="v !== null && v < 0"><x-icon name="down" class="h-3 w-3" /></template>
    <span x-text="v === null ? 'baru' : Math.abs(v).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + '%'"></span>
</span>

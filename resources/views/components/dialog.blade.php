@props(['show', 'title', 'size' => 'max-w-lg', 'close' => null])
{{--
    Dialog modal aksesibel: fokus terkunci di dalam (x-trap), Esc menutup,
    klik latar menutup. $show = ekspresi Alpine boolean, $close = ekspresi penutup.
--}}
@php $close = $close ?? "$show = false"; @endphp
<template x-teleport="body">
    <div x-show="{{ $show }}" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
         @keydown.escape.window="if ({{ $show }}) { {{ $close }} }">
        <div x-show="{{ $show }}" x-transition.opacity class="absolute inset-0 bg-ink-950/50 backdrop-blur-[2px]" @click="{{ $close }}"></div>
        <div x-show="{{ $show }}" x-trap.noscroll="{{ $show }}"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             role="dialog" aria-modal="true" aria-label="{{ $title }}"
             {{ $attributes->merge(['class' => "relative flex max-h-[92vh] w-full $size flex-col overflow-hidden rounded-t-3xl bg-white shadow-lift sm:rounded-3xl"]) }}>
            <div class="flex items-center justify-between gap-4 border-b border-ink-100 px-6 py-4">
                <h2 class="font-display text-xl font-semibold">
                    @isset($heading) {{ $heading }} @else {{ $title }} @endisset
                </h2>
                <button type="button" class="btn-icon -mr-2" @click="{{ $close }}" aria-label="Tutup">
                    <x-icon name="x" />
                </button>
            </div>
            <div class="scroll-thin flex-1 overflow-y-auto px-6 py-5">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-ink-100 bg-ink-50/50 px-6 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</template>

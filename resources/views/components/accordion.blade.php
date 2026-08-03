<button @click="$dispatch('loader-top-on')" {{ $attributes->merge(['class' => 'text-left w-full cursor-pointer']) }}>
    <span class="text-rose-700 dark:text-rose-300 hover:text-cod-gray-800 dark:hover:text-cod-gray-100 smooth-500">
        {{ $title ?? '' }} 
    </span>
    @if ($toggler)
    <span class="text-xs text-rose-500"><i class="caret down icon"></i></span>
    @else
    <span class="text-xs text-rose-500"><i class="caret right icon"></i></span>
    @endif
</button>

{{-- Keep slot mounted (CSS hide) so Alpine/PiP state inside survives collapse --}}
<div
    x-init="$dispatch('loader-top-off')"
    @class([
        'flex flex-col w-full text-left gap-y-1',
        'hidden' => ! $toggler,
    ])
    @if (! $toggler) aria-hidden="true" @endif
>
    {{ $slot }}
</div>

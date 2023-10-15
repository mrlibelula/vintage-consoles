<a wire:navigate {{ $attributes->merge(['class' => 'text-sky-500 tracking-wide lowercase text-sm font-mono font-semibold leading-tight hover:text-rose-300 smooth-300 cursor-pointer']) }}>
    {{ $slot }}
</a>
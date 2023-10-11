<button {{ $attributes->merge(['class' => 'text-left w-full cursor-pointer']) }}>
    <span class=" text-rose-300 hover:text-cod-gray-100 transition duration-500 ease-in-out">
        {{ $title ?? '' }} 
    </span>
    @if ($toggler)
    <span class="text-xs text-rose-500"><i class="caret down icon"></i></span>
    @else
    <span class="text-xs text-rose-500"><i class="caret right icon"></i></span>
    @endif
</button>

@if ($toggler)
<div class="flex flex-col w-full text-left gap-y-1">
    {{ $slot }}
</div>
@endif
@props(['title'])
<div>
    @isset($title)
    <div class=" text-cod-gray-400 leading-none">
        {{ $title }}
    </div>
    @endisset
    <div class=" leading-none">
        {{ $slot }}
    </div>
</div>
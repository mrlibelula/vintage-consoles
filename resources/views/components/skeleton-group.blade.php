@props(['count' => 1])
<div {{ $attributes->merge(['class' => 'flex items-start gap-x-5']) }}>
    @for ($i = 0; $i <= $count; $i++)
    <div class=" w-[13rem] h-[16.5rem] rounded-lg bg-gradient-to-br from-cod-gray-700 via-cod-gray-700/50 animate-pulse mt-[1.2rem] flex flex-col items-center justify-between px-6 mb-5">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div class="flex bg-cod-gray-700 rounded-md w-[3.2rem] h-[4.5rem]"></div>
        <div class="flex mb-8 rounded-full bg-cod-gray-700 w-full h-4"></div>
    </div>
    @endfor
</div>
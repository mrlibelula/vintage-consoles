@props(['count' => 1])
<div {{ $attributes->merge(['class' => 'flex items-start gap-x-5']) }}>
    @for ($i = 0; $i <= $count - 1; $i++)
    <div class=" w-[11.2rem] h-[15rem] rounded-lg bg-gradient-to-br from-cod-gray-700 via-cod-gray-700/50 animate-pulse mt-[1rem] flex flex-col items-center justify-between px-6">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div class="flex bg-cod-gray-700 rounded-md w-[3.2rem] h-[4.5rem]"></div>
        <div class="flex mb-8 rounded-full bg-cod-gray-700 w-full h-4"></div>
    </div>
    @endfor
</div>
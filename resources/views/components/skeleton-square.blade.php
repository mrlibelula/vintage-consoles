@props(['count' => 2])
<div class="flex items-start gap-x-5 mt-[1.5rem]">
    @for ($i = 0; $i <= $count; $i++)
    <div class="flex flex-col justify-between items-center w-[8.5rem] h-[10.95rem] skeleton">
        <div></div>
        <div class="w-[80%]">
            <div class="my-4 w-full rounded-full bg-cod-gray-700 h-3">&nbsp;</div>
            <div class="my-4 w-full rounded-full bg-cod-gray-700 h-3">&nbsp;</div>
        </div>
    </div>
    @endfor
</div>
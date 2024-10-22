@props(['count' => 2])
<div class="flex flex-col gap-y-4 mt-[1.9rem] mb-[4.6rem]">
    @for ($i = 0; $i <= $count; $i++)
    <div class="flex items-center justify-start gap-x-6 w-full">
        <div class="w-[5rem] skeleton rounded-full h-[5.5rem]"></div>
        <div class="flex flex-col gap-y-3 w-full">
            <div class=" w-[50%] skeleton h-5"></div>
            <div class=" w-[50%] skeleton h-5"></div>
        </div>
        <div class="w-[10%] skeleton h-5 -mt-6"></div>
    </div>
    @endfor
</div>
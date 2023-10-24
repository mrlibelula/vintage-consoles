@props(['count' => 2])
<div class="flex flex-col gap-y-3 mt-4 mb-[4rem]">
    @for ($i = 0; $i <= $count; $i++)
    <div class="flex items-center justify-start gap-x-6 w-full">
        <div class="w-[5rem] skeleton rounded-full h-[5.5rem]"></div>
        <div class="flex flex-col gap-y-3 w-full">
            <div class=" w-[50%] skeleton h-5"></div>
            <div class=" w-[50%] skeleton h-5"></div>
        </div>
    </div>
    @endfor
</div>
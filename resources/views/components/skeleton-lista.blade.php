@props(['count' => 2])
<div class="flex w-full min-w-0 flex-col gap-y-4 py-4">
    @for ($i = 0; $i <= $count; $i++)
    <div class="flex min-w-0 w-full items-center justify-start gap-x-4 lg:gap-x-6">
        {{-- poster --}}
        <div class="h-[7rem] w-[5rem] shrink-0 rounded-md skeleton"></div>
        {{-- game data --}}
        <div class="flex min-w-0 flex-1 items-start gap-x-4">
            <div class="flex min-w-0 flex-1 flex-col gap-y-2 text-lg md:text-xl">
                <div class="h-4 w-[55%] max-w-full rounded-full skeleton md:h-5"></div>
                <div class="h-3 w-[40%] max-w-full rounded-full skeleton"></div>
                <div class="h-3 w-[18%] max-w-full rounded-full skeleton"></div>
            </div>
            <div class="hidden h-4 w-10 shrink-0 rounded-full skeleton px-2 md:block"></div>
        </div>
    </div>
    @endfor
</div>

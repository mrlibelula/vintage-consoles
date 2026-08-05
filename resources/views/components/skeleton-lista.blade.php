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

    {{-- pagination: caption + page boxes — mirrors vendor/livewire/tailwind.blade.php --}}
    <nav aria-hidden="true" class="flex items-center justify-between">
        {{-- mobile prev / next --}}
        <div class="flex flex-1 justify-between sm:hidden">
            <div class="h-9 w-24 rounded-md skeleton"></div>
            <div class="h-9 w-20 rounded-md skeleton"></div>
        </div>

        {{-- desktop: "Showing …" + page boxes --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div class="h-5 w-52 max-w-[55%] rounded-full skeleton"></div>
            <div class="inline-flex overflow-hidden rounded-md">
                <div class="h-9 w-9 shrink-0 skeleton"></div>
                @for ($p = 0; $p < 5; $p++)
                <div class="h-9 w-10 shrink-0 skeleton"></div>
                @endfor
                <div class="h-9 w-9 shrink-0 skeleton"></div>
            </div>
        </div>
    </nav>
</div>

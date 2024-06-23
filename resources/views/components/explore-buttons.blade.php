<div class="flex flex-col gap-y-2 md:gap-y-0 md:flex-row items-center gap-x-4">
    <a wire:navigate href="{{ route('genres') }}" class=" w-full text-center rounded-md border-2 border-sky-600 px-3 text-sky-600 dark:text-sky-400/80 hover:text-sky-700 dark:hover:text-sky-200 smooth-300 cursor-pointer dark:hover:border-sky-500 {{ request()->routeIs('genres') ? 'bg-sky-200 dark:bg-sky-900' : '' }}">
        Genres
    </a>
    <a wire:navigate href="{{ route('publishers') }}" class=" w-full text-center rounded-md border-2 border-lime-600 px-3  text-lime-600 dark:text-lime-400/80 hover:text-lime-700 dark:hover:text-lime-200 smooth-300 cursor-pointer dark:hover:border-lime-500 {{ request()->routeIs('publishers') ? 'bg-lime-200 dark:bg-lime-800' : '' }}">
        Publishers
    </a>
</div>
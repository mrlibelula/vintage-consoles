<div>
    <div x-cloak class="flex py-4 overflow-hidden overflow-x-auto flex-col px-6" 
    {{-- style="
        scrollbar-width: thin;
        scrollbar-color: #252f3f #13161c;
        " --}}
        >
        <div class="flex flex-no-wrap">
            @forelse ($data as $item)
                @livewire($template, [$template_var_name => (array)$item], key(uniqid()))
            @empty
                <div class="flex flex-col cursor-default rounded-xl py-6 w-full fade-75-indigo-cyan text-gray-400">
                    <div class="text-center text-4xl">
                        ¯\_(ツ)_/¯
                    </div>
                    <div class="flex justify-center text-xl">
                        <span class="my-auto">
                            press 
                        </span>
                        <span class="my-auto">
                            <svg class="w-6 h-6 mx-2 rounded-md bg-gray-700 p-0.5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </span>
                    </div>
                    <div class="my-auto mx-auto text-xl">
                        to add/remove movies
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

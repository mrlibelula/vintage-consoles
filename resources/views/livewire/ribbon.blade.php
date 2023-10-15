<div>
    <div class="flex pb-6 overflow-hidden overflow-x-auto flex-col">
        <div class="flex flex-no-wrap gap-x-2">
            @forelse ($data as $item)
                
                @livewire($template, [$template_var_name => (array)$item], uniqid())

            @empty
            <div class="flex flex-col cursor-default rounded-xl pb-6 w-full fade text-cod-gray-300">
                <div class="text-center text-4xl">
                    ¯\_(ツ)_/¯
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

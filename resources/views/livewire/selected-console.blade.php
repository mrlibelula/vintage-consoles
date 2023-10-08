<div class="flex flex-col gap-y-6 w-full bg-cod-gray-800/50 p-4 
    {{ $is_selected_tab_first ? 'rounded-b-xl rounded-tr-xl' : ($is_selected_tab_last ? 'rounded-b-xl rounded-tl-xl' : 'rounded-xl') }} 
    ">
    <div>
        {{ $selected_console['long_name'] }}
    </div>

    <div class="leading-none">
        {{ $selected_console['description'] }}
    </div>
</div>
@props(['width' => 60, 'height' => 50, 'rounded' => true, 'header' => true, 'icon', 'title', 'close_icon' => true, 'spinner' => 'top', 'actions', 'shadow' => true, 'loader' => true])
<div 
    x-cloak 
    x-data="{
        fixedModalOpen: false,
        closeFixedModal() {
            this.fixedModalOpen = false
            // Livewire.emit('fixedModalClosed')
            @this.dispatch('fixedModalClosed')
        }
    }"
    @open-fixed-modal.window="
        if ($event.detail != false) fixedModalOpen = true
    "
    @close-fixed-modal.window="
        closeFixedModal()
    "
    x-show="fixedModalOpen"
    @keydown.escape.window="closeFixedModal()"
    @keydown.left.window="$dispatch('keydownLeft')"
    @keydown.right.window="$dispatch('keydownRight')"
    x-transition:enter="transition ease-out duration-[5ms]" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-[5ms]"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    {{ $attributes->merge(['class' => 'fixed z-[60] inset-0 overflow-y-auto']) }}
    >
    <div class="flex items-end justify-center min-h-screen text-center sm:block overflow-hidden">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 backdrop-grayscale backdrop-contrast-[.75] backdrop-brightness-[.4] backdrop-blur-[4.5px]"></div>
        </div>
        <!-- This element is to trick the browser into centering the modal contents. -->
        @if ($width != 100)
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        @endif
        
        <div
            class="inline-flex flex-col align-middle bg-white dark:bg-black {{ $rounded ? 'rounded-lg' : 'rounded-none' }} text-left overflow-hidden {{ $shadow ? 'shadow-lg shadow-black' : '' }} transform transition-all sm:align-middle justify-between"
            style="
                width: {{ $width }}vw; 
                height: {{ $height }}vh;
            "
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">

            @if ($header)
            <!-- header -->
            <div class="flex relative px-6 py-4 justify-between">
                <div class="sm:hidden">
                    &nbsp;
                </div>
                <div class="sm:flex sm:items-start -mr-5 sm:-mr-0 w-full">
                    @if (isset($icon))
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-500_ sm:mx-0 sm:h-10 sm:w-10">
                        <!-- icon -->
                        <div class="h-6 w-6 text-rose-200">
                            {{ $icon }}
                        </div>
                    </div>
                    @endif
                    <div class="{{ isset($icon) ? 'mt-3' : '' }} sm:mt-0 w-full 
                        {{ isset($icon) ? 'sm:ml-4' : '' }} 
                        sm:text-left">
                        <h3 class=" w-full text-2xl text-center sm:text-left leading-6 text-cod-gray-100" id="modal-headline">
                            {{ $title ?? '' }}
                        </h3>
                        
                    </div>
                </div>
                @if ($close_icon)
                <!-- close icon -->
                <svg @click="closeFixedModal()" class="w-5 h-5 md:w-6 md:h-6 cursor-pointer hover:text-rose-500 smooth-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                @endif
            </div>
            @endif

            <!-- loader -->
            @if (!$slot->isNotEmpty())
            <span class="loader-71 absolute h-0"></span>
            @endif

            <!-- loader bool -->
            @if ($loader)
            <span class="loader-71 absolute h-0"></span>
            @endif
            
            <div class="h-full overflow-hidden overflow-y-auto">
                @if ($slot->isNotEmpty())

                {{ $slot }}

                @else
                <!-- loader libeflix -->
                <x-center>
                    @if ($spinner === 'default')
                    <x-spinner />
                    @elseif ($spinner === 'top')
                    
                    @else
                        @if (View::exists('components.' . $spinner))
                        <x-dynamic-component :component="$spinner" />
                        @else
                        <x-spinner />
                        @endif
                    @endif
                </x-center>
                @endif
            </div>

            @if (isset($actions))
            <div class="bg-cod-cod-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                
                {{ $actions }}
                
            </div>
            @endif
        </div>

    </div>
</div>
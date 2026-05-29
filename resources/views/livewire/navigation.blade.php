<nav 
    x-data="{ 
        open: false, 
        loaderTop: false
    }" 
    @loader-top-off.window="loaderTop = false"
    @loader-top-on.window="loaderTop = true"
    @loader-top-toggle.window="loaderTop = !loaderTop"
    class="fixed w-full top-0 z-[65] bg-cod-gray-200 dark:bg-cod-gray-900"
    {{-- :class="!darkMode ? 'fade-nav-light' : 'fade-nav'" --}}
>
    <!-- loader-top -->
    <template x-cloak x-if="loaderTop">
        <div class="flex items-center justify-center z-50">
            <span class="loader-71 absolute h-0"></span>
        </div>
    </template>

    <!-- Primary Navigation Menu -->
    <div class="max-w-5xl mx-auto px-4 xl:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex w-full">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <x-logo />
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 xl:-my-px xl:ml-10 xl:flex">
                    <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home') || request()->routeIs('dashboard') || request()->routeIs('play')">
                        {{ __('Consoles') }}
                    </x-nav-link>

                    <x-nav-link href="{{ route('genres') }}" :active="request()->routeIs('publishers') || request()->routeIs('genres')">
                        {{ __('Explore') }}
                    </x-nav-link>
                    
                </div>
            </div>

            <!-- search bar -->
            <div class="relative w-full flex gap-x-2 items-center justify-between">
                <form autocomplete="off" class="absolute w-full" @submit.prevent>
                    <x-input 
                        @keydown.enter.prevent="$dispatch('loader-top-on'); $wire.set('search', $event.target.value, true)" 
                        wire:model.live.debounce.500ms="search" 
                        class="h-[2.2rem] w-full text-xl px-8" 
                        placeholder="Search game"
                        name="game_search_query_{{ uniqid() }}"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                        role="search"
                        aria-label="Search games"
                        data-lpignore="true"
                        data-form-type="other"
                    />
                </form>
                <x-icons.magnify class=" absolute mx-2 w-[0.8rem] h-[0.8rem] text-cod-gray-300" />
                <x-icons.x wire:click="clearSearchResults" class="absolute right-0 mx-2 w-[0.8rem] h-[0.8rem] text-cod-gray-300 cursor-pointer" />
            </div>

            <div class="hidden xl:flex xl:items-center xl:ml-6 xl:gap-x-4">
                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-2xl leading-4 font-medium rounded-md text-cod-gray-500 dark:text-cod-gray-400 bg-cod-gray-100/60 dark:bg-cod-gray-900 hover:text-cod-gray-700 dark:hover:text-cod-gray-300 focus:outline-none focus:bg-cod-gray-50 dark:focus:bg-cod-gray-700 active:bg-cod-gray-50 dark:active:bg-cod-gray-700 transition ease-in-out duration-150">
                                        {{ Auth::user()->currentTeam->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-cod-gray-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    <!-- Team Switcher -->
                                    @if (Auth::user()->allTeams()->count() > 1)
                                        <div class="border-t border-cod-gray-200 dark:border-cod-gray-600"></div>

                                        <div class="block px-4 py-2 text-cod-gray-400">
                                            {{ __('Switch Teams') }}
                                        </div>

                                        @foreach (Auth::user()->allTeams() as $team)
                                            <x-switchable-team :team="$team" />
                                        @endforeach
                                    @endif
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif
                
                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-2xl border-2 border-transparent rounded-full focus:outline-none focus:border-cod-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Str::limit(Str::of(Auth::user()->name)->explode(' ')->first(), 10, '...') }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md mt-0.5">
                                    <button type="button" class="inline-flex mr-4 items-center px-3 py-2 border border-transparent text-xl leading-4 font-medium rounded-md text-cod-gray-500 dark:text-cod-gray-400  hover:text-cod-gray-700 dark:hover:text-cod-gray-300 focus:outline-none focus:bg-cod-gray-50 dark:focus:bg-cod-gray-700 active:bg-cod-gray-50 dark:active:bg-cod-gray-700 transition ease-in-out duration-150">
                                        {{ Str::limit(Str::of(Auth::user()->name)->explode(' ')->first(), 10, '...') }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-cod-gray-400 sepia_">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('my-saves') }}" wire:navigate>
                                {{ __('My Saves') }}
                            </x-dropdown-link>

                            <button
                                type="button"
                                class="block w-full px-4 py-2 text-left text-xl leading-5 text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-rose-600 dark:hover:text-white focus:outline-none focus:bg-cod-gray-100 dark:focus:bg-cod-gray-900 transition duration-150 ease-in-out sepia_"
                                x-data="{
                                  cursorStyle: localStorage.getItem('cursorStyle') || 'default',
                                  label() {
                                    return this.cursorStyle === 'alternate' ? 'Cursor: Alternate' : 'Cursor: Default';
                                  },
                                  toggle() {
                                    this.cursorStyle = this.cursorStyle === 'alternate' ? 'default' : 'alternate';
                                    localStorage.setItem('cursorStyle', this.cursorStyle);
                                    document.documentElement.dataset.cursorStyle = this.cursorStyle;
                                    $wire.setCursorStyle(this.cursorStyle);
                                  },
                                }"
                                x-text="label()"
                                @click="toggle()"
                            ></button>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-cod-gray-200 dark:border-cod-gray-600"></div>

                            @if (Auth::user()->hasRole('admin'))
                                <x-dropdown-link href="{{ route('admin.games') }}">
                                    {{ __('Game Manager') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.fonts') }}">
                                    {{ __('Font Manager') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.backup') }}">
                                    {{ __('Backup / Restore') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <a href="{{ route('logout') }}" class="block w-full px-4 py-2 text-left text-xl leading-5 text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-100 dark:hover:bg-rose-600 dark:hover:text-white focus:outline-none focus:bg-cod-gray-100 dark:focus:bg-cod-gray-900 transition duration-150 ease-in-out sepia_"
                                        @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </a>
                            </form>
                        </x-slot>
                    </x-dropdown>
                    @endauth
                </div>

                
            </div>

            <div class="hidden xl:flex gap-x-8">
                <x-nav-link href="{{ route('about') }}" :active="request()->routeIs('about')">
                    {{ __('About') }}
                </x-nav-link>
                @guest
                <x-nav-link href="{{ route('login') }}" :active="request()->routeIs('login')">
                    {{ __('Login') }}
                </x-nav-link>
                @endguest
                
            </div>
            <div class=" hidden xl:flex items-center">
                <x-theme-switcher class="ml-6 mt-0.5" />

            </div>
            

            <!-- Hamburger -->
            <div class="ml-4 flex items-center xl:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-cod-gray-400 dark:text-cod-gray-500 hover:text-cod-gray-500 dark:hover:text-cod-gray-400 hover:bg-cod-gray-100 dark:hover:bg-cod-gray-900 focus:outline-none focus:bg-cod-gray-100 dark:focus:bg-cod-gray-900 focus:text-cod-gray-500 dark:focus:text-cod-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- restore notification banner -->
    @if(isset($restoreNotification) && $restoreNotification)
    <div
        x-data="{ visible: true }"
        x-show="visible"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="w-full bg-amber-500/90 dark:bg-amber-600/90 text-amber-950 dark:text-amber-50 text-base py-2 px-4 flex items-center justify-between gap-x-4"
    >
        <div class="flex items-center gap-x-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $restoreNotification->data['message'] ?? 'Site data was recently restored from backup.' }}</span>
        </div>
        <button
            @click="visible = false; $wire.dismissRestoreNotification('{{ $restoreNotification->id }}')"
            class="shrink-0 hover:opacity-70 smooth-300"
            aria-label="Dismiss"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    <!-- search results -->
    @if ($search_results)
    <div class="fixed top-[4.1rem] z-40 flex items-center justify-center w-full px-2">
        <div wire:click.away="clearSearchResults" class="flex flex-col gap-y-2 fade max-w-[28rem] _max-w-fit w-full rounded-md border-4 border-cod-gray-600 min-h-fit min-h-[10rem]_ sm:min-h-[13rem]_ max-h-[10rem] sm:max-h-[13rem] py-2 px-2 shadow-md shadow-black leading-tight overflow-hidden overflow-y-auto">
            @foreach ($search_results as $result)
            <a wire:navigate href="{{ $this->gameRoute(['short_name' => $result['console_short_name']], ['id' => $result['game_id'], 'title' => $result['game_title']]) }}" @click="$dispatch('loader-top-on')" class="group flex items-center gap-x-3 justify-start text-left rounded-md hover:bg-cod-gray-700 smooth-300 cursor-pointer pr-2">
                <div class=" w-12 h-[4.4rem] overflow-hidden rounded-md">
                    <img class=" w-full h-full brightness-75 group-hover:brightness-100 smooth-300" src="{{ $result['game_poster'] }}" alt="{{ $result['game_title'] }}">
                </div>

                <div class="flex flex-col">
                    <div class=" text-rose-300 leading-none">
                        #{{ strtolower($result['console_short_name']) }}
                    </div>
                    <div class=" leading-none text-cod-gray-50">
                        {{ $result['game_title'] }}
                    </div>
                    <div class=" leading-none text-cod-gray-300/90">
                        {{ number_format($result['game_rating'] * 100, 0) }}%
                    </div>
                </div>

            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" 
        class="hidden xl:hidden bg-white dark:bg-cod-gray-800 m-2 rounded-md shadow overflow-hidden"
    >
        <div>
            <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home') || request()->routeIs('dashboard') || request()->routeIs('play')">
                {{ __('Consoles & Games') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('genres') }}" :active="request()->routeIs('genres') || request()->routeIs('publishers')">
                {{ __('Explore') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div>
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Str::limit(Str::of(Auth::user()->name)->explode(' ')->first(), 10, '...') }}" />
                    </div>
                @endif

                @auth
                <a wire.navigate href="{{ route('profile.show') }}">
                    <div class="font-medium text-base text-cod-gray-800 dark:text-cod-gray-200">{{ Str::limit(Str::of(Auth::user()->name)->explode(' ')->first(), 10, '...') }}</div>
                    <div class="font-medium text-xl text-cod-gray-500 mb-2">
                        {{ Auth::user()->email }}
                    </div>
                </a>
                @endauth
            </div>

            <div>
                <div @click="cycleTheme()" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent dark:border-transparent hover:border-cod-gray-300 hover:dark:border-rose-600 dark:border-cod-gray-600 text-left text-lg text-cod-gray-600 dark:text-cod-gray-400 hover:text-cod-gray-700 hover:dark:text-cod-gray-300 hover:bg-cod-gray-50 hover:dark:bg-rose-900/50 focus:outline-none focus:text-cod-gray-800 dark:focus:text-cod-gray-200 focus:bg-cod-gray-100 dark:focus:bg-cod-gray-900 focus:border-cod-gray-700 dark:focus:border-cod-gray-300 smooth-300 cursor-pointer">
                    <div class="flex items-center">
                        <div x-cloak x-show="theme === 'light'" class="text-lg">
                            {{ __('Light theme') }}
                        </div>
                        <div x-cloak x-show="theme === 'dark'" class="text-lg">
                            {{ __('Dark theme') }}
                        </div>
                        <div x-cloak x-show="theme === 'system'" class="text-lg">
                            {{ __('System theme') }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent dark:border-transparent hover:border-cod-gray-300 hover:dark:border-rose-600 dark:border-cod-gray-600 text-left text-lg text-cod-gray-600 dark:text-cod-gray-400 hover:text-cod-gray-700 hover:dark:text-cod-gray-300 hover:bg-cod-gray-50 hover:dark:bg-rose-900/50 focus:outline-none focus:text-cod-gray-800 dark:focus:text-cod-gray-200 focus:bg-cod-gray-100 dark:focus:bg-cod-gray-900 focus:border-cod-gray-700 dark:focus:border-cod-gray-300 smooth-300 cursor-pointer"
                    x-data="{
                      cursorStyle: localStorage.getItem('cursorStyle') || 'default',
                      label() {
                        return this.cursorStyle === 'alternate' ? 'Cursor: Alternate' : 'Cursor: Default';
                      },
                      toggle() {
                        this.cursorStyle = this.cursorStyle === 'alternate' ? 'default' : 'alternate';
                        localStorage.setItem('cursorStyle', this.cursorStyle);
                        document.documentElement.dataset.cursorStyle = this.cursorStyle;
                        $wire.setCursorStyle(this.cursorStyle);
                      },
                    }"
                    x-text="label()"
                    @click="toggle()"
                ></button>

                <!-- Account Management -->
                @guest
                <x-responsive-nav-link href="{{ route('login') }}">
                    {{ __('Login') }}
                </x-responsive-nav-link>
                @endguest

                @auth
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                @endauth

                @auth
                <x-responsive-nav-link href="{{ route('my-saves') }}" :active="request()->routeIs('my-saves')">
                    {{ __('My Saves') }}
                </x-responsive-nav-link>
                @endauth

                <x-responsive-nav-link href="{{ route('about') }}" 
                    :active="request()->routeIs('about')"
                    >
                    {{ __('About') }}
                </x-responsive-nav-link>

                @auth
                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif
                @endauth

                @auth
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <a href="{{ route('logout') }}" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-lg text-cod-gray-600 dark:text-cod-gray-400 hover:text-cod-gray-800 dark:hover:text-cod-gray-200 hover:bg-cod-gray-50 dark:hover:bg-rose-900/50 hover:border-cod-gray-300 dark:hover:border-rose-600 focus:outline-none focus:text-cod-gray-800 dark:focus:text-cod-gray-200 focus:bg-cod-gray-50 dark:focus:bg-cod-gray-700 focus:border-cod-gray-300 dark:focus:border-cod-gray-600 smooth-300"
                        @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </a>
                </form>

                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-cod-gray-200 dark:border-cod-gray-600"></div>

                    <div class="block px-4 py-2 text-cod-gray-400">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    <!-- Team Switcher -->
                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="border-t border-cod-gray-200 dark:border-cod-gray-600"></div>

                        <div class="block px-4 py-2 text-cod-gray-400">
                            {{ __('Switch Teams') }}
                        </div>

                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif
                @endif
                @endauth
            </div>
        </div>
    </div>
</nav>

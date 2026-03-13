<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-2xl text-green-600 dark:text-green-400">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ secure_url(route('login', [], false)) }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ml-2 text-2xl text-cod-gray-600 dark:text-cod-gray-400">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a wire:navigate class="underline text-2xl text-cod-gray-600 dark:text-cod-gray-400 hover:text-cod-gray-900 dark:hover:text-cod-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 dark:focus:ring-offset-cod-gray-800" href="{{ secure_url(route('password.request', [], false)) }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ml-4">
                    {{ __('Log in') }}
                </x-button>
            </div>

            <div class="flex justify-center">
                <a href="{{ route('login.google') }}" class="group flex justify-center items-center gap-x-4 border-[1.5px] bg-gray-300 border-black dark:border-white hover:bg-gray-100/70 rounded-full mt-8 mb-4 px-5 py-4 w-fit cursor-pointer transition-all duration-300 ease-in-out">
                    <div class="shadow-none cursor-pointer flex justify-center text-black dark:text-white hover:text-gray-600 dark:hover:text-gray-200 transition-all duration-300 ease-in-out text-2xl xl:text-3xl group-hover:text-gray-600 dark:group-hover:text-gray-200">
                        {{-- <i class="google icon"></i> --}}
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2d/Google-favicon-2015.png" alt="Google" class="w-8 h-8">
                    </div>
                    <div class="text-2xl group-hover:text-black  dark:group-hover:text-black transition-all duration-300 ease-in-out text-gray-800 dark:text-gray-800">
                        Sign in with Google
                    </div>
                    
                </a>
            </div>

            <a a wire:navigate href="{{ secure_url(route('register', [], false)) }}">
                <div class=" w-full pt-8 text-xl text-center text-rose-600 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-100">
                    New? Sign up for free
                </div>
            </a>
        </form>
    </x-authentication-card>
</x-guest-layout>

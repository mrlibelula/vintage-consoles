<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @stack('meta')
        
        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0;
                overflow: hidden;
                background: #000000;
            }
        </style>

        @stack('styles')

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">

        <script>
            window.VintagePlayerFetch = (input, init = {}) => window.fetch(input, init);
        </script>

        @vite(['resources/js/player.js'])

        @livewireStyles
    </head>
    <body>
        
        {{ $slot }}

        @stack('scripts')
        
        @livewireScripts

    </body>
</html>

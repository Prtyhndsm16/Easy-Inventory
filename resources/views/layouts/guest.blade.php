<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Easy Inventory & Sales Manager') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-[radial-gradient(circle_at_top_left,#ecfdf5_0,#f9fafb_36%,#f3f4f6_100%)] px-4 py-8">
            <div>
                <a href="/" class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm">
                        <x-application-logo class="h-8 w-8" />
                    </span>
                    <span>
                        <span class="block text-base font-semibold text-gray-950">Easy Inventory</span>
                        <span class="block text-sm text-gray-500">Manager</span>
                    </span>
                </a>
            </div>

            <div class="mt-8 w-full overflow-hidden rounded-lg border border-gray-200 bg-white px-4 py-6 shadow-sm sm:max-w-md sm:px-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

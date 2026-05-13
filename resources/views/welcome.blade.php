<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Easy Inventory Manager') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900">
        @if (Route::has('login'))
            <header class="p-6">
                <nav class="flex justify-end gap-4 text-sm">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-medium text-gray-700 hover:text-gray-900">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-gray-700 hover:text-gray-900">
                            Log in
                        </a>
                    @endauth
                </nav>
            </header>
        @endif

        <main class="flex min-h-[70vh] items-center justify-center px-6">
            <h1 class="text-3xl font-semibold">
                Welcome to {{ config('app.name', 'Easy Inventory Manager') }}
            </h1>
        </main>
    </body>
</html>

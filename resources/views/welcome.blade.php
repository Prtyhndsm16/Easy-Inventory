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
    <body class="min-h-screen bg-gray-950 text-white antialiased">
        @if (Route::has('login'))
            <header class="relative z-20">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-sm">
                            <x-application-logo class="h-7 w-7" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">Easy Inventory</span>
                            <span class="block text-xs text-emerald-100">Manager</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-3 text-sm">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2.5 font-semibold text-gray-950 transition hover:bg-emerald-50">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2.5 font-semibold text-gray-950 transition hover:bg-emerald-50">
                                Log in
                            </a>
                        @endauth
                    </div>
                </nav>
            </header>
        @endif

        <main>
            <section class="relative isolate -mt-[84px] min-h-[82vh] overflow-hidden px-6 pb-12 pt-32 lg:px-8">
                <div class="absolute inset-0 -z-20 bg-gray-950"></div>

                <div class="absolute inset-0 -z-10 overflow-hidden opacity-85">
                    <div class="absolute left-1/2 top-20 h-[620px] w-[920px] -translate-x-1/2 rounded-full bg-emerald-700/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-gray-950 to-transparent"></div>

                    <div class="absolute right-[-90px] top-28 hidden w-[680px] rotate-[-3deg] lg:block">
                        <div class="grid grid-cols-4 gap-4">
                            @foreach (range(1, 12) as $box)
                                <div class="h-28 rounded-lg border border-white/10 bg-white/10 p-4 shadow-2xl backdrop-blur">
                                    <div class="h-3 w-16 rounded-full bg-emerald-300/80"></div>
                                    <div class="mt-6 h-2 rounded-full bg-white/25"></div>
                                    <div class="mt-3 h-2 w-2/3 rounded-full bg-white/20"></div>
                                    <div class="mt-5 flex gap-2">
                                        <div class="h-5 w-5 rounded bg-amber-300/80"></div>
                                        <div class="h-5 w-5 rounded bg-blue-300/80"></div>
                                        <div class="h-5 w-5 rounded bg-rose-300/80"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="absolute bottom-12 left-6 right-6 grid gap-3 sm:grid-cols-3 lg:left-auto lg:right-16 lg:w-[620px]">
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <p class="text-xs font-semibold uppercase text-emerald-100">Products</p>
                            <p class="mt-2 text-3xl font-semibold">240</p>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <p class="text-xs font-semibold uppercase text-blue-100">Stock Units</p>
                            <p class="mt-2 text-3xl font-semibold">12.4k</p>
                        </div>
                        <div class="rounded-lg border border-red-300/20 bg-red-500/15 p-4 backdrop-blur">
                            <p class="text-xs font-semibold uppercase text-red-100">Alerts</p>
                            <p class="mt-2 text-3xl font-semibold">8</p>
                        </div>
                    </div>
                </div>

                <div class="mx-auto flex min-h-[58vh] max-w-7xl items-center">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold uppercase text-emerald-200">Inventory control for small teams</p>
                        <h1 class="mt-5 text-4xl font-semibold leading-tight text-white sm:text-6xl">
                            {{ config('app.name', 'Easy Inventory Manager') }}
                        </h1>
                        <p class="mt-6 max-w-2xl text-base leading-7 text-gray-200 sm:text-lg">
                            Track products, monitor low-stock items, manage staff access, and keep daily inventory work organized in one focused system.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-400">
                                    Open Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-400">
                                    Log in to System
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white px-6 py-12 text-gray-900 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-5 md:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 p-5">
                        <p class="text-sm font-semibold text-emerald-700">Product Management</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">Create, edit, search, and filter product records with stock, supplier, barcode, and pricing details.</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-5">
                        <p class="text-sm font-semibold text-blue-700">Staff Workflow</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">Give staff a clear read-only inventory view while keeping admin-only actions protected.</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-5">
                        <p class="text-sm font-semibold text-red-700">Low Stock Alerts</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">Spot items near the restock threshold directly from the dashboard and filtered product lists.</p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>

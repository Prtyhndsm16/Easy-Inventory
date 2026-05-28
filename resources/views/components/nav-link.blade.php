@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold leading-5 text-emerald-800 ring-1 ring-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium leading-5 text-gray-600 hover:bg-gray-100 hover:text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

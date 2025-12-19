@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-block px-1 pb-0.5 border-b-2 border-red-600 text-sm text-red-600 font-bold leading-tight focus:outline-none transition duration-150 ease-in-out'
            : 'inline-block px-1 pb-0.5 border-b-2 border-transparent text-sm font-medium leading-tight text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

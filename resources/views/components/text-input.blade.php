@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full bg-gray-800 border-gray-700 text-gray-200 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm']) !!}>

@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'border-gray-300 dark:border-primary-700 ' . 
    'dark:bg-primary-850 ' . 
    'dark:text-gray-100 ' .  
    'focus:border-primary-500 dark:focus:border-primary-400 ' . 
    'focus:ring-primary-500 dark:focus:ring-primary-400 ' .     
    'rounded-md shadow-sm'
]) !!}>
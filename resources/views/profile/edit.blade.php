<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- Menggunakan space-y-6 untuk jarak antar card --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"> 
            
            {{-- Card 1: Update Profile Info --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                {{-- MENGGUNAKAN max-w-3xl UNTUK MEMPERLEBAR FORM --}}
                <div class="max-w-3xl"> 
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Card 2: Update Password --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                 {{-- MENGGUNAKAN max-w-3xl UNTUK MEMPERLEBAR FORM --}}
                <div class="max-w-3xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Card 3: Delete Account --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                 {{-- MENGGUNAKAN max-w-3xl UNTUK MEMPERLEBAR FORM --}}
                <div class="max-w-3xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
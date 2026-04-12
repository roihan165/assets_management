@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <!-- <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" /> -->
             <!-- <img src="{{ asset('images/logo-login-header.png') }}" alt="Logo" class="h-8 w-auto"> -->
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="" {{ $attributes }}>
        <x-slot name="logo">
            <!-- <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" /> -->
            <img src="{{ asset('images/logo-login-header.png') }}" alt="Logo" class="h-6 w-auto">
        </x-slot>
    </flux:brand>
@endif

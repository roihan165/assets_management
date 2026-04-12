<?php

use function Livewire\Volt\{state};

state([
    'open' => false
]);

$toggle = fn () => $this->open = !$this->open;

?>

<div class="absolute top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between text-white">

        <!-- Logo -->
        <div class="flex items-center gap-2">
            <img 
                src="{{ asset('images/logo.png') }}" 
                alt="Sinergi"
                class="h-8 w-auto"
            >
            <span class="text-lg font-bold tracking-wide">
                SINERGI
            </span>
        </div>

        <!-- Login -->
        <a href="{{ route('login') }}"
           class="border border-white px-4 py-1.5 rounded-full text-sm hover:bg-white hover:text-black transition">
            Login Asset Management
        </a>

    </div>
</div>
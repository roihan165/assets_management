<?php

use function Livewire\Volt\{state};
use function Livewire\Volt\{on};

state([
    'slides' => [
        [
            'title' => 'EMPOWERING IN BUSINESS INNOVATION',
            'image' => 'images/bg.jpg'
        ],
        [
            'title' => 'DIGITAL TRANSFORMATION SOLUTIONS',
            'image' => 'images/bg2.jpg'
        ],
        [
            'title' => 'BUILDING FUTURE TECHNOLOGY',
            'image' => 'images/bg3.jpg'
        ],
    ],
    'active' => 0
]);

$next = fn () => $this->active = ($this->active + 1) % count($this->slides);
$setSlide = fn ($i) => $this->active = $i;
?>

<div wire:poll.5s="next" class="relative h-screen w-full overflow-hidden text-white">

    <!-- Background -->
    <div class="absolute inset-0 transition-all duration-700">
        <img 
            src="{{ asset($slides[$active]['image']) }}"
            class="w-full h-full object-cover"
        >
    </div>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-blue-900/70"></div>

    <!-- HERO TEXT -->
    <div class="relative z-10 flex flex-col justify-center items-center h-full text-center px-4">

        <h1 class="text-4xl md:text-6xl font-bold leading-tight transition-all duration-500">
            {{ $slides[$active]['title'] }}
        </h1>

        <!-- BUTTON -->
        <button 
            wire:click="next"
            class="mt-6 px-6 py-2 border border-white rounded hover:bg-white hover:text-black transition"
        >
            Next Slide
        </button>

    </div>

    <!-- DOT INDICATOR -->
    <div class="absolute right-6 top-1/2 -translate-y-1/2 flex flex-col gap-3 z-20">
        @foreach($slides as $i => $slide)
            <span 
                wire:click="$set('active', {{ $i }})"
                class="w-3 h-3 rounded-full cursor-pointer 
                {{ $active === $i ? 'bg-white' : 'border border-white' }}">
            </span>
        @endforeach
    </div>

</div>
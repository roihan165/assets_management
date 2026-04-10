<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
            
                {{-- DASHBOARD (SEMUA) --}}
                <flux:navbar.item 
                    icon="layout-grid" 
                    :href="route('dashboard')" 
                    :current="request()->routeIs('dashboard')" 
                    wire:navigate
                >
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            
                {{-- STAFF --}}
                @role('staff')
                    <flux:navbar.item 
                        icon="plus-circle" 
                        href="/loans/create" 
                        :current="request()->is('loans/create')" 
                        wire:navigate
                    >
                        Pinjam Barang
                    </flux:navbar.item>
                    
                    <flux:navbar.item 
                        icon="arrow-uturn-left" 
                        href="/loans/return" 
                        :current="request()->is('loans/return')" 
                        wire:navigate
                    >   
                        Pengembalian
                    </flux:navbar.item>
                    @endrole
                    
                    {{-- ATASAN --}}
                    @role('atasan')
                    
                    <flux:navbar.item 
                    icon="check-circle" 
                    href="/loans/approve" 
                    :current="request()->is('loans/approve')" 
                    wire:navigate
                    >
                    Approval
                    </flux:navbar.item>

                    <flux:navbar.item 
                        icon="arrow-uturn-left" 
                        href="/loans/return-approval" 
                        :current="request()->is('loans/return-approval')" 
                        wire:navigate
                    >
                        Pengembalian
                    </flux:navbar.item>
                    <flux:navbar.item 
                        icon="archive-box" 
                        href="/items/manage" 
                        :current="request()->is('items/manage')" 
                        wire:navigate
                    >
                        Kelola Barang
                    </flux:navbar.item>
                @endrole
            
                {{-- ADMIN + ATASAN --}}
                @role('admin|atasan')
                    <flux:navbar.item 
                        icon="users" 
                        href="/users/create" 
                        :current="request()->is('users/create')" 
                        wire:navigate
                    >
                        Tambah User
                    </flux:navbar.item>
                @endrole
            
                {{-- ADMIN --}}
                @role('admin')
                    <flux:navbar.item 
                        icon="document-arrow-down" 
                        href="{{ url('/reports/loans/pdf?status=returned') }}"
                        target="_blank"
                    >
                        Export PDF
                    </flux:navbar.item>
                @endrole
            
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
                <flux:tooltip :content="__('Repository')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </flux:tooltip>
                <flux:tooltip :content="__('Documentation')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        :label="__('Documentation')"
                    />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>

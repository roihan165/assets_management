<x-layouts::auth :title="__('Log in')">

    <!-- BACKGROUND -->
    <div class="min-h-screen flex items-center justify-center relative">

        <!-- OVERLAY -->
        <!-- <div class="absolute inset-0 bg-blue-900/70"></div> -->

        <!-- CONTENT -->
        <div class="relative z-10 w-full max-w-md bg-white/90 backdrop-blur-md p-6 rounded-xl shadow-xl">

            <!-- LOGO -->
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/logo-login-header.png') }}" alt="Logo" class="h-12">
            </div>

            <div class="flex flex-col gap-6">
                <x-auth-header 
                    :title="__('Welcome To Assets Management')" 
                    :description="__('Enter your email and password below to log in')" 
                />

                <x-auth-session-status class="text-center" :status="session('status')" />

                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <flux:input
                        name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Password')"
                            type="password"
                            required
                            autocomplete="current-password"
                            :placeholder="__('Password')"
                            viewable
                        />

                        @if (Route::has('password.request'))
                            <flux:link class="absolute top-0 text-sm end-0" 
                                :href="route('password.request')" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </flux:link>
                        @endif
                    </div>

                    <flux:checkbox name="remember" 
                        :label="__('Remember me')" 
                        :checked="old('remember')" 
                    />

                    <flux:button variant="primary" type="submit" class="w-full">
                        {{ __('Log in') }}
                    </flux:button>
                </form>

                @if (Route::has('register'))
                    <div class="text-sm text-center text-zinc-600">
                        <span>{{ __('Don\'t have an account?') }}</span>
                        <flux:link :href="route('register')" wire:navigate>
                            {{ __('Sign up') }}
                        </flux:link>
                    </div>
                @endif
            </div>

        </div>
    </div>

</x-layouts::auth>
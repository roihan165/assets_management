<x-layouts::auth :title="__('Confirm password')">
    <div class="min-h-screen flex items-center justify-center relative">
        <div class="relative z-10 w-full max-w-md bg-white/90 backdrop-blur-md p-6 rounded-xl shadow-xl">
            <div class="flex flex-col gap-6">
                <x-auth-header
                    :title="__('Confirm password')"
                    :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
                />
        
                <x-auth-session-status class="text-center" :status="session('status')" />
        
                <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
                    @csrf
        
                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Password')"
                        viewable
                    />
        
                    <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                        {{ __('Confirm') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::auth>

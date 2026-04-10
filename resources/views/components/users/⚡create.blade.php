<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public $name = '';
    public $email = '';
    public $role = '';

    public function submit()
    {
        // 🔒 proteksi role
        if (!auth()->user()->hasAnyRole(['admin', 'atasan'])) {
            abort(403);
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,atasan,staff',
        ]);

        try {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make('password123'), // default password
            ]);

            $user->assignRole($this->role);

            $this->reset(['name', 'email', 'role']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log('Membuat user baru');

            session()->flash('success', 'User berhasil dibuat');

        } catch (\Exception $e) {
            $this->addError('service', $e->getMessage());
        }
    }
};
?>

<!-- <div>
    {{-- Simplicity is the ultimate sophistication. - Leonardo da Vinci --}}
</div> -->

<div class="max-w-xl mx-auto space-y-6">

    <flux:heading size="lg">
        Tambah User
    </flux:heading>

    {{-- SUCCESS --}}
    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR --}}
    @error('service')
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit.prevent="submit" class="space-y-4">

        {{-- NAME --}}
        <div>
            <label class="block text-sm font-medium mb-1">Nama</label>
            <input type="text"
                   wire:model="name"
                   class="w-full border rounded px-3 py-2">
            @error('name') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
        </div>

        {{-- EMAIL --}}
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email"
                   wire:model="email"
                   class="w-full border rounded px-3 py-2">
            @error('email') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
        </div>

        {{-- ROLE --}}
        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select wire:model="role"
                    class="w-full border rounded px-3 py-2">
                <option value="">Pilih role</option>
                <option value="admin">Admin</option>
                <option value="atasan">Atasan</option>
                <option value="staff">Staff</option>
            </select>
            @error('role') <div class="text-red-500 text-sm">{{ $message }}</div> @enderror
        </div>

        {{-- BUTTON --}}
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">
                Simpan
            </flux:button>
        </div>

    </form>
</div>
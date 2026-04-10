<?php

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public $roles = [];
    public $selectedRoles = [];

    public function mount()
    {
        // ambil semua role
        $this->roles = Role::pluck('name')->toArray();
    }

    public function getUsersProperty()
    {
        return User::with('roles')->get();
    }

    public function updateRole($userId)
    {
        $user = User::findOrFail($userId);

        // 🔒 proteksi
        if (!auth()->user()->hasAnyRole(['admin', 'atasan'])) {
            abort(403);
        }

        $role = $this->selectedRoles[$userId] ?? null;

        if (!$role) return;

        $user->syncRoles([$role]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Mengubah role user');
            
        session()->flash('success', 'Role berhasil diperbarui');

    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        // 🔒 proteksi admin only
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // jangan hapus diri sendiri
        if ($user->id === auth()->id()) {
            return;
        }

        $user->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Menghapus user');

        session()->flash('success', 'User dihapus');
    }
};
?>

<!-- <div>
    {{-- I begin to speak only when I am certain what I will say is not better left unsaid. - Cato the Younger --}}
</div> -->

<div class="max-w-5xl mx-auto space-y-6">

    <flux:heading size="lg">
        Daftar User
    </flux:heading>

    {{-- SUCCESS --}}
    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <flux:card class="p-4">

        <table class="w-full text-sm">

            <thead class="text-left border-b">
                <tr>
                    <th class="py-2">Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($this->users as $user)
                    <tr class="border-b">

                        <td class="py-2">
                            {{ $user->name }}
                        </td>

                        <td>
                            {{ $user->email }}
                        </td>

                        <td>
                            <select 
                                wire:model="selectedRoles.{{ $user->id }}"
                                class="border rounded px-2 py-1"
                            >
                                <option value="">-- pilih --</option>

                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="text-right space-x-2">

                            {{-- UPDATE ROLE --}}
                            <flux:button 
                                size="sm"
                                wire:click="updateRole({{ $user->id }})"
                            >
                                Update
                            </flux:button>

                            {{-- DELETE (ADMIN ONLY) --}}
                            @role('admin')
                                <flux:button 
                                    size="sm"
                                    variant="danger"
                                    wire:click="deleteUser({{ $user->id }})"
                                >
                                    Hapus
                                </flux:button>
                            @endrole

                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </flux:card>

</div>
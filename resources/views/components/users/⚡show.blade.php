<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\User;

new class extends Component
{
    public $search = '';
 
    #[Computed]
    public function users()
    {
        return User::where('name',$this->search)->get();
    }
};
?>

<div>
    {{-- The only way to do great work is to love what you do. - Steve Jobs --}}
    <input type="text" wire:model.live="search">
 
    <ul>
        @foreach ($this->users as $user)
            <li wire:key="{{ $user->id }}">{{ $user->name }}</li>
        @endforeach
    </ul>
</div>
<?php

use Livewire\Component;

new class extends Component
{
    public $dummy_data;

    public string $title = 'lorem ipsum';
 
    public string $content = '';
    
    public function mount($dummy)
    {
        $this->dummy_data = $dummy;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        dd($this->title, $this->content);
    }
};
?>

<!-- <div>
    {{-- Simplicity is the essence of happiness. - Cedric Bledsoe --}}
</div> -->

<flux:card>
    <flux:heading>{{ $dummy_data }}</flux:heading>
    <form wire:submit="save">
        <label>
            Title
            <input type="text" wire:model="title" value="{{ $title }}">
            @error('title') <span style="color: red;">{{ $message }}</span> @enderror
        </label>
     
        <label>
            Content
            <textarea wire:model="content" rows="5"></textarea>
            @error('content') <span style="color: red;">{{ $message }}</span> @enderror
        </label>
     
        <button type="submit">Save Post</button>
    </form>
</flux:card>
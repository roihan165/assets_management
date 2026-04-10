<?php

use Livewire\Component;

new class extends Component
{
    // 🔹 item
    public $name;

    // 🔹 mode
    public $mode = 'auto';

    // 🔹 auto
    public $total_units = 1;

    // 🔹 manual
    public $code;
    public $condition = 'good';
    public $note;

    public function store()
    {
        // 🔥 VALIDASI DASAR
        if (!$this->name) {
            throw new \Exception('Nama barang wajib diisi');
        }

        if ($this->mode === 'auto' && $this->total_units < 1) {
            throw new \Exception('Minimal 1 unit');
        }

        if ($this->mode === 'manual' && !$this->code) {
            throw new \Exception('Kode unit wajib diisi');
        }

        app(\App\Services\ItemService::class)->create([
            'name' => $this->name,
            'mode' => $this->mode,
            'total_units' => $this->total_units,
            'code' => $this->code,
            'condition' => $this->condition,
            'note' => $this->note,
        ]);

        session()->flash('success', 'Barang berhasil ditambahkan');

        // 🔥 RESET FORM
        $this->reset([
            'name',
            'total_units',
            'code',
            'condition',
            'note',
        ]);
    }
};
?>

<!-- <div>
    {{-- Simplicity is the consequence of refined emotions. - Jean D'Alembert --}}
</div> -->

<div class="max-w-xl mx-auto space-y-4">

    <flux:heading size="lg">
        Tambah Barang
    </flux:heading>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-3 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- NAME --}}
    <input type="text"
           wire:model="name"
           placeholder="Nama barang"
           class="border rounded px-3 py-2 w-full">

    {{-- MODE --}}
    <div class="flex gap-2">

        <flux:button wire:click="$set('mode','auto')"
                     :variant="$mode === 'auto' ? 'primary' : 'ghost'">
            Auto
        </flux:button>

        <flux:button wire:click="$set('mode','manual')"
                     :variant="$mode === 'manual' ? 'primary' : 'ghost'">
            Manual
        </flux:button>

    </div>

    {{-- AUTO --}}
    @if($mode === 'auto')

        <input type="number"
               wire:model="total_units"
               min="1"
               class="border rounded px-3 py-2 w-full">

    @endif

    {{-- MANUAL --}}
    @if($mode === 'manual')

        <input type="text"
               wire:model="code"
               placeholder="Kode unit"
               class="border rounded px-3 py-2 w-full">

        <select wire:model="condition"
                class="border rounded px-3 py-2 w-full">
            <option value="good">Baik</option>
            <option value="minor_damage">Minor</option>
            <option value="major_damage">Major</option>
            <option value="lost">Hilang</option>
        </select>

        <input type="text"
               wire:model="note"
               placeholder="Catatan"
               class="border rounded px-3 py-2 w-full">

    @endif

    <flux:button wire:click="store"
             wire:loading.attr="disabled">

        <span wire:loading.remove>Simpan</span>
        <span wire:loading>Menyimpan...</span>

    </flux:button>

</div>
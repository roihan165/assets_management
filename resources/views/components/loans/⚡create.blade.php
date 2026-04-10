<?php

use Livewire\Component;
use App\Models\ItemUnit;

new class extends Component
{
    public $units = [];
    public $selectedUnits = [];
    public $search = '';
    public $showPreview = false;
    public $previewUnits = [];
    public $notes = [];

    public function mount()
    {
        $this->units = ItemUnit::with('item')
            ->where('status', 'available')
            ->whereNotIn('condition_status', ['major_damage', 'lost'])
            ->get();
    }

    public function getGroupedUnitsProperty()
    {
        return \App\Models\ItemUnit::with('item')
            ->where('status', 'available')
            ->whereNotIn('condition_status', ['major_damage', 'lost'])

            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('code', 'like', '%' . $this->search . '%')
                       ->orWhereHas('item', function ($q3) {
                           $q3->where('name', 'like', '%' . $this->search . '%');
                       });
                });
            })

            ->get()
            ->groupBy(fn ($unit) => $unit->item->name);
    }

    public function submit()
    {
        $this->validate([
            'selectedUnits' => 'required|array|min:1'
        ]);

        $this->previewUnits = \App\Models\ItemUnit::with('item')
            ->whereIn('id', $this->selectedUnits)
            ->get();

        $this->showPreview = true;
    }

    public function confirm()
    {
        app(\App\Services\LoanService::class)
            ->createLoan(auth()->id(), $this->selectedUnits, $this->notes);

        session()->flash('success', 'Peminjaman diajukan');

        return redirect()->route('dashboard');
    }

};
?>

<!-- <div>
    {{-- Happiness is not something readymade. It comes from your own actions. - Dalai Lama --}}
</div> -->

@if ($showPreview)

    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

        <div class="bg-white rounded-lg p-6 w-full max-w-md">

            <h2 class="text-lg font-semibold mb-4">
                Konfirmasi Peminjaman
            </h2>

            <div class="space-y-2 max-h-60 overflow-auto">

                @foreach ($previewUnits as $unit)

                    <div class="border rounded p-2 text-sm">

                        <div class="font-medium">
                            {{ $unit->item->name }}
                        </div>

                        <div class="text-gray-500">
                            Code: {{ $unit->code }}
                        </div>

                        <div>
                            @if ($unit->condition_status === 'good')
                                <span class="text-green-600">Baik</span>
                            @elseif ($unit->condition_status === 'minor_damage')
                                <span class="text-yellow-600">Minor</span>
                            @endif
                        </div>

                        <div class="text-xs text-gray-500 mt-1">
                            Catatan:
                            <span class="italic">
                                {{ $unit->condition_notes ?: '-' }}
                            </span>
                        </div>

                    </div>

                @endforeach

            </div>

            <div class="mt-4 flex justify-end gap-2">

                <flux:button variant="ghost" wire:click="$set('showPreview', false)">
                    Batal
                </flux:button>

                <flux:button wire:click="confirm">
                    Konfirmasi
                </flux:button>

            </div>

        </div>

    </div>

@endif

<div class="space-y-6">
    <div class="mb-4">

        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Cari barang atau kode unit..."
               class="w-full border rounded px-3 py-2">

    </div>
    <flux:heading size="lg">
        Pinjam Barang
    </flux:heading>
    
    <form wire:submit.prevent="submit">
        
        @if ($this->groupedUnits->isEmpty())
            <div class="text-gray-500 text-sm">
                Tidak ditemukan
            </div>
        @endif

        @foreach ($this->groupedUnits as $itemName => $units)
        
            <div class="border rounded p-4">

                {{-- ITEM HEADER --}}
                <div class="font-semibold mb-3">
                    {{ $itemName }}
                </div>

                {{-- UNIT LIST --}}
                <div class="grid grid-cols-2 gap-3">

                    @foreach ($units as $unit)

                        <label class="flex items-start gap-2 p-2 border rounded cursor-pointer hover:bg-gray-50">

                            <input type="checkbox"
                                   value="{{ $unit->id }}"
                                   wire:model="selectedUnits">

                            <div class="text-sm">

                                <div>
                                    {{ $unit->code }}
                                </div>

                                {{-- CONDITION --}}
                                <div class="text-xs mt-1">

                                    @if ($unit->condition_status === 'good')
                                        <span class="text-green-600">Baik</span>
                                    @elseif ($unit->condition_status === 'minor_damage')
                                        <span class="text-yellow-600">Minor</span>
                                    @endif

                                </div>

                                <div class="text-xs text-gray-500 mt-1">
                                    Catatan:
                                    <span class="italic">
                                        {{ $unit->condition_notes ?: '-' }}
                                    </span>
                                </div>

                            </div>

                        </label>

                    @endforeach

                </div>

            </div>

        @endforeach

        <div class="mt-4">
            <flux:button type="submit">
                Ajukan Peminjaman
            </flux:button>
        </div>

    </form>

</div>

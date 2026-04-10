<?php

use Livewire\Component;
use App\Models\ItemUnit;

new class extends Component
{
    public $search = '';

    public function getUnitsProperty()
    {
        return ItemUnit::with('item')
            ->when($this->search, function ($q) {
                $q->whereHas('item', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->limit(100)
            ->get();
    }

    public function updateUnit($id, $field, $value)
    {
        $unit = ItemUnit::findOrFail($id);

        // 🔒 proteksi
        if (!auth()->user()->hasRole('atasan')) {
            abort(403);
        }

        $unit->update([
            $field => $value
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($unit)
            ->log("Update {$field} item unit");

        session()->flash('success', 'Data diperbarui');
    }
};
?>

<!-- <div>
    {{-- It is never too late to be what you might have been. - George Eliot --}}
</div> -->

<div class="max-w-6xl mx-auto space-y-6">

    <flux:heading size="lg">
        Kelola Kondisi Barang
    </flux:heading>

    {{-- SEARCH --}}
    <input type="text"
           wire:model.live="search"
           placeholder="Cari barang..."
           class="border rounded px-3 py-2 w-1/3">

    <flux:button wire:navigate href="/items/create" icon="plus" class="mb-2 bg-blue-600 hover:bg-blue-700 text-white">
                    Tambah Barang
                </flux:button>

    {{-- SUCCESS --}}
    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <flux:card class="p-4">

        <table class="w-full text-sm">

            <thead class="border-b text-left">
                <tr>
                    <th class="py-2">Barang</th>
                    <th>Kode</th>
                    <th>Status</th>
                    <th>Kondisi</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($this->units as $unit)
                    <tr class="border-b">

                        <td class="py-2">
                            {{ $unit->item->name }}
                        </td>

                        <td>{{ $unit->code }}</td>

                        {{-- STATUS --}}
                        <td>
                            <select 
                                wire:change="updateUnit({{ $unit->id }}, 'status', $event.target.value)"
                                class="border rounded px-2 py-1 text-sm"
                            >
                                <option value="available" @selected($unit->status=='available')>Available</option>
                                <option value="maintenance" @selected($unit->status=='maintenance')>Maintenance</option>
                                <option value="retired" @selected($unit->status=='lost')>Lost</option>
                            </select>
                        </td>

                        {{-- KONDISI --}}
                        <td>
                            <select 
                                wire:change="updateUnit({{ $unit->id }}, 'condition_status', $event.target.value)"
                                class="border rounded px-2 py-1 text-sm"
                            >
                                <option value="good" @selected($unit->condition_status=='good')>Baik</option>
                                <option value="minor_damage" @selected($unit->condition_status=='minor_damage')>Minor</option>
                                <option value="major_damage" @selected($unit->condition_status=='major_damage')>Major</option>
                                <option value="lost" @selected($unit->condition_status=='lost')>Hilang</option>
                            </select>
                        </td>

                        {{-- NOTES --}}
                        <td>
                            <input type="text"
                                   value="{{ $unit->condition_notes }}"
                                   wire:blur="updateUnit({{ $unit->id }}, 'condition_notes', $event.target.value)"
                                   class="border rounded px-2 py-1 text-sm w-full">
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </flux:card>

</div>
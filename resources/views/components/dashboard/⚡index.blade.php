<?php

use Livewire\Component;
use App\Models\ItemUnit;
use App\Models\Item;

new class extends Component
{
    public $search = '';
    public $filterStatus = '';
    
    public function getStatsProperty()
    {
        return [
            'total' => ItemUnit::count(),
            'borrowed' => ItemUnit::where('status', 'borrowed')->count(),
            'available' => ItemUnit::where('status', 'available')->count(),
            'maintenance' => ItemUnit::where('status', 'maintenance')->count(),
        ];
    }

    public function getItemsProperty()
    {
        return \App\Models\Item::query()

            ->withCount([

                'units as total_units',

                'units as available_units' => fn ($q) => $q->where('status', 'available'),

                'units as borrowed_units' => fn ($q) => $q->where('status', 'borrowed'),

                'units as maintenance_units' => fn ($q) => $q->where('status', 'maintenance'),

                'units as retired_units' => fn ($q) => $q->where('status', 'retired'),

                'units as lost_units' => fn ($q) => $q->where('status', 'lost'),

            
                // 🔥 kondisi
                'units as good_units' => function ($q) {
                    $q->where('condition_status', 'good');
                },
            
                'units as minor_units' => function ($q) {
                    $q->where('condition_status', 'minor_damage');
                },
            
                'units as major_units' => function ($q) {
                    $q->where('condition_status', 'major_damage');
                },
            
                'units as lost_units' => function ($q) {
                    $q->where('condition_status', 'lost');
                },
            ])

            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })

            ->when($this->filterStatus === 'available', function ($q) {
                $q->whereHas('units', function ($q2) {
                    $q2->where('status', 'available');
                });
            })

            ->when($this->filterStatus === 'borrowed', function ($q) {
                $q->whereHas('units', function ($q2) {
                    $q2->where('status', 'borrowed');
                });
            })

            ->get();
    }
};
?>

<!-- <div>
    {{-- Life is available only in the present moment. - Thich Nhat Hanh --}}
</div> -->


    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- GRID STATS --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">

            {{-- TOTAL --}}
            <flux:card class="p-4">
                <flux:heading size="sm">Total Barang</flux:heading>
                <div class="text-2xl font-bold mt-2">
                    {{ $this->stats['total'] }}
                </div>
            </flux:card>

            {{-- DIPINJAM --}}
            <flux:card class="p-4">
                <flux:heading size="sm">Sedang Dipinjam</flux:heading>
                <div class="text-2xl font-bold text-yellow-600 mt-2">
                    {{ $this->stats['borrowed'] }}
                </div>
            </flux:card>

            {{-- TERSEDIA --}}
            <flux:card class="p-4">
                <flux:heading size="sm">Siap Dipinjam</flux:heading>
                <div class="text-2xl font-bold text-green-600 mt-2">
                    {{ $this->stats['available'] }}
                </div>
            </flux:card>

            {{-- MAINTENANCE --}}
            <flux:card class="p-4">
                <flux:heading size="sm">Maintenance</flux:heading>
                <div class="text-2xl font-bold text-red-600 mt-2">
                    {{ $this->stats['maintenance'] }}
                </div>
            </flux:card>

        </div>

        {{-- OPTIONAL: AREA TAMBAHAN --}}
        <div class="rounded-xl border p-4">
            <div class="flex gap-4 items-center mb-4">
                {{-- SEARCH --}}
                <input 
                    type="text"
                    wire:model.live="search"
                    placeholder="Cari barang..."
                    class="border rounded px-3 py-2 w-1/3"
                >

                {{-- FILTER --}}
                <select 
                    wire:model.live="filterStatus"
                    class="border rounded px-3 py-2"
                >
                    <option value="">Semua</option>
                    <option value="available">Tersedia</option>
                    <option value="borrowed">Sedang Dipinjam</option>
                </select>

            </div>
            <flux:card class="p-4">

                <flux:heading size="md">
                    Daftar Barang
                </flux:heading>

                <table class="w-full text-sm mt-4">

                    <thead class="border-b text-left">
                        <tr>
                            <th class="py-2">Nama Barang</th>
                            <th>Total</th>
                            <th>Tersedia</th>
                            <th>Dipinjam</th>
                            <th>Maintenance</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($this->items as $item)

                            @php
                                $total = $item->units->count();

                                $available = $item->units->where('status', 'available')->count();

                                $borrowed = $item->units->where('status', 'borrowed')->count();

                                $damaged = $item->units
                                    ->whereIn('condition_status', ['minor_damage', 'major_damage'])
                                    ->count();

                                $lost = $item->units
                                    ->where('condition_status', 'lost')
                                    ->count();

                                $good = $item->units
                                    ->where('condition_status', 'good')
                                    ->count();
                            @endphp

                            <tr class="border-b hover:bg-gray-50">

                                <td class="py-2">
                                    <div class="flex items-center gap-2">

                                        {{-- indicator kecil --}}
                                        @if ($item->lost_units > 0 || $item->major_units > 0)
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        @elseif ($item->minor_units > 0)
                                            <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                        @endif

                                        {{ $item->name }}
                                    </div>
                                </td>

                                <td>{{ $total }}</td>

                                @php
                                    $lowStock = $item->available_units <= 2;
                                @endphp
                                <td>
                                    <span class="px-2 py-1 rounded text-xs 
                                        {{ $lowStock ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">

                                        {{ $item->available_units }} tersedia
                                    </span>
                                </td>

                                <td>
                                    <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-700">
                                        {{ $item->borrowed_units }} dipinjam
                                    </span>
                                </td>

                                <td>
                                    <span class="px-2 py-1 rounded text-xs bg-orange-100 text-orange-700">
                                        {{ $item->maintenance_units }} Maintenance
                                    </span>
                                </td>

                                <td class="space-y-1 text-xs">

                                    @if ($item->good_units > 0)
                                        <span class="inline-block px-2 py-1 rounded bg-green-100 text-green-700">
                                            Baik: {{ $item->good_units }}
                                        </span>
                                    @endif

                                    @if ($item->minor_units > 0)
                                        <span class="inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-700">
                                            Minor: {{ $item->minor_units }}
                                        </span>
                                    @endif

                                    @if ($item->major_units > 0)
                                        <span class="inline-block px-2 py-1 rounded bg-orange-100 text-orange-700">
                                            Major: {{ $item->major_units }}
                                        </span>
                                    @endif

                                    @if ($item->lost_units > 0)
                                        <span class="inline-block px-2 py-1 rounded bg-red-100 text-red-700 font-semibold">
                                            Hilang: {{ $item->lost_units }}
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </flux:card>
        </div>
    </div>
<?php

use Livewire\Component;

new class extends Component
{
    public $conditions = [];
    public $notes = [];

    public function getLoansProperty()
    {
        return \App\Models\Loan::with(['user', 'details.itemUnit.item'])
            ->where('status', 'return_pending') // hanya yang menunggu approval
            ->latest()
            ->get();
    }
    
    public function approve($loanId)
    {
        app(\App\Services\LoanService::class)
            ->approveReturn($loanId, auth()->id(), $this->conditions, $this->notes);

        session()->flash('success', 'Return disetujui');
    }

    public function reject($loanId)
    {
        app(\App\Services\LoanService::class)
            ->rejectReturn($loanId, auth()->id());

        session()->flash('success', 'Return ditolak');
    }
};
?>

<!-- <div>
    {{-- People find pleasure in different ways. I find it in keeping my mind clear. - Marcus Aurelius --}}
</div> -->

<div class="max-w-4xl mx-auto space-y-6">

    <flux:heading size="lg">
        Pengembalian Barang
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

    @forelse ($this->loans as $loan)

        <x-loan.card :loan="$loan">

            @foreach ($loan->details as $detail)

                @php $unit = $detail->itemUnit; @endphp

                <div class="border rounded p-2 text-sm">

                    <div class="font-medium">
                        {{ $unit->item->name }}
                    </div>

                    <div class="text-gray-500">
                        Code: {{ $unit->code }}
                    </div>

                    <div class="mt-1">
                        @if ($unit->condition_status === 'good')
                            <span class="text-green-600">Baik</span>
                        @elseif ($unit->condition_status === 'minor_damage')
                            <span class="text-yellow-600">Minor</span>
                        @elseif ($unit->condition_status === 'major_damage')
                            <span class="text-orange-600">Major</span>
                        @elseif ($unit->condition_status === 'lost')
                            <span class="text-red-600">Hilang</span>
                        @endif
                    </div>

                    {{-- 🔥 PREVIEW --}}
                    @include('partials.return-preview', [
                        'detail' => $detail
                    ])
                    
                </div>
                    
            @endforeach

            <x-slot:actions>

                <flux:button wire:click="approve({{ $loan->id }})">
                    Approve
                </flux:button>

                <flux:button variant="danger"
                             wire:click="reject({{ $loan->id }})">
                    Reject
                </flux:button>

            </x-slot:actions>

        </x-loan.card>
    @empty
    <div class="border rounded-lg p-6 text-center text-gray-500">
                        
        <div class="text-lg font-semibold">
            📦 Loan tidak ditemukan
        </div>
                        
        <div class="text-sm mt-2">
            Belum ada peminjaman yang bisa ditampilkan
        </div>
                        
    </div>
    @endforelse

</div>
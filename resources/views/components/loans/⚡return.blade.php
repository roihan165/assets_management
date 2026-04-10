<?php

use Livewire\Component;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public $conditions = [];
    public $notes = [];
    public $details = [];

    public function mount()
    {
        $this->loans = Loan::with('details.itemUnit')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['approved','return_pending'])      // hanya yang bisa di-return
            ->latest()
            ->get();

        foreach ($this->loans as $loan) {
            $this->details[$loan->id] = $loan->details->map(fn($d) => [
                'loan_detail_id' => $d->id,
                'condition_after' => '',
                'condition_notes' => '',
            ])->toArray();
        }
    }

    public function getLoansProperty()
    {
        return \App\Models\Loan::with(['details.itemUnit.item'])
            ->where('user_id', auth()->id())   // hanya milik dia
            ->whereIn('status', ['approved','return_pending'])      // hanya yang bisa di-return
            ->latest()
            ->get();
    }
    
    public function submit($loanId)
    {
        $details = $this->details[$loanId] ?? [];

        app(\App\Services\LoanService::class)
            ->submitReturn($loanId, auth()->id(), $details);

        session()->flash('success', 'Pengembalian diajukan (menunggu approval)');
    }
};
?>

<!-- <div>
    {{-- Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh --}}
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
        
        @foreach ($loan->details as $index => $detail)
        
                    @php $unit = $detail->itemUnit; @endphp
            
                    <div class="border rounded p-2 text-sm">
            
                        <div class="font-medium">
                            {{ $unit->item->name }}
                        </div>
            
                        <div class="text-gray-500">
                            Code: {{ $unit->code }}
                        </div>
            
                        {{-- CONDITION --}}
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
            
                        {{-- 🔥 INPUT --}}
                        @include('partials.return-input', [
                            'detail' => $detail,
                            'index' => $index,
                            'loanId' => $loan->id
                        ])
                        
                    </div>
                        
                @endforeach

            <x-slot:actions>
                @if($loan->status === 'approved')
                    <flux:button wire:click="submit({{ $loan->id }})">
                        Ajukan Pengembalian
                    </flux:button>
                @elseif($loan->status === 'return_pending')
                    <flux:button disabled>
                        Menuggu Persetujuan
                    </flux:button>
                @endif
            </x-slot:actions>

        </x-loan.card>
    @empty

    <div class="border rounded-lg p-6 text-center text-gray-500">

        <div class="text-lg font-semibold">
            📦 Loan tidak ditemukan
        </div>

        <div class="text-sm mt-2">
            Anda Belum melakukan peminjaman atau semua peminjaman sudah selesai dikembalikan
        </div>

    </div>

    @endforelse

</div>
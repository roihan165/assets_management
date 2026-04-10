<?php

use Livewire\Component;
use App\Models\Loan;

new class extends Component
{
    public function getLoansProperty()
    {
        return Loan::with(['user', 'details.itemUnit.item'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function approve($loanId)
    {
        app(\App\Services\LoanService::class)
            ->approveLoan($loanId, auth()->id());

        session()->flash('success', 'Loan disetujui');
    }

    public function reject($loanId)
    {
        app(\App\Services\LoanService::class)
            ->rejectLoan($loanId, auth()->id());

        session()->flash('success', 'Loan ditolak');
    }
};
?>

<!-- <div>
    {{-- No surplus words or unnecessary actions. - Marcus Aurelius --}}
</div> -->

<div class="space-y-6">

    <flux:heading size="lg">
        Approval Peminjaman
    </flux:heading>

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

                        {{-- NOTES --}}
                        <div class="text-xs text-gray-500">
                            Catatan:
                            {{ $detail->condition_notes ?? '-' }}
                        </div>
                        
                    </div>
                        
                @endforeach

            <x-slot:actions>
    
                <flux:button size="sm"
                             wire:click="approve({{ $loan->id }})">
                    Approve
                </flux:button>
    
                <flux:button size="sm"
                             variant="danger"
                             wire:click="reject({{ $loan->id }})">
                    Reject
                </flux:button>
    
            </x-slot:actions>
    
        </x-loan.card>
    @empty
        <div class="border rounded-lg p-6 text-center text-gray-500">

            <div class="text-lg font-semibold">
                📭 Tidak ada pengajuan
            </div>

            <div class="text-sm mt-2">
                Semua permintaan sudah diproses
            </div>

        </div>
    @endforelse

</div>
<?php

use App\Models\AssetPurchaseRequest;
use App\Services\AssetPurchaseRequestService;
use Livewire\Component;

new class extends Component
{
    public array $reviewNotes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    public function getRequestsProperty()
    {
        return AssetPurchaseRequest::query()
            ->with(['requester', 'reviewer'])
            ->latest()
            ->get();
    }

    public function review(int $requestId, string $status): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $notes = trim($this->reviewNotes[$requestId] ?? '');
        if ($status === 'rejected' && $notes === '') {
            $this->addError("reviewNotes.$requestId", 'Catatan wajib diisi jika pengajuan ditolak.');
            return;
        }

        app(AssetPurchaseRequestService::class)->review(
            $requestId,
            auth()->id(),
            $status,
            $notes !== '' ? $notes : null,
        );

        unset($this->reviewNotes[$requestId]);
        session()->flash('success', $status === 'approved' ? 'Pengajuan aset disetujui.' : 'Pengajuan aset ditolak.');
    }
};
?>

<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <flux:heading size="lg">Persetujuan Pembelian Aset</flux:heading>
        <flux:text class="mt-1">Tinjau pengajuan dari akun SPV/atasan.</flux:text>
    </div>

    @if (session()->has('success'))
        <div class="rounded bg-green-100 px-4 py-2 text-green-800">{{ session('success') }}</div>
    @endif

    @forelse ($this->requests as $request)
        <flux:card class="space-y-4 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">{{ $request->asset_name }} <span class="font-normal text-zinc-500">× {{ $request->quantity }}</span></h2>
                    <p class="text-sm text-zinc-500">Diajukan {{ $request->created_at->format('d/m/Y H:i') }} oleh {{ $request->requester->name }}</p>
                </div>
                <span @class([
                    'rounded px-2 py-1 text-xs font-medium',
                    'bg-yellow-100 text-yellow-800' => $request->status === 'pending',
                    'bg-green-100 text-green-800' => $request->status === 'approved',
                    'bg-red-100 text-red-800' => $request->status === 'rejected',
                ])>{{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$request->status] }}</span>
            </div>

            <div class="grid gap-3 text-sm sm:grid-cols-2">
                <p><span class="text-zinc-500">Harga satuan:</span> Rp {{ number_format($request->estimated_unit_cost, 0, ',', '.') }}</p>
                <p><span class="text-zinc-500">Estimasi total:</span> <strong>Rp {{ number_format($request->estimated_unit_cost * $request->quantity, 0, ',', '.') }}</strong></p>
                @if ($request->needed_by)<p><span class="text-zinc-500">Dibutuhkan:</span> {{ $request->needed_by->format('d/m/Y') }}</p>@endif
            </div>
            <div>
                <h3 class="text-sm font-medium">Alasan pengajuan</h3>
                <p class="whitespace-pre-line text-sm text-zinc-700">{{ $request->justification }}</p>
            </div>
            @if ($request->specifications)
                <div>
                    <h3 class="text-sm font-medium">Spesifikasi / keterangan</h3>
                    <p class="whitespace-pre-line text-sm text-zinc-700">{{ $request->specifications }}</p>
                </div>
            @endif

            @if ($request->status === 'pending')
                <div class="space-y-2 border-t pt-4">
                    <label for="reviewNotes-{{ $request->id }}" class="block text-sm font-medium">Catatan keputusan <span class="font-normal text-zinc-500">(wajib saat menolak)</span></label>
                    <textarea id="reviewNotes-{{ $request->id }}" wire:model="reviewNotes.{{ $request->id }}" rows="2" class="w-full rounded border px-3 py-2"></textarea>
                    @error("reviewNotes.$request->id") <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="flex gap-2">
                        <flux:button wire:click="review({{ $request->id }}, 'approved')" wire:loading.attr="disabled" wire:target="review({{ $request->id }}, 'approved')">Setujui</flux:button>
                        <flux:button variant="danger" wire:click="review({{ $request->id }}, 'rejected')" wire:loading.attr="disabled" wire:target="review({{ $request->id }}, 'rejected')">Tolak</flux:button>
                    </div>
                </div>
            @elseif ($request->reviewer)
                <p class="border-t pt-3 text-sm text-zinc-500">Diproses oleh {{ $request->reviewer->name }} pada {{ $request->reviewed_at?->format('d/m/Y H:i') }}@if ($request->review_notes) · Catatan: {{ $request->review_notes }}@endif</p>
            @endif
        </flux:card>
    @empty
        <div class="rounded-lg border p-6 text-center text-zinc-500">Belum ada pengajuan pembelian aset.</div>
    @endforelse
</div>

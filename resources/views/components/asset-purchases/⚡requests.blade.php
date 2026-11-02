<?php

use App\Models\AssetPurchaseRequest;
use App\Services\AssetPurchaseRequestService;
use Livewire\Component;

new class extends Component
{
    public string $assetName = '';
    public string $specifications = '';
    public int|string $quantity = 1;
    public int|string $estimatedUnitCost = '';
    public string $justification = '';
    public string $neededBy = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('atasan'), 403);
    }

    public function getRequestsProperty()
    {
        return AssetPurchaseRequest::query()
            ->with('reviewer')
            ->where('requested_by', auth()->id())
            ->latest()
            ->get();
    }

    public function submit(): void
    {
        abort_unless(auth()->user()->hasRole('atasan'), 403);

        $data = $this->validate([
            'assetName' => ['required', 'string', 'max:255'],
            'specifications' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'estimatedUnitCost' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'justification' => ['required', 'string', 'max:5000'],
            'neededBy' => ['nullable', 'date', 'after_or_equal:today'],
        ], [], [
            'assetName' => 'nama aset',
            'specifications' => 'spesifikasi',
            'quantity' => 'jumlah',
            'estimatedUnitCost' => 'estimasi harga satuan',
            'justification' => 'alasan pengajuan',
            'neededBy' => 'tanggal dibutuhkan',
        ]);

        app(AssetPurchaseRequestService::class)->submit(auth()->id(), [
            'asset_name' => $data['assetName'],
            'specifications' => $data['specifications'] ?: null,
            'quantity' => $data['quantity'],
            'estimated_unit_cost' => $data['estimatedUnitCost'],
            'justification' => $data['justification'],
            'needed_by' => $data['neededBy'] ?: null,
        ]);

        $this->reset(['assetName', 'specifications', 'justification', 'neededBy', 'estimatedUnitCost']);
        $this->quantity = 1;
        session()->flash('success', 'Pengajuan pembelian aset berhasil dikirim ke Super Admin.');
    }
};
?>

<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <flux:heading size="lg">Pengajuan Pembelian Aset</flux:heading>
        <flux:text class="mt-1">Isi kebutuhan aset baru untuk diajukan kepada Super Admin.</flux:text>
    </div>

    @if (session()->has('success'))
        <div class="rounded bg-green-100 px-4 py-2 text-green-800">{{ session('success') }}</div>
    @endif

    <flux:card class="space-y-4 p-5">
        <form wire:submit="submit" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="assetName" class="mb-1 block text-sm font-medium">Nama aset</label>
                    <input id="assetName" type="text" wire:model="assetName" class="w-full rounded border px-3 py-2" placeholder="Contoh: Laptop kerja">
                    @error('assetName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="quantity" class="mb-1 block text-sm font-medium">Jumlah unit</label>
                    <input id="quantity" type="number" min="1" wire:model="quantity" class="w-full rounded border px-3 py-2">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="estimatedUnitCost" class="mb-1 block text-sm font-medium">Estimasi harga per unit (Rp)</label>
                    <input id="estimatedUnitCost" type="number" min="0" wire:model="estimatedUnitCost" class="w-full rounded border px-3 py-2" placeholder="Contoh: 7500000">
                    @error('estimatedUnitCost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="neededBy" class="mb-1 block text-sm font-medium">Tanggal dibutuhkan (opsional)</label>
                    <input id="neededBy" type="date" min="{{ now()->toDateString() }}" wire:model="neededBy" class="w-full rounded border px-3 py-2">
                    @error('neededBy') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="specifications" class="mb-1 block text-sm font-medium">Spesifikasi / keterangan</label>
                <textarea id="specifications" wire:model="specifications" rows="3" class="w-full rounded border px-3 py-2" placeholder="Spesifikasi minimum, merek acuan, atau kebutuhan khusus"></textarea>
                @error('specifications') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="justification" class="mb-1 block text-sm font-medium">Alasan dan manfaat pengadaan</label>
                <textarea id="justification" wire:model="justification" rows="3" required class="w-full rounded border px-3 py-2" placeholder="Jelaskan kebutuhan dan dampaknya bagi pekerjaan"></textarea>
                @error('justification') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <flux:button type="submit" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
                <span wire:loading wire:target="submit">Mengirim...</span>
            </flux:button>
        </form>
    </flux:card>

    <section class="space-y-4">
        <flux:heading size="md">Riwayat Pengajuan Saya</flux:heading>
        @forelse ($this->requests as $request)
            <flux:card class="space-y-3 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold">{{ $request->asset_name }} <span class="font-normal text-zinc-500">× {{ $request->quantity }}</span></h3>
                        <p class="text-sm text-zinc-500">Diajukan {{ $request->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span @class([
                        'rounded px-2 py-1 text-xs font-medium',
                        'bg-yellow-100 text-yellow-800' => $request->status === 'pending',
                        'bg-green-100 text-green-800' => $request->status === 'approved',
                        'bg-red-100 text-red-800' => $request->status === 'rejected',
                    ])>{{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$request->status] }}</span>
                </div>
                <p class="text-sm">Estimasi total: <strong>Rp {{ number_format($request->estimated_unit_cost * $request->quantity, 0, ',', '.') }}</strong></p>
                <p class="whitespace-pre-line text-sm">{{ $request->justification }}</p>
                @if ($request->specifications)<p class="whitespace-pre-line text-sm text-zinc-600">{{ $request->specifications }}</p>@endif
                @if ($request->needed_by)<p class="text-sm text-zinc-600">Dibutuhkan: {{ $request->needed_by->format('d/m/Y') }}</p>@endif
                @if ($request->review_notes)<p class="rounded bg-zinc-50 p-3 text-sm">Catatan Super Admin: {{ $request->review_notes }}</p>@endif
            </flux:card>
        @empty
            <div class="rounded-lg border p-6 text-center text-sm text-zinc-500">Belum ada pengajuan pembelian aset.</div>
        @endforelse
    </section>
</div>

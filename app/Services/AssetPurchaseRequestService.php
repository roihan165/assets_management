<?php

namespace App\Services;

use App\Models\AssetPurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetPurchaseRequestService
{
    public function submit(int $userId, array $data): AssetPurchaseRequest
    {
        return DB::transaction(function () use ($userId, $data) {
            $request = AssetPurchaseRequest::create([
                ...$data,
                'requested_by' => $userId,
                'status' => 'pending',
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($request)
                ->log('Mengajukan pembelian aset');

            return $request;
        });
    }

    public function review(int $requestId, int $adminId, string $status, ?string $notes = null): void
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'Status persetujuan tidak valid.']);
        }

        DB::transaction(function () use ($requestId, $adminId, $status, $notes) {
            $request = AssetPurchaseRequest::query()->lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['request' => 'Pengajuan ini sudah diproses.']);
            }

            $request->update([
                'status' => $status,
                'reviewed_by' => $adminId,
                'review_notes' => $notes,
                'reviewed_at' => now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($request)
                ->log($status === 'approved' ? 'Menyetujui pembelian aset' : 'Menolak pembelian aset');
        });
    }
}

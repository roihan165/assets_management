<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\ItemUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    public function createLoan(int $userId, array $itemUnitIds, $notes): Loan
    {
        return DB::transaction(function () use ($userId, $itemUnitIds) {

            // 1. Ambil data unit dari DB
            $units = ItemUnit::whereIn('id', $itemUnitIds)
                ->where('status', 'available')
                ->whereNotIn('condition_status', ['major_damage', 'lost'])
                ->get();

            if ($units->count() !== count($itemUnitIds)) {
                throw new \Exception('Beberapa unit tidak valid');
            }

            foreach ($units as $unit) {
                if ($unit->status !== 'available') {
                    throw ValidationException::withMessages([
                        'items' => "Barang {$unit->code} tidak tersedia"
                    ]);
                }
            }

            // 3. Buat loan
            $loan = Loan::create([
                'user_id' => $userId,
                'borrow_date' => now(),
                'status' => 'pending'
            ]);

            // 4. Simpan detail
            foreach ($units as $unit) {
                LoanDetail::create([
                    'loan_id' => $loan->id,
                    'item_unit_id' => $unit->id,
                    'condition_before' => $unit->condition_status,
                    'condition_notes' => $notes[$unit->id] ?? null,
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($loan)
                ->log('Membuat peminjaman');

            return $loan;
        });

    }

    public function approveLoan($loanId, $approverId)
    {
        $loan = Loan::with('details.itemUnit')->findOrFail($loanId);

        if (!auth()->user()->hasRole('atasan')) {
            throw new \Exception('Unauthorized');
        }

        if ($loan->status !== 'pending') {
            throw new \Exception('Loan tidak valid');
        }

        // update status loan
        $loan->update([
            'status' => 'approved',
            'approved_by' => $approverId,
        ]);

        // update unit jadi borrowed
        foreach ($loan->details as $detail) {
            $detail->itemUnit->update([
                'status' => 'borrowed'
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log('Approve loan');
    }

    public function rejectLoan($loanId, $approverId)
    {
        $loan = Loan::findOrFail($loanId);

        if (!auth()->user()->hasRole('atasan')) {
            throw new \Exception('Unauthorized');
        }

        $loan->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log('Reject loan');
    }

    public function submitReturn(int $loanId, int $userId, array $details): void
    {
        DB::transaction(function () use ($loanId, $userId, $details) {

            $loan = \App\Models\Loan::findOrFail($loanId);

            if ($loan->user_id !== $userId) {
                throw new \Exception('Unauthorized');
            }

            if ($loan->status !== 'approved') {
                throw new \Exception('Loan tidak valid untuk return');
            }
            
            // dd([
            //     'loan_id' => $loan->id,
            //     'available_detail_ids' => $loan->details->pluck('id')->toArray(),
            //     'incoming_detail_ids' => collect($details)->pluck('loan_detail_id')->toArray(),
            // ]);

            logger('DETAILS START', $details);

            foreach ($details as $item) {
                logger('DETAIL ITEM', $item);
            
                $detail = $loan->details->firstWhere('id', $item['loan_detail_id']);
            
                logger('FOUND DETAIL', [
                    'search' => $item['loan_detail_id'],
                    'result' => $detail?->id
                ]);

                $detail->update([
                    'condition_after' => $item['condition_after'],
                    'condition_notes' => $item['condition_notes'] ?? null,
                ]);

                $detail->refresh();

                logger('AFTER UPDATE', $detail->toArray());
            }

            // foreach ($details as $item) {
            //     // dd($details);

            //     $detail = \App\Models\LoanDetail::where('id', $item['loan_detail_id'])
            //         ->where('loan_id', $loan->id)
            //         ->first();

            //     dd($detail);
            //     if (!$detail) {
            //         throw new \Exception('Detail tidak ditemukan');
            //     }

            //     $detail->update([
            //         'condition_after' => $item['condition_after'],
            //         'condition_notes' => $item['condition_notes'] ?? null,
            //     ]);
            // }

            $loan->update([
                'status' => 'return_pending',
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($loan)
                ->log('Submit return');
        });
    }

    public function approveReturn(int $loanId, int $approverId): void
    {
        DB::transaction(function () use ($loanId, $approverId) {
        
            $loan = \App\Models\Loan::with('details.itemUnit')->findOrFail($loanId);
        
            if (!auth()->user()->hasRole('atasan')) {
                throw new \Exception('Unauthorized');
            }
        
            if ($loan->status !== 'return_pending') {
                throw new \Exception('Loan tidak valid');
            }
        
            foreach ($loan->details as $detail) {
            
                $unit = $detail->itemUnit;
            
                // 🔥 AMBIL DARI SNAPSHOT (loan_details)
                $condition = $detail->condition_after;
                $note = $detail->condition_notes;
            
                if (!$condition) {
                    throw new \Exception('Kondisi belum diisi oleh staff');
                }
            
                $unit->update([
                    'condition_status' => $condition,
                    'condition_notes' => $note,
                    'status' => match ($condition) {
                        'good' => 'available',
                        'minor_damage' => 'available', // atau maintenance (opsional)
                        'major_damage' => 'maintenance',
                        'lost' => 'lost',
                    }
                ]);
            }
        
            $loan->update([
                'status' => 'returned',
                'return_date' => now(),
                'approved_by' => $approverId,
            ]);
        
            activity()
                ->causedBy(auth()->user())
                ->performedOn($loan)
                ->log('Approve return');
        });
    }

    public function rejectReturn(int $loanId, int $approverId): void
    {
        $loan = \App\Models\Loan::findOrFail($loanId);
    
        if (!auth()->user()->hasRole('atasan')) {
            throw new \Exception('Unauthorized');
        }
    
        if ($loan->status !== 'return_pending') {
            throw new \Exception('Loan tidak valid');
        }
    
        $loan->update([
            'status' => 'approved', // balik ke sebelumnya
            'approved_by' => $approverId,
        ]);
    
        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log('Reject return');
    }

    public function returnLoan(int $loanId, array $conditions): void
    {
        DB::transaction(function () use ($loanId, $conditions) {

            $loan = Loan::with('details.itemUnit')->findOrFail($loanId);

            // 🔒 validasi
            if ($loan->status !== 'borrowed') {
                throw ValidationException::withMessages([
                    'loan' => 'Loan belum bisa dikembalikan'
                ]);
            }

            foreach ($loan->details as $detail) {

                $data = $conditions[$detail->id] ?? null;

                if (!$data) {
                    throw ValidationException::withMessages([
                        'condition' => 'Semua kondisi harus diisi'
                    ]);
                }

                // update detail
                $detail->update([
                    'condition_after' => $data['status'],
                    'condition_notes' => $data['notes'] ?? null,
                ]);

                // update item unit
                $status = match ($data['status']) {
                    'good', 'minor_damage' => 'available',
                    'major_damage' => 'maintenance',
                    'lost' => 'lost',
                };

                $detail->itemUnit->update([
                    'status' => $status,
                    'condition_status' => $data['status'],
                    'condition_notes' => $data['notes'] ?? null,
                ]);
            }
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($loan)
                ->log('Mengembalikan barang');
                
            $loan->update([
                'status' => 'returned',
                'return_date' => now()
            ]);
        });

    }
}
<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Support\Facades\DB;

class ItemService
{
    public function create(array $data): void
    {
        DB::transaction(function () use ($data) {

            // 🔹 1. CREATE ITEM
            $item = Item::create([
                'name' => $data['name'],
            ]);

            // 🔹 2. AUTO MODE
            if ($data['mode'] === 'auto') {

                for ($i = 0; $i < $data['total_units']; $i++) {

                    ItemUnit::create([
                        'item_id' => $item->id,
                        'code' => $this->generateCode($item->id, $i),
                        'status' => 'available',
                        'condition_status' => 'good',
                    ]);
                }
            }

            // 🔹 3. MANUAL MODE
            if ($data['mode'] === 'manual') {

                // ❗ prevent duplicate code
                if (ItemUnit::where('code', $data['code'])->exists()) {
                    throw new \Exception('Kode sudah digunakan');
                }

                ItemUnit::create([
                    'item_id' => $item->id,
                    'code' => strtoupper($data['code']),
                    'status' => $this->mapConditionToStatus($data['condition']),
                    'condition_status' => $data['condition'],
                    'condition_notes' => $data['note'] ?? null,
                ]);
            }
        });
    }

    private function generateCode($itemId, $index): string
    {
        return 'ITM-' . str_pad($itemId, 3, '0', STR_PAD_LEFT)
            . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
    }

    private function mapConditionToStatus($condition): string
    {
        return match ($condition) {
            'good', 'minor_damage' => 'available',
            'major_damage' => 'maintenance',
            'lost' => 'lost',
        };
    }
}
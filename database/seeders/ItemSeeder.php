<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['available', 'borrowed', 'maintenance', 'lost'];
    
        // 🔥 Mapping rules
        $statusConditionMap = [
            'available' => ['good', 'minor_damage'],
            'borrowed' => ['good', 'minor_damage'],
            'maintenance' => ['major_damage'],
            'lost' => ['lost'],
        ];
    
        for ($i = 1; $i <= 15; $i++) {
        
            $item = Item::create([
                'name' => 'Barang ' . $i,
            ]);
        
            $unitCount = rand(5, 10);
        
            for ($j = 1; $j <= $unitCount; $j++) {
            
                // 🔥 Random status
                $status = $statuses[array_rand($statuses)];
            
                // 🔥 Ambil condition sesuai status
                $allowedConditions = $statusConditionMap[$status];
                $condition = $allowedConditions[array_rand($allowedConditions)];
            
                ItemUnit::create([
                    'item_id' => $item->id,
                    'code' => strtoupper(Str::random(6)),
                    'status' => $status,
                    'condition_status' => $condition,
                ]);
            }
        }
    }
}
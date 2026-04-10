<div class="p-3 border rounded-lg space-y-2">

    {{-- UNIT --}}
    <div class="text-sm font-medium">
        {{ $detail->itemUnit->name }}
    </div>

    {{-- BEFORE --}}
    <div class="text-xs text-gray-500">
        Sebelum:
        <span class="font-medium">
            {{ $detail->condition_before }}
        </span>
    </div>

    {{-- AFTER (INI DARI STAFF 🔥) --}}
    <div class="text-xs text-gray-500">
        Setelah:
        <span class="font-semibold text-yellow-600">
            {{ $detail->condition_after ?? '-' }}
        </span>
    </div>

    {{-- NOTES --}}
    <div class="text-xs text-gray-500">
        Catatan:
        {{ $detail->condition_notes ?? '-' }}
    </div>

</div>
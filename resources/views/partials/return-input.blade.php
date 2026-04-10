<div>

    <input type="hidden"
       wire:model="details.{{ $loanId }}.{{ $index }}.loan_detail_id">

    <select wire:model="details.{{ $loanId }}.{{ $index }}.condition_after"
            class="border rounded px-2 py-1 text-sm">
        <option value="">-- pilih kondisi --</option>
        <option value="good">Baik</option>
        <option value="minor_damage">Minor</option>
        <option value="major_damage">Major</option>
        <option value="lost">Hilang</option>
    </select>

    <input type="text"
           wire:model="details.{{ $loanId }}.{{ $index }}.condition_notes"
           placeholder="Catatan"
           class="border rounded px-2 py-1 text-sm w-full mt-1">

</div>
<!-- <div>
     Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh
</div> -->

@props([
    'loan',
    'showActions' => true
])

<flux:card class="p-4">

    @if($loan->status === 'return_pending')
        <div class="text-sm text-yellow-600">
            ⏳ Menunggu persetujuan pengembalian
        </div>
    @endif
    {{-- HEADER --}}
    <div class="flex justify-between items-center">

        <div>
            <div class="font-medium">
                {{ $loan->user->name }}
            </div>

            <div class="text-sm text-gray-500">
                Loan #{{ $loan->id }} • {{ $loan->created_at }}
            </div>
        </div>

        {{-- SLOT ACTION --}}
        @if ($showActions)
            <div class="flex gap-2">
                {{ $actions ?? '' }}
            </div>
        @endif

    </div>

    {{-- ITEMS --}}
    <div class="mt-4 space-y-2">

        <div>
            {{ $slot }}
        </div>

    </div>

</flux:card>
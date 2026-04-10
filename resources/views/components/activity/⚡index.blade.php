<?php

use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

new class extends Component
{
    public function getLogsProperty()
    {
        return Activity::with('causer')
            ->latest()
            ->limit(50)
            ->get();
    }
};
?>

<!-- <div>
    {{-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius --}}
</div> -->

<div class="max-w-4xl mx-auto space-y-6">

    <flux:heading size="lg">
        Audit Log
    </flux:heading>

    <flux:card class="p-4">

        <table class="w-full text-sm">

            <thead class="border-b">
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aktivitas</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($this->logs as $log)
                    <tr class="border-b">

                        <td class="py-2">
                            {{ $log->created_at }}
                        </td>

                        <td>
                            {{ $log->causer->name ?? '-' }}
                        </td>

                        <td>
                            {{ $log->description }}
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </flux:card>

</div>
<?php

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Services\ItemService;
use App\Services\LoanService;
use Livewire\Livewire;

function testItemWithUnits(string $name = 'Laptop', int $count = 1, string $status = 'available', string $condition = 'good'): array
{
    $item = Item::create(['name' => $name]);
    $units = collect(range(1, $count))->map(fn ($number) => ItemUnit::create([
        'item_id' => $item->id,
        'code' => strtoupper(substr($name, 0, 3)) . '-' . $item->id . '-' . $number,
        'status' => $status,
        'condition_status' => $condition,
    ]));

    return [$item, $units];
}

test('inventory service creates a batch of available units with generated codes', function () {
    app(ItemService::class)->create([
        'name' => 'Monitor',
        'mode' => 'auto',
        'total_units' => 3,
    ]);

    $item = Item::where('name', 'Monitor')->firstOrFail();

    expect($item->units)->toHaveCount(3)
        ->and($item->units->pluck('code')->all())->toBe([
            'ITM-' . str_pad($item->id, 3, '0', STR_PAD_LEFT) . '-001',
            'ITM-' . str_pad($item->id, 3, '0', STR_PAD_LEFT) . '-002',
            'ITM-' . str_pad($item->id, 3, '0', STR_PAD_LEFT) . '-003',
        ])
        ->and($item->units->every(fn ($unit) => $unit->status === 'available' && $unit->condition_status === 'good'))->toBeTrue();
});

test('dashboard displays inventory counts from unit statuses', function () {
    testItemWithUnits('Available', 2, 'available');
    testItemWithUnits('Borrowed', 1, 'borrowed');
    testItemWithUnits('Maintenance', 1, 'maintenance', 'major_damage');
    $this->actingAs(testUserWithRole('staff'));

    Livewire::test('dashboard.index')
        ->assertSee('Total Barang')
        ->assertSee('4')
        ->assertSee('Sedang Dipinjam')
        ->assertSee('Siap Dipinjam');
});

test('inventory service maps manual unit condition to its availability status', function (string $condition, string $status) {
    app(ItemService::class)->create([
        'name' => 'Printer',
        'mode' => 'manual',
        'code' => 'prt-001',
        'condition' => $condition,
        'note' => 'Initial condition',
    ]);

    $unit = ItemUnit::firstOrFail();

    expect($unit->code)->toBe('PRT-001')
        ->and($unit->condition_status)->toBe($condition)
        ->and($unit->status)->toBe($status)
        ->and($unit->condition_notes)->toBe('Initial condition');
})->with([
    'good items are available' => ['good', 'available'],
    'minor damage remains available' => ['minor_damage', 'available'],
    'major damage enters maintenance' => ['major_damage', 'maintenance'],
    'lost items are marked lost' => ['lost', 'lost'],
]);

test('inventory service rolls back a new item when its manual unit code is duplicated', function () {
    [, $units] = testItemWithUnits('Existing', 1);

    expect(fn () => app(ItemService::class)->create([
        'name' => 'Duplicate',
        'mode' => 'manual',
        'code' => $units->first()->code,
        'condition' => 'good',
    ]))->toThrow(Exception::class, 'Kode sudah digunakan');

    expect(Item::where('name', 'Duplicate')->exists())->toBeFalse();
});

test('supervisor can create an item from the inventory form and update its unit condition', function () {
    $supervisor = testUserWithRole('atasan');
    $this->actingAs($supervisor);

    Livewire::test('items.create')
        ->set('name', 'Webcam')
        ->set('mode', 'manual')
        ->set('code', 'cam-001')
        ->set('condition', 'minor_damage')
        ->set('note', 'Goresan kecil')
        ->call('store')
        ->assertHasNoErrors();

    $unit = ItemUnit::where('code', 'CAM-001')->firstOrFail();
    expect($unit->status)->toBe('available')
        ->and($unit->condition_status)->toBe('minor_damage');

    Livewire::test('items.manage')
        ->call('updateUnit', $unit->id, 'condition_status', 'good')
        ->assertHasNoErrors();
    expect($unit->fresh()->condition_status)->toBe('good');
});

test('staff can request available units and the loan records their condition snapshot', function () {
    $staff = testUserWithRole('staff');
    $this->actingAs($staff);
    [, $units] = testItemWithUnits('Laptop', 2);

    $loan = app(LoanService::class)->createLoan($staff->id, $units->pluck('id')->all(), [$units[0]->id => 'Screen good']);

    expect($loan->status)->toBe('pending')
        ->and($loan->details)->toHaveCount(2)
        ->and($loan->details->first()->condition_before)->toBe('good')
        ->and($loan->details->first()->condition_notes)->toBe('Screen good');
    $this->assertDatabaseHas('activity_log', ['subject_id' => $loan->id, 'description' => 'Membuat peminjaman']);
});

test('staff can preview and confirm a loan from the loan request form', function () {
    $staff = testUserWithRole('staff');
    [, $units] = testItemWithUnits('Webcam', 1);
    $this->actingAs($staff);

    Livewire::test('loans.create')
        ->set('selectedUnits', [$units[0]->id])
        ->call('submit')
        ->assertSet('showPreview', true)
        ->call('confirm')
        ->assertRedirect(route('dashboard'));

    expect(Loan::where('user_id', $staff->id)->firstOrFail()->status)->toBe('pending');
});

test('loan service refuses units that are unavailable or in unusable condition', function () {
    $staff = testUserWithRole('staff');
    $this->actingAs($staff);
    [, $borrowed] = testItemWithUnits('Borrowed', 1, 'borrowed');
    [, $damaged] = testItemWithUnits('Damaged', 1, 'available', 'major_damage');

    expect(fn () => app(LoanService::class)->createLoan($staff->id, [$borrowed->first()->id], []))->toThrow(Exception::class);
    expect(fn () => app(LoanService::class)->createLoan($staff->id, [$damaged->first()->id], []))->toThrow(Exception::class);

    expect(Loan::count())->toBe(0);
});

test('supervisor can approve or reject pending loans and approval reserves the units', function () {
    $staff = testUserWithRole('staff');
    $supervisor = testUserWithRole('atasan');
    [, $units] = testItemWithUnits('Keyboard', 2);
    $this->actingAs($staff);
    $approved = app(LoanService::class)->createLoan($staff->id, [$units[0]->id], []);
    $rejected = app(LoanService::class)->createLoan($staff->id, [$units[1]->id], []);

    $this->actingAs($supervisor);
    app(LoanService::class)->approveLoan($approved->id, $supervisor->id);
    app(LoanService::class)->rejectLoan($rejected->id, $supervisor->id);

    expect($approved->fresh()->status)->toBe('approved')
        ->and($approved->fresh()->approved_by)->toBe($supervisor->id)
        ->and($units[0]->fresh()->status)->toBe('borrowed')
        ->and($rejected->fresh()->status)->toBe('rejected')
        ->and($units[1]->fresh()->status)->toBe('available');
});

test('loan approval screen approves the selected pending loan', function () {
    $staff = testUserWithRole('staff');
    $supervisor = testUserWithRole('atasan');
    [, $units] = testItemWithUnits('Dock', 1);
    $this->actingAs($staff);
    $loan = app(LoanService::class)->createLoan($staff->id, [$units[0]->id], []);

    $this->actingAs($supervisor);
    Livewire::test('loans.approve')
        ->call('approve', $loan->id)
        ->assertHasNoErrors();

    expect($loan->fresh()->status)->toBe('approved')
        ->and($units[0]->fresh()->status)->toBe('borrowed');
});

test('staff can submit a return and supervisor approval updates unit condition and availability', function () {
    $staff = testUserWithRole('staff');
    $supervisor = testUserWithRole('atasan');
    [, $units] = testItemWithUnits('Tablet', 1, 'borrowed');
    $loan = Loan::create(['user_id' => $staff->id, 'borrow_date' => now(), 'status' => 'approved']);
    $detail = LoanDetail::create(['loan_id' => $loan->id, 'item_unit_id' => $units[0]->id, 'condition_before' => 'good']);

    $this->actingAs($staff);
    app(LoanService::class)->submitReturn($loan->id, $staff->id, [[
        'loan_detail_id' => $detail->id,
        'condition_after' => 'major_damage',
        'condition_notes' => 'Screen cracked',
    ]]);

    expect($loan->fresh()->status)->toBe('return_pending')
        ->and($detail->fresh()->condition_after)->toBe('major_damage');

    $this->actingAs($supervisor);
    app(LoanService::class)->approveReturn($loan->id, $supervisor->id);

    expect($loan->fresh()->status)->toBe('returned')
        ->and($loan->fresh()->return_date)->not->toBeNull()
        ->and($units[0]->fresh()->status)->toBe('maintenance')
        ->and($units[0]->fresh()->condition_notes)->toBe('Screen cracked');
});

test('staff can send return details through the return form', function () {
    $staff = testUserWithRole('staff');
    [, $units] = testItemWithUnits('Speaker', 1, 'borrowed');
    $loan = Loan::create(['user_id' => $staff->id, 'borrow_date' => now(), 'status' => 'approved']);
    $detail = LoanDetail::create(['loan_id' => $loan->id, 'item_unit_id' => $units[0]->id, 'condition_before' => 'good']);
    $this->actingAs($staff);

    Livewire::test('loans.return')
        ->set("details.{$loan->id}.0.loan_detail_id", $detail->id)
        ->set("details.{$loan->id}.0.condition_after", 'minor_damage')
        ->set("details.{$loan->id}.0.condition_notes", 'Tombol agak keras')
        ->call('submit', $loan->id)
        ->assertHasNoErrors();

    expect($loan->fresh()->status)->toBe('return_pending')
        ->and($detail->fresh()->condition_after)->toBe('minor_damage');
});

test('return approval screen completes the two step return workflow', function () {
    $staff = testUserWithRole('staff');
    $supervisor = testUserWithRole('atasan');
    [, $units] = testItemWithUnits('Headset', 1, 'borrowed');
    $loan = Loan::create(['user_id' => $staff->id, 'borrow_date' => now(), 'status' => 'return_pending']);
    LoanDetail::create([
        'loan_id' => $loan->id,
        'item_unit_id' => $units[0]->id,
        'condition_before' => 'good',
        'condition_after' => 'good',
        'condition_notes' => 'Utuh',
    ]);

    $this->actingAs($supervisor);
    Livewire::test('loans.return-approval')
        ->call('approve', $loan->id)
        ->assertHasNoErrors()
        ->assertSee('Return disetujui');

    expect($loan->fresh()->status)->toBe('returned')
        ->and($units[0]->fresh()->status)->toBe('available');
});

test('staff cannot submit another users return and supervisor can reject a return', function () {
    $staff = testUserWithRole('staff');
    $otherStaff = testUserWithRole('staff');
    $supervisor = testUserWithRole('atasan');
    [, $units] = testItemWithUnits('Camera', 1, 'borrowed');
    $loan = Loan::create(['user_id' => $otherStaff->id, 'borrow_date' => now(), 'status' => 'return_pending']);
    LoanDetail::create(['loan_id' => $loan->id, 'item_unit_id' => $units[0]->id, 'condition_before' => 'good', 'condition_after' => 'good']);

    $this->actingAs($staff);
    expect(fn () => app(LoanService::class)->submitReturn($loan->id, $staff->id, []))->toThrow(Exception::class, 'Unauthorized');

    $this->actingAs($supervisor);
    app(LoanService::class)->rejectReturn($loan->id, $supervisor->id);

    expect($loan->fresh()->status)->toBe('approved')
        ->and($units[0]->fresh()->status)->toBe('borrowed');
});

test('legacy one step return updates detail, unit condition and loan status', function () {
    $staff = testUserWithRole('staff');
    $this->actingAs($staff);
    [, $units] = testItemWithUnits('Mouse', 1, 'borrowed');
    $loan = Loan::create(['user_id' => $staff->id, 'borrow_date' => now(), 'status' => 'borrowed']);
    $detail = LoanDetail::create(['loan_id' => $loan->id, 'item_unit_id' => $units[0]->id, 'condition_before' => 'good']);

    app(LoanService::class)->returnLoan($loan->id, [$detail->id => ['status' => 'lost', 'notes' => 'Tidak ditemukan']]);

    expect($loan->fresh()->status)->toBe('returned')
        ->and($detail->fresh()->condition_after)->toBe('lost')
        ->and($units[0]->fresh()->status)->toBe('lost');
});

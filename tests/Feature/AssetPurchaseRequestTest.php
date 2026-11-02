<?php

use App\Models\AssetPurchaseRequest;
use App\Services\AssetPurchaseRequestService;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('supervisor can submit an asset purchase request through the form', function () {
    $supervisor = testUserWithRole('atasan');
    $this->actingAs($supervisor);

    Livewire::test('asset-purchases.requests')
        ->set('assetName', 'Laptop desain')
        ->set('specifications', 'RAM minimal 32 GB')
        ->set('quantity', 2)
        ->set('estimatedUnitCost', 18000000)
        ->set('justification', 'Perangkat lama tidak mendukung pekerjaan desain.')
        ->set('neededBy', now()->addWeek()->toDateString())
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('Pengajuan pembelian aset berhasil dikirim');

    $request = AssetPurchaseRequest::firstOrFail();
    expect($request->requested_by)->toBe($supervisor->id)
        ->and($request->asset_name)->toBe('Laptop desain')
        ->and($request->quantity)->toBe(2)
        ->and($request->estimated_unit_cost)->toBe(18000000)
        ->and($request->status)->toBe('pending');
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => AssetPurchaseRequest::class,
        'subject_id' => $request->id,
        'description' => 'Mengajukan pembelian aset',
    ]);
});

test('asset purchase request form validates required fields, quantity and cost', function () {
    $this->actingAs(testUserWithRole('atasan'));

    Livewire::test('asset-purchases.requests')
        ->set('quantity', 0)
        ->set('estimatedUnitCost', -1)
        ->call('submit')
        ->assertHasErrors(['assetName' => 'required', 'quantity' => 'min', 'estimatedUnitCost' => 'min', 'justification' => 'required']);

    expect(AssetPurchaseRequest::count())->toBe(0);
});

test('super admin can approve a request and the decision is audited', function () {
    $supervisor = testUserWithRole('atasan');
    $admin = testUserWithRole('admin');
    $this->actingAs($supervisor);
    $request = app(AssetPurchaseRequestService::class)->submit($supervisor->id, [
        'asset_name' => 'Proyektor',
        'specifications' => null,
        'quantity' => 1,
        'estimated_unit_cost' => 9000000,
        'justification' => 'Untuk ruang rapat.',
        'needed_by' => null,
    ]);

    $this->actingAs($admin);
    Livewire::test('asset-purchases.approval')
        ->call('review', $request->id, 'approved')
        ->assertHasNoErrors()
        ->assertSee('Pengajuan aset disetujui');

    expect($request->fresh()->status)->toBe('approved')
        ->and($request->fresh()->reviewed_by)->toBe($admin->id)
        ->and($request->fresh()->reviewed_at)->not->toBeNull();
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => AssetPurchaseRequest::class,
        'subject_id' => $request->id,
        'description' => 'Menyetujui pembelian aset',
    ]);
});

test('super admin must provide a note when rejecting and can then reject the request', function () {
    $supervisor = testUserWithRole('atasan');
    $admin = testUserWithRole('admin');
    $this->actingAs($supervisor);
    $request = app(AssetPurchaseRequestService::class)->submit($supervisor->id, [
        'asset_name' => 'Kursi',
        'specifications' => null,
        'quantity' => 4,
        'estimated_unit_cost' => 500000,
        'justification' => 'Kursi rusak.',
        'needed_by' => null,
    ]);

    $this->actingAs($admin);
    Livewire::test('asset-purchases.approval')
        ->call('review', $request->id, 'rejected')
        ->assertHasErrors(["reviewNotes.{$request->id}"]);

    Livewire::test('asset-purchases.approval')
        ->set("reviewNotes.{$request->id}", 'Ajukan kembali setelah evaluasi anggaran.')
        ->call('review', $request->id, 'rejected')
        ->assertHasNoErrors();

    expect($request->fresh()->status)->toBe('rejected')
        ->and($request->fresh()->review_notes)->toBe('Ajukan kembali setelah evaluasi anggaran.');
});

test('a processed purchase request cannot be reviewed a second time', function () {
    $supervisor = testUserWithRole('atasan');
    $admin = testUserWithRole('admin');
    $this->actingAs($supervisor);
    $request = app(AssetPurchaseRequestService::class)->submit($supervisor->id, [
        'asset_name' => 'Scanner',
        'specifications' => null,
        'quantity' => 1,
        'estimated_unit_cost' => 1000000,
        'justification' => 'Digitalisasi dokumen.',
        'needed_by' => null,
    ]);
    $this->actingAs($admin);
    app(AssetPurchaseRequestService::class)->review($request->id, $admin->id, 'approved');

    expect(fn () => app(AssetPurchaseRequestService::class)->review($request->id, $admin->id, 'rejected', 'Terlambat'))
        ->toThrow(ValidationException::class);
    expect($request->fresh()->status)->toBe('approved');
});

test('asset purchase pages are restricted to their assigned roles', function () {
    $staff = testUserWithRole('staff');
    $this->actingAs($staff)->get('/asset-purchases')->assertForbidden();
    $this->actingAs(testUserWithRole('atasan'))->get('/asset-purchases/approval')->assertForbidden();

    $this->actingAs(testUserWithRole('atasan'))->get('/asset-purchases')->assertOk();
    $this->actingAs(testUserWithRole('admin'))->get('/asset-purchases/approval')->assertOk();
});

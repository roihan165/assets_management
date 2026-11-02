<?php

use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

test('role middleware protects inventory, loan, user, activity and report pages', function () {
    $staff = testUserWithRole('staff');
    $this->actingAs($staff);

    $this->get('/items/manage')->assertForbidden();
    $this->get('/items/create')->assertForbidden();
    $this->get('/loans/approve')->assertForbidden();
    $this->get('/loans/return-approval')->assertForbidden();
    $this->get('/users')->assertForbidden();
    $this->get('/activity')->assertForbidden();
    $this->get('/reports/loans/pdf')->assertForbidden();
    $this->get('/loans/create')->assertOk();
    $this->get('/loans/return')->assertOk();
});

test('supervisor can access inventory and loan approvals but not the admin audit page', function () {
    $supervisor = testUserWithRole('atasan');
    $this->actingAs($supervisor);

    $this->get('/items/manage')->assertOk();
    $this->get('/items/create')->assertOk();
    $this->get('/loans/approve')->assertOk();
    $this->get('/loans/return-approval')->assertOk();
    $this->get('/users')->assertOk();
    $this->get('/activity')->assertForbidden();
    $this->get('/reports/loans/pdf')->assertForbidden();
});

test('admin can create users and assign the selected role', function () {
    $this->actingAs(testUserWithRole('admin'));

    Livewire::test('users.create')
        ->set('name', 'Test Atasan')
        ->set('email', 'new-supervisor@example.com')
        ->set('role', 'atasan')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('User berhasil dibuat');

    $created = User::where('email', 'new-supervisor@example.com')->firstOrFail();
    expect($created->hasRole('atasan'))->toBeTrue();
    $this->assertDatabaseHas('activity_log', ['subject_id' => $created->id, 'description' => 'Membuat user baru']);
});

test('user form rejects invalid roles and duplicate email addresses', function () {
    $this->actingAs(testUserWithRole('admin'));
    $existing = User::factory()->create(['email' => 'used@example.com']);

    Livewire::test('users.create')
        ->set('name', 'Test User')
        ->set('email', $existing->email)
        ->set('role', 'unknown')
        ->call('submit')
        ->assertHasErrors(['email' => 'unique', 'role' => 'in']);
});

test('user management lets authorized users update roles and only admins delete accounts', function () {
    $supervisor = testUserWithRole('atasan');
    $target = User::factory()->create();
    $this->actingAs($supervisor);

    Livewire::test('users.index')
        ->set("selectedRoles.{$target->id}", 'staff')
        ->call('updateRole', $target->id)
        ->assertHasNoErrors();
    expect($target->fresh()->hasRole('staff'))->toBeTrue();

    $admin = testUserWithRole('admin');
    $this->actingAs($admin);
    Livewire::test('users.index')->call('deleteUser', $target->id)->assertHasNoErrors();
    expect($target->fresh())->toBeNull();
});

test('activity page lists recent activity and remains admin only', function () {
    $admin = testUserWithRole('admin');
    activity()->causedBy($admin)->log('Test activity entry');

    $this->actingAs($admin)->get('/activity')->assertOk()->assertSee('Test activity entry');
    $this->actingAs(testUserWithRole('staff'))->get('/activity')->assertForbidden();
});

test('admin can download the inventory and loan report as a PDF', function () {
    $admin = testUserWithRole('admin');

    $this->actingAs($admin)
        ->get('/reports/loans/pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('default user seeder provisions all role accounts with the documented credentials', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserSeeder::class);

    expect(User::where('email', 'admin@example.com')->firstOrFail()->hasRole('admin'))->toBeTrue()
        ->and(User::where('email', 'spv@example.com')->firstOrFail()->hasRole('atasan'))->toBeTrue()
        ->and(User::where('email', 'staff@example.com')->firstOrFail()->hasRole('staff'))->toBeTrue();

    $this->assertDatabaseCount('users', 3);
});

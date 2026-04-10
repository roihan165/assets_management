<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::view('/items/manage', 'items.manage')
    ->middleware(['auth', 'role:atasan']);
Route::view('/items/create', 'items.create')
    ->middleware(['auth', 'role:atasan']);

// Route untuk generate PDF laporan loan

Route::get('/reports/loans/pdf', [ReportController::class, 'loanPdf'])
    ->middleware(['auth', 'role:admin']);

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::view('/users/create', 'users.create')
    ->middleware(['auth','role:admin|atasan']);

Route::view('/users', 'users.index')
    ->middleware(['auth', 'role:admin|atasan']);

Route::view('/activity', 'activity.index')
    ->middleware(['auth', 'role:admin']);

// Route::livewire('/post/create/{dummy}', 'pages::post.create');

Route::view('/loans/create', 'loans.create')
    ->middleware(['auth','role:staff']);
Route::view('/loans/approve', 'loans.approve')
    ->middleware(['auth','role:atasan']);
// STAFF
Route::view('/loans/return', 'loans.return')
    ->middleware(['auth', 'role:staff']);

// ATASAN
Route::view('/loans/return-approval', 'loans.return-approval')
    ->middleware(['auth', 'role:atasan']);

require __DIR__.'/settings.php';

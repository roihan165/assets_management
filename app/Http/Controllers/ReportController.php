<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function loanPdf()
    {
        $items = Item::withCount([
            'units as total_units',
            'units as available_units' => fn($q) => $q->where('status', 'available'),
            'units as borrowed_units' => fn($q) => $q->where('status', 'borrowed'),
            'units as maintenance_units' => fn($q) => $q->where('status', 'maintenance'),
            'units as lost_units' => fn($q) => $q->where('status', 'lost'),
        ])->get();

        $loans = Loan::with('user', 'details.itemUnit.item')->latest()->get();

        $pdf = Pdf::loadView('reports.loan-pdf', compact('items', 'loans'));

        return $pdf->download('laporan-peminjaman.pdf');
    }
}

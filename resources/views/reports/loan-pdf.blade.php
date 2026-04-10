<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Barang</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2, .header h3, .header p {
            margin: 0;
        }

        .meta {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 10px;
        }

    </style>
</head>

<body>

    {{-- 🔥 HEADER --}}
    <div class="header">
        <img src="{{ public_path('logo.png') ?? '' }}" width="120">
        <h2>LAPORAN DATA BARANG</h2>
        <h3>SISTEM MANAJEMEN ASSET</h3>
        <p><strong>PT. SINERGI TEKNOGLOBAL PERKASA</strong></p>
        <p>ASSET MANAGEMENT</p>
    </div>

    {{-- 🔥 META --}}
    <div class="meta">
        <table>
            <tr>
                <td width="50%">
                    Dibuat pada : {{ now()->format('d/m/Y H:i:s') }}
                </td>
                <td width="50%">
                    Jenis Export : Data Master (Semua Data)
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Total Records : {{ $items->count() }} data
                </td>
            </tr>
        </table>
    </div>
    
    <h3 style="margin-top: 20px;">
        I. Data Barang
    </h3>
    {{-- 🔥 TABLE --}}
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Barang</th>
                <th width="10%">Kode</th>
                <th width="20%">Deskripsi</th>
                <th width="8%">Tersedia</th>
                <th width="8%">Dipinjam</th>
                <th width="8%">Maintenance</th>
                <th width="8%">Hilang</th>
                <th width="8%">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $index => $item)

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $item->name }}
                    </td>

                    <td class="text-center">
                        {{ $item->code ?? '-' }}
                    </td>

                    <td class="small">
                        {{ $item->condition_notes ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->available_units ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $item->borrowed_units ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $item->maintenance_units ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $item->lost_units ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $item->total_units ?? 0 }}
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        Tidak ada data
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <hr style="margin: 20px 0;">

    {{-- 🔥 SECTION 2 --}}
    <div style="margin-top: 25px;">

        <h3 style="margin-top: 20px;">
            II. Riwayat Peminjaman
        </h3>

        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="10%">User</th>
                    <th width="30%">Barang</th>
                    <th width="8%">Jumlah</th>
                    <th width="10%">Status</th>
                    <th width="12%">Tanggal</th>
                    <th width="8%">Mulai</th>
                    <th width="8%">Selesai</th>
                    <th width="9%">Durasi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($loans as $loan)

                    @php
                        $start = $loan->created_at;
                        $end = $loan->return_date 
                            ? \Carbon\Carbon::parse($loan->return_date) 
                            : null;

                        $duration = null;

                        if ($end && $end->greaterThan($start)) {
                            $diff = $start->diff($end);
                            $duration = $diff->h . ' jam ' . $diff->i . ' menit';
                        }
                    @endphp

                    <tr>
                        <td class="text-center">{{ $loan->id }}</td>

                        <td>{{ $loan->user->name ?? '-' }}</td>

                        <td class="small">
                            @foreach($loan->details as $detail)
                                • {{ $detail->itemUnit->item->name }}
                                ({{ $detail->itemUnit->code }})<br>
                            @endforeach
                        </td>

                        <td class="text-center">
                            {{ $loan->details->count() }}
                        </td>

                        <td class="text-center">
                            {{ ucfirst($loan->status) }}
                        </td>

                        <td>
                            {{ $start->format('d-m-Y') }}
                        </td>

                        <td class="text-center">
                            {{ $start->format('H:i') }}
                        </td>

                        <td class="text-center">
                            {{ ($end && $end->greaterThan($start)) 
                                ? $end->format('H:i') 
                                : '-' }}
                        </td>

                        <td class="text-center">
                            {{ $duration ?? '-' }}
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9" class="text-center">
                            Tidak ada data peminjaman
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</body>
</html>
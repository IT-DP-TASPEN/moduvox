<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Jurnal API</title>
    @if(!$isExcel)
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        @media print {
            body { font-size: 10px; }
            th, td { padding: 4px; }
            @page { size: landscape; }
        }
    </style>
    @endif
</head>
<body @if(!$isExcel) onload="window.print()" @endif>

    <div class="header">
        <h2>Laporan Status Jurnal API FinCloud</h2>
        <p>
            Cabang: {{ $kantor ? $kantor->kode . ' - ' . $kantor->nama : 'Semua Cabang' }} |
            Periode: {{ $periodeLabel }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Reff ID</th>
                <th>Cabang</th>
                <th>Batch</th>
                <th>Akun Debet</th>
                <th>Akun Kredit</th>
                <th class="text-right">Amount (Rp)</th>
                <th>Status</th>
                <th>Core Reff</th>
                <th>Tanggal Kirim</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @forelse($journals as $index => $journal)
                @php
                    $cabangCode = substr($journal->reff_id, 3, 2);
                    $payloadAmount = 0;
                    $debitAccount = '-';
                    $creditAccount = '-';
                    
                    if (isset($journal->payload)) {
                        if(isset($journal->payload['amount'])) {
                            $payloadAmount = (float) str_replace(',', '.', $journal->payload['amount']);
                        }
                        $debitAccount = $journal->payload['debitAccount'] ?? '-';
                        $creditAccount = $journal->payload['creditAccount'] ?? '-';
                    }
                    $totalAmount += $payloadAmount;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $journal->reff_id }}</td>
                    <td class="text-center">{{ $cabangCode }}</td>
                    <td class="text-center">#{{ $journal->batch_id }}</td>
                    <td>{{ $debitAccount }}</td>
                    <td>{{ $creditAccount }}</td>
                    <td class="text-right">
                        @if($isExcel)
                            {{ $payloadAmount }}
                        @else
                            {{ number_format($payloadAmount, 2, ',', '.') }}
                        @endif
                    </td>
                    <td>{{ $journal->state->value }}</td>
                    <td>{{ $journal->core_reff ?? '-' }}</td>
                    <td>{{ $journal->updated_at ? $journal->updated_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data jurnal yang sesuai dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total Amount:</th>
                <th class="text-right">
                    @if($isExcel)
                        {{ $totalAmount }}
                    @else
                        {{ number_format($totalAmount, 2, ',', '.') }}
                    @endif
                </th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>

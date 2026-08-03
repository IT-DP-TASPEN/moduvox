<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $salary->user->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #000; line-height: 1.3; margin: 0; padding: 0; }
        .wrapper { border: 1px solid #000; padding: 10px; margin: 10px; }
        .header-table { width: 100%; margin-bottom: 15px; }
        .company-name { font-size: 13px; font-weight: bold; }
        .slip-title { font-size: 13px; font-weight: bold; }
        
        .info-table { width: 100%; margin-bottom: 10px; font-size: 10px; }
        .info-table td { padding: 2px 0; vertical-align: top; }
        .info-label { width: 120px; }
        
        .main-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .main-table th { text-align: left; padding: 5px 10px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .main-table td { padding: 3px 10px; }
        .amount { text-align: right; }
        
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .font-bold { font-weight: bold; }
        .pl-15 { padding-left: 15px !important; }
        
        .footer-note { text-align: center; font-size: 9px; padding: 10px 0 5px; }
    </style>
</head>
<body>
    @php
        $monthName = Carbon\Carbon::create(null, $salary->month)->locale('id')->translatedFormat('F');
        
        $earningsList = [
            ['name' => 'Gaji', 'amount' => $salary->basic_salary],
            ['name' => 'Uang Lembur', 'amount' => $salary->overtime_pay],
            ['name' => 'Uang Makan Lembur', 'amount' => $salary->overtime_meal_pay],
            ['name' => 'Tunjangan Pajak', 'amount' => $salary->tax_allowance],
            ['name' => 'Tunjangan Jabatan', 'amount' => $salary->position_allowance],
            ['name' => 'Tunjangan Kinerja Individu', 'amount' => $salary->performance_allowance],
        ];
        
        $deductionsList = [
            ['name' => 'Pajak', 'amount' => $salary->income_tax],
        ];
        
        $nonThpList = [];
        
        $dynamicComponents = json_decode($salary->dynamic_components, true) ?: [];
        foreach ($dynamicComponents as $comp) {
            $cat = strtolower($comp['category'] ?? '');
            if ($cat === 'earning' || $cat === 'pendapatan') {
                $earningsList[] = ['name' => $comp['name'], 'amount' => $comp['amount']];
            } elseif ($cat === 'deduction' || $cat === 'potongan') {
                $deductionsList[] = ['name' => $comp['name'], 'amount' => $comp['amount']];
            } elseif ($cat === 'company_paid' || $cat === 'non-thp' || $cat === 'non_thp') {
                $nonThpList[] = ['name' => $comp['name'], 'amount' => $comp['amount']];
            }
        }
        
        $maxRows = max(count($earningsList), count($deductionsList));
    @endphp

    <div class="wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 250px;">
                    <div style="font-weight:900; color: #1e3a8a; font-size: 20px; letter-spacing: -0.5px;">PT MODUVOX TECH ID</div>
                </td>
                <td>
                    <div class="slip-title">Slip Gaji Bulan {{ $monthName }} {{ $salary->year }}</div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td class="info-label">ID Karyawan</td>
                <td style="width: 250px;">{{ $salary->user->employee_id }}</td>
                <td class="info-label">Status PTKP</td>
                <td>{{ $salary->user->profile->ptkp_status ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Nama</td>
                <td>{{ $salary->user->name }}</td>
                <td class="info-label">Status Kepegawaian</td>
                <td>{{ $salary->user->employment->employment_status ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Jabatan</td>
                <td>{{ $salary->position_name_snapshot ?? ($salary->user->employment->position ?? '-') }}</td>
                <td class="info-label">Divisi / Unit Kerja</td>
                <td>{{ $salary->division_name_snapshot ?? ($salary->user->employment->department ?? '-') }}</td>
            </tr>
            <tr>
                <td class="info-label">Grade / SKG</td>
                <td>{{ $salary->grade_snapshot ?? '-' }} / {{ $salary->skg_snapshot ?? '-' }}</td>
                <td class="info-label">NPWP karyawan</td>
                <td>{{ $salary->user->employment->npwp ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Lokasi kerja</td>
                <td>{{ $salary->user->office->name ?? '-' }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th colspan="2">Pendapatan</th>
                    <th colspan="2">Potongan</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $maxRows; $i++)
                    <tr>
                        @if(isset($earningsList[$i]))
                            <td style="width: 25%;">{{ $earningsList[$i]['name'] }}</td>
                            <td style="width: 25%;" class="amount">{{ number_format($earningsList[$i]['amount'], 0, ',', '.') }}</td>
                        @else
                            <td style="width: 25%;"></td><td style="width: 25%;"></td>
                        @endif
                        
                        @if(isset($deductionsList[$i]))
                            <td style="width: 25%;">{{ $deductionsList[$i]['name'] }}</td>
                            <td style="width: 25%;" class="amount">{{ number_format($deductionsList[$i]['amount'], 0, ',', '.') }}</td>
                        @else
                            <td style="width: 25%;"></td><td style="width: 25%;"></td>
                        @endif
                    </tr>
                @endfor
                
                <tr class="border-top border-bottom font-bold">
                    <td>Total Pendapatan</td><td class="amount">{{ number_format($salary->total_earnings, 0, ',', '.') }}</td>
                    <td>Total Potongan</td><td class="amount">{{ number_format($salary->total_deductions, 0, ',', '.') }}</td>
                </tr>
                
                <tr class="border-bottom font-bold">
                    <td>Jumlah yang diterima karyawan</td><td class="amount">{{ number_format($salary->net_salary, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
                
                <tr>
                    <td colspan="4">Pembayaran</td>
                </tr>
                <tr class="border-bottom">
                    <td class="pl-15">Tunai</td><td class="amount">{{ number_format($salary->net_salary, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
                
                <tr>
                    <td colspan="4" class="font-bold">Pendapatan non THP</td>
                </tr>
                @forelse($nonThpList as $nonThp)
                <tr>
                    <td class="pl-15">{{ $nonThp['name'] }}</td><td class="amount">{{ number_format($nonThp['amount'], 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                @empty
                <tr>
                    <td class="pl-15">-</td><td class="amount">0</td><td colspan="2"></td>
                </tr>
                @endforelse
                
                <tr class="border-top border-bottom font-bold">
                    <td>Total pendapatan non THP</td><td class="amount">{{ number_format($salary->total_non_thp, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
                <tr class="border-bottom">
                    <td>Total seluruh pendapatan karyawan</td><td class="amount">{{ number_format($salary->total_earnings + $salary->total_non_thp, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div class="footer-note">
            Slip gaji ini dibuat otomatis menggunakan aplikasi HRIS Moduvox, sehingga tidak membutuhkan tanda tangan.
        </div>
    </div>
</body>
</html>

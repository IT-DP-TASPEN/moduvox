<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $salary->user->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #000; line-height: 1.3; margin: 0; padding: 0; }
        .wrapper { border: 1px solid #000; padding: 10px; margin: 10px; }
        .header-table { width: 100%; margin-bottom: 15px; }
        .header-logo { height: 40px; width: auto; }
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
        $logoPath = public_path('images/logo.png');
        $logoSrc = '';
        if(file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }
        $monthName = Carbon\Carbon::create(null, $salary->month)->translatedFormat('F');
    @endphp

    <div class="wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 150px;">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" class="header-logo" alt="Logo">
                    @else
                        <div style="font-weight:bold; color: #1e3a8a;">moduvox</div>
                    @endif
                </td>
                <td>
                    <div class="company-name">PT Moduvox</div>
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
                <tr>
                    <td style="width: 25%;">Gaji</td><td style="width: 25%;" class="amount">{{ number_format($salary->basic_salary, 0, ',', '.') }}</td>
                    <td style="width: 25%;">Pajak</td><td style="width: 25%;" class="amount">{{ number_format($salary->income_tax, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Uang Lembur</td><td class="amount">{{ number_format($salary->overtime_pay, 0, ',', '.') }}</td>
                    <td>JHT Karyawan</td><td class="amount">{{ number_format($salary->jht_employee, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Uang Makan Lembur</td><td class="amount">{{ number_format($salary->overtime_meal_pay, 0, ',', '.') }}</td>
                    <td>JP Karyawan</td><td class="amount">{{ number_format($salary->jp_employee, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Pajak</td><td class="amount">{{ number_format($salary->tax_allowance, 0, ',', '.') }}</td>
                    <td>JKN Karyawan</td><td class="amount">{{ number_format($salary->jkn_employee, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Jabatan</td><td class="amount">{{ number_format($salary->position_allowance, 0, ',', '.') }}</td>
                    <td>Potongan Moduvox Save</td><td class="amount">{{ number_format($salary->taspen_save_deduction, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Kinerja Individu</td><td class="amount">{{ number_format($salary->performance_allowance, 0, ',', '.') }}</td>
                    <td></td><td></td>
                </tr>
                
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
                <tr>
                    <td class="pl-15">JKK Perusahaan</td><td class="amount">{{ number_format($salary->jkk_company, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                <tr>
                    <td class="pl-15">JKM Perusahaan</td><td class="amount">{{ number_format($salary->jkm_company, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                <tr>
                    <td class="pl-15">JHT Perusahaan</td><td class="amount">{{ number_format($salary->jht_company, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                <tr>
                    <td class="pl-15">JP Perusahaan</td><td class="amount">{{ number_format($salary->jp_company, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                <tr>
                    <td class="pl-15">JKN Perusahaan</td><td class="amount">{{ number_format($salary->jkn_company, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                <tr>
                    <td class="pl-15">Premi Pensiun</td><td class="amount">{{ number_format($salary->pension_premium, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                <tr>
                    <td class="pl-15">Tunjangan Moduvox Save</td><td class="amount">{{ number_format($salary->taspen_save_allowance, 0, ',', '.') }}</td><td colspan="2"></td>
                </tr>
                
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
            Slip gaji ini dibuat otomatis menggunakan aplikasi ESS gaji.id, sehingga tidak membutuhkan tanda tangan.
        </div>
    </div>
</body>
</html>

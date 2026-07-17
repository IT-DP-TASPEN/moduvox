<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $salary->user->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #004A99; padding-bottom: 20px; }
        .logo { height: 50px; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 20px; color: #004A99; }
        .title p { margin: 2px 0; color: #666; }
        
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box { width: 48%; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box table td { padding: 3px 0; }
        .info-box table td.label { color: #888; width: 40%; }
        .info-box table td.value { font-weight: bold; }

        .salary-table { width: 100%; margin-bottom: 20px; }
        .salary-table th { background: #f8f9fa; text-align: left; padding: 10px; font-weight: bold; color: #004A99; border-bottom: 1px solid #eee; }
        .salary-table td { padding: 8px 10px; border-bottom: 1px solid #f1f1f1; }
        .salary-table .subtotal { font-weight: bold; background: #fafafa; }
        .amount { text-align: right; }

        .thp-box { background: #004A99; color: white; padding: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-top: 30px; }
        .thp-box h2 { margin: 0; font-size: 14px; }
        .thp-box .amount { font-size: 24px; font-weight: bold; }

        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 9px; }
        .signature-box { margin-top: 40px; display: flex; justify-content: flex-end; }
        .signature { text-align: center; width: 200px; }
        .signature .line { border-bottom: 1px solid #333; margin-top: 60px; margin-bottom: 5px; }

        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <img src="https://ui-avatars.com/api/?name=Moduvox&background=1e3a8a&color=fff&rounded=true&bold=true&size=128" class="logo" alt="Moduvox">
        <div class="title">
            <h1>SLIP GAJI KARYAWAN</h1>
            <p>Periode: {{ Carbon\Carbon::create(null, $salary->month)->translatedFormat('F') }} {{ $salary->year }}</p>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <table>
                <tr><td class="label">Nama Lengkap</td><td class="value">{{ $salary->user->name }}</td></tr>
                <tr><td class="label">NIP</td><td class="value">{{ $salary->user->employee_id }}</td></tr>
                <tr><td class="label">Jabatan</td><td class="value">{{ $salary->user->title }}</td></tr>
            </table>
        </div>
        <div class="info-box">
            <table>
                <tr><td class="label">Unit Kerja</td><td class="value">{{ $salary->user->unit_name }}</td></tr>
                <tr><td class="label">Divisi</td><td class="value">{{ $salary->user->division_name }}</td></tr>
                <tr><td class="label">Status</td><td class="value">{{ $salary->user->employment_status ?? 'Pegawai Tetap' }}</td></tr>
            </table>
        </div>
    </div>

    <table class="salary-table">
        <tr>
            <th colspan="2">PENDAPATAN (EARNINGS)</th>
            <th colspan="2">POTONGAN (DEDUCTIONS)</th>
        </tr>
        <tr>
            <td>Gaji Pokok</td><td class="amount">{{ number_format($salary->basic_salary, 0, ',', '.') }}</td>
            <td>Pajak (PPh 21)</td><td class="amount">{{ number_format($salary->income_tax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Lembur</td><td class="amount">{{ number_format($salary->overtime_pay, 0, ',', '.') }}</td>
            <td>BPJS TK (JHT)</td><td class="amount">{{ number_format($salary->jht_employee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Makan Lembur</td><td class="amount">{{ number_format($salary->overtime_meal_pay, 0, ',', '.') }}</td>
            <td>BPJS TK (JP)</td><td class="amount">{{ number_format($salary->jp_employee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunjangan Pajak</td><td class="amount">{{ number_format($salary->tax_allowance, 0, ',', '.') }}</td>
            <td>BPJS Kesehatan</td><td class="amount">{{ number_format($salary->jkn_employee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunjangan Jabatan</td><td class="amount">{{ number_format($salary->position_allowance, 0, ',', '.') }}</td>
            <td>Moduvox Save</td><td class="amount">{{ number_format($salary->taspen_save_deduction, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunjangan Kinerja</td><td class="amount">{{ number_format($salary->performance_allowance, 0, ',', '.') }}</td>
            <td></td><td></td>
        </tr>
        <tr class="subtotal">
            <td>Total Pendapatan</td><td class="amount">{{ number_format($salary->total_earnings, 0, ',', '.') }}</td>
            <td>Total Potongan</td><td class="amount">{{ number_format($salary->total_deductions, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="thp-box">
        <h2>PENGHASILAN BERSIH (TAKE HOME PAY)</h2>
        <div class="amount">Rp {{ number_format($salary->net_salary, 0, ',', '.') }}</div>
    </div>

    <div style="margin-top: 30px;">
        <p style="font-weight: bold; color: #666; font-size: 9px; margin-bottom: 5px;">PENDAPATAN NON-THP (DIBAYARKAN PERUSAHAAN)</p>
        <table style="width: 100%; font-size: 9px; color: #777;">
            <tr>
                <td>JKK: {{ number_format($salary->jkk_company, 0, ',', '.') }}</td>
                <td>JKM: {{ number_format($salary->jkm_company, 0, ',', '.') }}</td>
                <td>JHT: {{ number_format($salary->jht_company, 0, ',', '.') }}</td>
                <td>JP: {{ number_format($salary->jp_company, 0, ',', '.') }}</td>
                <td>JKN: {{ number_format($salary->jkn_company, 0, ',', '.') }}</td>
                <td>Pensiun: {{ number_format($salary->pension_premium, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="signature-box">
        <div class="signature">
            <p>Jakarta, {{ date('d F Y') }}</p>
            <p>Human Resources Department</p>
            <div class="line"></div>
            <p>Moduvox</p>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem HRIS Moduvox dan sah tanpa tanda tangan basah.
    </div>
</body>
</html>

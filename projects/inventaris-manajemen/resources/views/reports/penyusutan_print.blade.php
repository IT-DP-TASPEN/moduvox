<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $is_excel ? 'Laporan Nominatif' : 'Rincian Nominatif Aktiva Tetap dan Inventaris' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-weight: bold;
            font-size: 14px;
            text-align: left;
        }
        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-top: 20px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .group-header {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: left;
        }
        .subtotal {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .grand-total {
            background-color: #d9d9d9;
            font-weight: bold;
        }
        .no-print { display: block; margin-bottom: 20px; }
        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

@if(!$is_excel)
<div class="no-print">
    <button onclick="window.print()" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px;">Cetak Dokumen</button>
    <button onclick="window.close()" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; cursor: pointer; border-radius: 4px; margin-left: 10px;">Tutup</button>
</div>
@endif

@if($is_excel)
    <table>
        <tr>
            <td colspan="11" style="font-weight: bold; font-size: 14px;">PT Moduvox Tech ID</td>
        </tr>
        <tr>
            <td colspan="11" style="font-weight: bold;">{{ $kantor ? $kantor->nama : 'KONSOLIDASI SELURUH CABANG' }}</td>
        </tr>
        <tr>
            <td colspan="11" style="border: none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align: center; font-weight: bold; font-size: 12px;">LAPORAN PENYUSUTAN AKTIVA TETAP DAN INVENTARIS</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align: center;">Periode {{ $dateFormatted }}</td>
        </tr>
    </table>
@else
    <table style="width: 100%; border: none; margin-bottom: 10px;">
        <tr style="border: none;">
            <td style="border: none; width: 120px; vertical-align: middle; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #f59e0b; border: 2px solid #f59e0b; padding: 10px; border-radius: 8px; display: inline-block;">
                    MODUVOX
                </div>
            </td>
            <td style="border: none; vertical-align: middle;" class="company-name">
                PT Moduvox Tech ID<br>
                {{ $kantor ? $kantor->nama : 'KONSOLIDASI SELURUH CABANG' }}
            </td>
        </tr>
    </table>
    <hr style="border-top: 2px solid #000;">

    <div class="report-title">
        LAPORAN PENYUSUTAN AKTIVA TETAP DAN INVENTARIS<br>
        <span style="font-weight: normal; text-transform: none;">Periode {{ $dateFormatted }}</span>
    </div>
@endif

@foreach($groupedData as $kantorName => $golongans)
    @if($is_excel)
        <table>
            <tr><td colspan="11" style="border:none;">&nbsp;</td></tr>
            <tr>
                <td colspan="11" style="text-align: center; font-weight: bold; font-size: 12px;">
                    LAPORAN PENYUSUTAN KANTOR CABANG {{ strtoupper($kantorName) }}
                </td>
            </tr>
        </table>
    @else
        <div style="text-align: center; font-weight: bold; margin-top: 30px; margin-bottom: 10px; font-size: 12px;">
            LAPORAN PENYUSUTAN KANTOR CABANG {{ strtoupper($kantorName) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th>Nomor Rekening</th>
                <th>Nama Aktiva</th>
                <th style="width: 70px;">Tgl Perolehan</th>
                <th style="width: 40px;">Usia Pakai (Bln)</th>
                <th style="width: 70px;">Tgl Habis Buku</th>
                <th>Nilai Perolehan</th>
                <th>Nilai Buku Bln Lalu</th>
                <th>Nilai Penyusutan</th>
                <th>Akumulasi Penyusutan</th>
                <th>Nilai Buku Bln Ini</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalPerolehan = 0;
                $grandTotalBukuLalu = 0;
                $grandTotalPenyusutan = 0;
                $grandTotalAkumulasi = 0;
                $grandTotalBukuSekarang = 0;
                $no = 1;
            @endphp

            @forelse($golongans as $golonganName => $items)
                <tr class="group-header">
                    <td colspan="11">[{{ $golonganName }}]</td>
                </tr>
                
                @php
                    $subTotalPerolehan = 0;
                    $subTotalBukuLalu = 0;
                    $subTotalPenyusutan = 0;
                    $subTotalAkumulasi = 0;
                    $subTotalBukuSekarang = 0;
                @endphp

                @foreach($items as $item)
                    @php
                        // Hitung umur aktual per tanggal report (dibulatkan ke bawah)
                        $umurPakai = intval($item->tgl_perolehan->diffInMonths($periodeDate));

                        // Ambil beban yang terjadi SETELAH periode laporan
                        $penyusutanSetelahPeriode = $item->beban_setelah_periode ?? 0;
                        $bebanBulanIni = $item->beban_bulan_ini_val ?? 0;
                        
                        // Akumulasi s.d bulan report (Master Akumulasi - Beban Setelah Periode)
                        $akumulasiReport = $item->akumulasi_penyusutan - $penyusutanSetelahPeriode;
                        
                        // Nilai Buku bulan report
                        $bukuSekarang = $item->harga_perolehan - $akumulasiReport;
                        
                        // Nilai Buku bulan lalu (sebelum dikurangi beban bulan ini)
                        $bukuBulanLalu = $bukuSekarang + $bebanBulanIni;

                        // Perhitungan Subtotal
                        $subTotalPerolehan += $item->harga_perolehan;
                        $subTotalBukuLalu += $bukuBulanLalu;
                        $subTotalPenyusutan += $bebanBulanIni;
                        $subTotalAkumulasi += $akumulasiReport;
                        $subTotalBukuSekarang += $bukuSekarang;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td style="mso-number-format:'\@';">{{ $item->rekening }}</td>
                        <td>{{ $item->nama_aset }}</td>
                        <td class="text-center">{{ $item->tgl_perolehan->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $umurPakai }}</td>
                        <td class="text-center">
                            @if($item->golongan->umur_standar > 0)
                                {{ $item->tgl_perolehan->copy()->addMonths($item->golongan->umur_standar)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $item->harga_perolehan : App\Helpers\FormatHelper::rupiah($item->harga_perolehan) }}</td>
                        <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $bukuBulanLalu : App\Helpers\FormatHelper::rupiah($bukuBulanLalu) }}</td>
                        <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $bebanBulanIni : App\Helpers\FormatHelper::rupiah($bebanBulanIni) }}</td>
                        <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $akumulasiReport : App\Helpers\FormatHelper::rupiah($akumulasiReport) }}</td>
                        <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $bukuSekarang : App\Helpers\FormatHelper::rupiah($bukuSekarang) }}</td>
                    </tr>
                @endforeach

                <tr class="subtotal">
                    <td colspan="6" class="text-center">Subtotal {{ $golonganName }}</td>
                    <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $subTotalPerolehan : App\Helpers\FormatHelper::rupiah($subTotalPerolehan) }}</td>
                    <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $subTotalBukuLalu : App\Helpers\FormatHelper::rupiah($subTotalBukuLalu) }}</td>
                    <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $subTotalPenyusutan : App\Helpers\FormatHelper::rupiah($subTotalPenyusutan) }}</td>
                    <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $subTotalAkumulasi : App\Helpers\FormatHelper::rupiah($subTotalAkumulasi) }}</td>
                    <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $subTotalBukuSekarang : App\Helpers\FormatHelper::rupiah($subTotalBukuSekarang) }}</td>
                </tr>

                @php
                    $grandTotalPerolehan += $subTotalPerolehan;
                    $grandTotalBukuLalu += $subTotalBukuLalu;
                    $grandTotalPenyusutan += $subTotalPenyusutan;
                    $grandTotalAkumulasi += $subTotalAkumulasi;
                    $grandTotalBukuSekarang += $subTotalBukuSekarang;
                @endphp
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px;">Tidak ada data aset pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($golongans->count() > 0)
        <tfoot>
            <tr class="grand-total">
                <td colspan="6" class="text-center">Grand Total {{ $kantorName }}</td>
                <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $grandTotalPerolehan : App\Helpers\FormatHelper::rupiah($grandTotalPerolehan) }}</td>
                <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $grandTotalBukuLalu : App\Helpers\FormatHelper::rupiah($grandTotalBukuLalu) }}</td>
                <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $grandTotalPenyusutan : App\Helpers\FormatHelper::rupiah($grandTotalPenyusutan) }}</td>
                <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $grandTotalAkumulasi : App\Helpers\FormatHelper::rupiah($grandTotalAkumulasi) }}</td>
                <td class="text-right" {!! $is_excel ? 'style="mso-number-format:\'\\#\\,\\#\\#0\';"' : '' !!}>{{ $is_excel ? $grandTotalBukuSekarang : App\Helpers\FormatHelper::rupiah($grandTotalBukuSekarang) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
@endforeach

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr style="border: none;">
            <td style="border: none; text-align: center; width: 33%;">
                Dibuat,<br><br><br><br>
                (___________________)
            </td>
            <td style="border: none; text-align: center; width: 33%;">
                Diperiksa,<br><br><br><br>
                (___________________)
            </td>
            <td style="border: none; text-align: center; width: 33%;">
                Disahkan,<br><br><br><br>
                (___________________)
            </td>
        </tr>
    </table>
</body>
</html>

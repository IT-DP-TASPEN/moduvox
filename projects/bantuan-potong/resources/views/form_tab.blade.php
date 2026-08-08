<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pembukaan Rekening</title>
    <style>
        @page {
            size: 14.5cm 20.5cm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .page-container {
            position: relative;
            width: 100%;
            height: 20.5cm;
            font-family: sans-serif;
        }

        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .content {
            position: relative;
            padding: 50px 30px 0 30px;
            /* atas kanan bawah kiri */
            color: #000;
            z-index: 1;
        }

        .field {
            margin-bottom: 4px;
            font-size: 8pt;
            line-height: 1.2;
            overflow: hidden;
        }

        .label-1 {
            float: left;
            width: none;
            font-weight: bold;
        }

        .label {
            float: left;
            width: 4cm;
        }

        .value {
            display: block;
            margin-left: 4.2cm;
            word-wrap: break-word;
            max-width: 10cm;
        }

        .field::after {
            content: "";
            display: block;
            clear: both;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</head>

<body>
    <div class="page-container">
        <!-- Background image -->
        <img src="{{ asset('bgimg.png') }}" alt="Background" class="bg-image" />

        <!-- Content -->
        <div class="content">
            <div class="field" style="margin-bottom: 160px; font-size: 10pt; font-weight: bold;">
                <span class="label-1">TU No</span>

                <span class="value-1">. {{ $tab->saving_booknumber ?? '...' }}</span>
            </div>
            <div class="field">
                <span class="label">CABANG</span>
                <span class="value">: 01 : KP.Operasional</span>
            </div>
            <div class="field">
                <span class="label">NOMOR REKENING</span>
                <span class="value">: {{ $tab->rek_tabungan ?? '...' }} - Tabungan Pensiun</span>
            </div>
            <div class="field">
                <span class="label">NAMA NASABAH</span>
                <span class="value">: {{ $tab->customer_name ?? '...' }}</span>
            </div>
            <div class="field">
                <span class="label">ALAMAT NASABAH</span>
                <span class="value">: {{ $tab->address ?? '...' }}</span>
            </div>
            <div class="field">
                <span class="label">NOMOR IDENTITAS</span>
                <span class="value">: {{ $tab->national_id_number ?? '...' }}</span>
            </div>
        </div>
    </div>
</body>

</html>
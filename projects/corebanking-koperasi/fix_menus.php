<?php
$mapping = [
    'CIF' => '1. CIF',
    'Saving' => '2. Simpanan',
    'Deposit' => '3. Simpanan Berjangka',
    'Loan' => '4. Pinjaman',
];

foreach ($mapping as $old => $new) {
    DB::table('menus')->where('category', $old)->update(['category' => $new]);
}

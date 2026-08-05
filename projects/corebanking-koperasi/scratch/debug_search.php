<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$search = 'rama';
$query = App\Models\SavingAccount::with(['cif', 'product', 'branch']);
$query->where(function($q) use ($search) {
    $q->where('account_no', 'like', '%' . $search . '%')
      ->orWhereHas('cif', function($qc) use ($search) {
          $qc->where('name', 'like', '%' . $search . '%')
            ->orWhere('cif_no', 'like', '%' . $search . '%');
      });
});

$items = $query->latest('id')->paginate(10);
echo "Total Count: " . $items->total() . "\n";
echo "Count on Current Page: " . $items->count() . "\n";
foreach($items as $r) {
    echo "ID: {$r->id} | Account: {$r->account_no} | CIF: {$r->cif->name} | Branch: {$r->branch->name}\n";
}

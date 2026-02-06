<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Medication;
use App\Models\Inventory;
use Carbon\Carbon;

echo "Populating inventory...\n";

$meds = Medication::all();
foreach ($meds as $med) {
    if (Inventory::where('medication_id', $med->id)->exists()) {
        continue;
    }

    Inventory::create([
        'tenant_id' => $med->tenant_id,
        'medication_id' => $med->id,
        'quantity' => rand(5, 100),
        'reorder_level' => 20,
        'batch_number' => 'BATCH-' . strtoupper(Str::random(6)),
        'expiry_date' => Carbon::now()->addMonths(rand(6, 24))->toDateString(),
    ]);
}

echo "Inventory populated successfully.\n";

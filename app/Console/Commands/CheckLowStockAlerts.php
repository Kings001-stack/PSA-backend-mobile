<?php

namespace App\Console\Commands;

use App\Services\LowStockAlertService;
use Illuminate\Console\Command;

class CheckLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory for low stock levels and create alerts';

    /**
     * Execute the console command.
     */
    public function handle(LowStockAlertService $alertService): int
    {
        $this->info('Checking inventory for low stock levels...');

        $alerts = $alertService->checkLowStockLevels();

        $this->info('Alert check completed.');
        $this->info('New alerts created: ' . count($alerts));

        if (count($alerts) > 0) {
            $this->table(
                ['ID', 'Medication', 'Type', 'Quantity'],
                collect($alerts)->map(fn($alert) => [
                    $alert->id,
                    $alert->medication->name ?? 'Unknown',
                    $alert->alert_type,
                    $alert->current_quantity,
                ])
            );
        }

        return Command::SUCCESS;
    }
}

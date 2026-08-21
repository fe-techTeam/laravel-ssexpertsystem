<?php

namespace FeTech\SSExpert\Commands;

use Illuminate\Console\Command;
use FeTech\SSExpert\Facades\SSExpert;

class CheckBalanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssexpert:balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check available SMS credits and balance on SSExpertSystem account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Checking SSExpertSystem account balance...");

        try {
            $records = SSExpert::balance()->list();

            if ($records->isEmpty()) {
                $this->warn("No balance records returned from gateway.");

                return self::SUCCESS;
            }

            $rows = [];
            foreach ($records as $record) {
                $rows[] = [
                    $record->pluginType,
                    number_format($record->credits, 2),
                    $record->rawCredits,
                    $record->currencySymbol ?: 'N/A',
                ];
            }

            $this->table(['Plugin / Product Type', 'Available Credits', 'Raw Value', 'Currency'], $rows);

            $primary = $records->first();
            $this->info("✔ Total Available SMS Credits: " . number_format($primary->credits, 2));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✖ Failed to fetch balance: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FertilizerTransaction;

class FixMissingCoordinatesCommand extends Command
{
    protected $signature = 'fix:coordinates {--dataset=}';
    protected $description = 'Fix missing coordinates for transactions';

    public function handle()
    {
        $query = FertilizerTransaction::query()
            ->where(function($q) {
                $q->whereNull('latitude')
                  ->orWhereNull('longitude');
            });

        if ($this->option('dataset')) {
            $query->where('dataset_id', $this->option('dataset'));
        }

        $transactions = $query->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions found without coordinates.');
            return 0;
        }

        $this->info("Found {$transactions->count()} transactions without coordinates.");
        $this->info('Assigning coordinates...');

        $defaultCoordinates = [
            [-7.8167, 112.0167],
            [-7.8200, 112.0200],
            [-7.8100, 112.0100],
            [-7.8250, 112.0250],
            [-7.8150, 112.0150],
            [-7.8300, 112.0300],
        ];

        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        foreach ($transactions as $index => $transaction) {
            $coordIndex = $index % count($defaultCoordinates);
            $baseCoord = $defaultCoordinates[$coordIndex];

            $latitude = $baseCoord[0] + (mt_rand(-200, 200) / 10000);
            $longitude = $baseCoord[1] + (mt_rand(-200, 200) / 10000);

            $transaction->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => $transaction->address ?? 'DS. BLABAK KEC. KANDAT - Kandat - KAB. KEDIRI',
                'village' => $transaction->village ?? 'BLABAK',
                'district' => $transaction->district ?? 'KANDAT',
                'regency' => $transaction->regency ?? 'KEDIRI',
                'province' => $transaction->province ?? 'JAWA TIMUR',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Coordinates fixed successfully!');

        return 0;
    }
}

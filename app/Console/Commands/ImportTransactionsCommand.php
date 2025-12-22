<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dataset;
use App\Http\Controllers\DatasetController;

class ImportTransactionsCommand extends Command
{
    protected $signature = 'import:transactions {file} {--dataset=} {--year=} {--month=}';
    protected $description = 'Import fertilizer transactions from Excel file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error('File not found: ' . $filePath);
            return 1;
        }

        $this->info('Starting import...');

        // Create dataset
        $dataset = Dataset::create([
            'user_id' => 1, // Default user
            'name' => 'CLI Import ' . now()->format('Y-m-d H:i:s'),
            'slug' => 'cli-import-' . time(),
            'type' => 'fertilizer',
            'import_status' => 'processing',
            'import_file_path' => $filePath,
        ]);

        $controller = new DatasetController();
        $year = $this->option('year') ?? date('Y');
        $month = $this->option('month') ?? date('m');

        try {
            $controller->processImport($dataset, $filePath, $year, $month);
            $this->info('Import completed successfully!');
            $this->info('Dataset ID: ' . $dataset->id);
            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}

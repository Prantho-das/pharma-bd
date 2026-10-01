<?php

namespace App\Console\Commands;

use App\Services\MedicineDirectoryService;
use Illuminate\Console\Command;

class SyncMedexMedicinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medex:sync-all 
                            {--alpha=all : Specific alphabet (a-z) or all}
                            {--start-page=1 : Start page number}
                            {--max-pages=10 : Max pages per letter (0 for unlimited)}
                            {--delay=1 : Delay in seconds between requests to avoid Cloudflare/IP block}
                            {--source=both : Data source: medex, local, or both}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and import medicines from Medex & National Drug Directory into local catalog with anti-ban throttling';

    /**
     * Execute the console command.
     */
    public function handle(MedicineDirectoryService $service): int
    {
        $alpha = strtolower((string) $this->option('alpha'));
        $startPage = (int) $this->option('start-page');
        $maxPages = (int) $this->option('max-pages');
        $delay = (int) $this->option('delay');
        $source = (string) $this->option('source');

        $this->info("==========================================================");
        $this->info("💊 MEDICINE DIRECTORY MASTER SYNC ENGINE (Anti-Bot Safe)");
        $this->info("==========================================================");
        $this->line("• Source: <comment>{$source}</comment>");
        $this->line("• Alphabet: <comment>{$alpha}</comment>");
        $this->line("• Delay: <comment>{$delay}s between pages</comment>");
        $this->line("----------------------------------------------------------");

        $totalFetched = 0;
        $totalImported = 0;

        // 1. Sync Local Master Repository if requested
        if (in_array($source, ['local', 'both'])) {
            $this->info("⚡ Importing Verified National Drug Repository...");
            $localList = $service->getNationalMedicineDataset();
            $bar = $this->output->createProgressBar(count($localList));
            $bar->start();

            foreach ($localList as $item) {
                $service->importMedicine($item);
                $totalImported++;
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info("✓ Local master catalog synchronized ({$totalImported} medicines).");
        }

        // 2. Fetch Live Medex if requested
        if (in_array($source, ['medex', 'both'])) {
            $letters = ($alpha === 'all') ? range('a', 'z') : [$alpha];

            foreach ($letters as $letter) {
                $this->info("\n🌐 Crawling Medex for letter '{$letter}'...");
                $page = $startPage;
                $pagesProcessed = 0;

                while (true) {
                    if ($maxPages > 0 && $pagesProcessed >= $maxPages) {
                        break;
                    }

                    $this->line("  ↳ Fetching page {$page} for letter '{$letter}'...");
                    $items = $service->fetchMedexBrandsPage($letter, $page);

                    if (empty($items)) {
                        $this->warn("  - No more medicines found or rate-limited on page {$page}. Moving to next letter.");
                        break;
                    }

                    $totalFetched += count($items);
                    foreach ($items as $item) {
                        $service->importMedicine($item);
                        $totalImported++;
                    }

                    $this->info("    ✓ Imported " . count($items) . " brands from page {$page}. (Total: {$totalImported})");
                    $page++;
                    $pagesProcessed++;

                    if ($delay > 0) {
                        sleep($delay);
                    }
                }
            }
        }

        $this->newLine();
        $this->info("==========================================================");
        $this->info("🎉 SYNC COMPLETED SUCCESSFULLY!");
        $this->info("• Total Fetched: {$totalFetched}");
        $this->info("• Total Saved to Database: {$totalImported}");
        $this->info("==========================================================");

        return Command::SUCCESS;
    }
}

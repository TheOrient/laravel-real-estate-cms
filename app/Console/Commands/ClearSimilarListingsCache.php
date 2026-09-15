<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSimilarListingsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-similar-listings
                           {--category= : Clear cache for specific category ID}
                           {--all : Clear all similar listings cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear similar listings cache for better performance management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cleaner = new \App\Lib\ClearSimilarListingsCache();

        if ($this->option('all')) {
            $this->info('Clearing all similar listings cache...');
            $result = $cleaner->handle(null, true);
        } elseif ($categoryId = $this->option('category')) {
            $this->info("Clearing similar listings cache for category ID: {$categoryId}");
            $result = $cleaner->handle($categoryId);
        } else {
            $this->info('Please specify either --all or --category=ID');
            return 1;
        }

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            return 0;
        } else {
            $this->error('❌ ' . $result['message']);
            return 1;
        }
    }
}

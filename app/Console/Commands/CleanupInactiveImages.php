<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ListingImage;
use Carbon\Carbon;

class CleanupInactiveImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup-inactive {--days=1 : Number of days after which inactive images will be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup inactive listing images that were uploaded but never activated (user left without saving)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("🔍 Searching for inactive images older than {$days} day(s)...");

        $cleaner = new \App\Lib\CleanupInactiveImages();
        $result = $cleaner->handle($days);

        if ($result['total_found'] === 0) {
            $this->info('✅ No inactive images found to cleanup.');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Found {$result['total_found']} inactive image(s).");
        $this->info("✅ Cleanup completed:");
        $this->info("   - Successfully deleted: {$result['deleted_count']} image(s)");

        if ($result['failed_count'] > 0) {
            $this->error("   - Failed to delete: {$result['failed_count']} image(s)");
        }

        return Command::SUCCESS;
    }
}

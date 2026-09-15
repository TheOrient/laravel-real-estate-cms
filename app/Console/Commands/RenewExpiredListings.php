<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\ListingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RenewExpiredListings extends Command
{
    protected $listingService;

    public function __construct(ListingService $listingService)
    {
        parent::__construct();
        $this->listingService = $listingService;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:renew-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extend the publication period of active office portfolio listings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting expired listings renewal process...');

        $renewer = new \App\Lib\RenewExpiredListings($this->listingService);
        $result = $renewer->handle();

        $this->info("Found {$result['processed']} expired listings to process.");

        foreach ($result['messages'] as $message) {
            if (str_starts_with($message, '✓')) {
                $this->line($message);
            } elseif (str_starts_with($message, '⊗')) {
                $this->line($message);
            } else {
                $this->error($message);
            }
        }

        $this->newLine();
        $this->info("Renewal process completed!");
        $this->info("Total listings processed: {$result['processed']}");
        $this->info("Listings renewed: {$result['renewed']}");
        $this->info("Listings deactivated: {$result['deactivated']}");
        if ($result['errors'] > 0) {
            $this->error("Errors encountered: {$result['errors']}");
        }

        return Command::SUCCESS;
    }
}

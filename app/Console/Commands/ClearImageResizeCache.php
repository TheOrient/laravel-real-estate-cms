<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearImageResizeCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-image-resize-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears image resize cache files older than 30 seconds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cleaner = new \App\Lib\ClearImageResizeCache();
        $result = $cleaner->handle();

        $this->info("Cache cleanup completed: {$result['deleted_files']} files and {$result['deleted_dirs']} directories removed");
    }
}

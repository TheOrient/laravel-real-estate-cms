<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule expired listings renewal to run hourly
Schedule::command('listings:renew-expired')->hourly();

// Schedule cleanup of inactive images (uploaded but never activated) to run daily
Schedule::command('images:cleanup-inactive --days=1')->daily();

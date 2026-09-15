<?php

namespace App\Console\Commands;

use App\Services\ListingImport\ListingImporter;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportListing extends Command
{
    protected $signature = 'listings:import {file} {--agent=} {--publish} {--dry-run}';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(__('listing_import.command_description'));
    }

    public function handle(ListingImporter $importer): int
    {
        try {
            $result = $importer->import($this->argument('file'), $this->option('agent'),
                (bool) $this->option('publish'), (bool) $this->option('dry-run'));
            $listing = $result['listing'];
            $this->line(json_encode([
                'action' => $result['action'],
                'message' => __('listing_import.'.$result['action']),
                'reference' => $result['reference'],
                'listing_id' => $listing?->id,
                'photo_count' => $result['photo_count'],
                'is_public' => $listing ? $listing->is_active && $listing->is_approved : false,
                'admin_url' => $listing ? route('admin.listings.index', ['search' => $listing->id]) : null,
                'agent_edit_url' => $listing ? route('user.listings.edit', ['listing' => $listing->id]) : null,
                'public_url' => $listing && $listing->is_active && $listing->is_approved
                    ? route('listings.show', ['slug' => $listing->slug]) : null,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->error($field.': '.implode(' ', $messages));
            }
        } catch (QueryException $exception) {
            report($exception);
            $this->error(__('listing_import.database_failed'));
        } catch (Throwable $exception) {
            report($exception);
            $this->error(__('listing_import.import_failed'));
        }

        return self::FAILURE;
    }
}

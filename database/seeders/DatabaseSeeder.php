<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // This installer is not an upgrade command. Refuse before changing any data.
        if (User::withTrashed()->exists() || Listing::withTrashed()->exists()) {
            throw new RuntimeException(__('installation.existing_database'));
        }

        $this->command->info('Starting database seeding...');

        // Create users
        $this->call([
            UserSeeder::class,
        ]);

        // Configured frontend languages
        $this->command->info('Creating languages from config/app.php...');
        $this->call([
            LanguageSeeder::class,
        ]);

        // Create categories
        $this->command->info('Creating categories...');
        $this->call([
            CategorySeeder::class,
        ]);

        // Create attributes and their values
        $this->command->info('Creating attributes...');
        $this->call([
            AttributeSeeder::class,
        ]);

        // Create pages
        $this->command->info('Creating pages...');
        $this->call([
            PageSeeder::class,
        ]);

        // Create settings
        $this->command->info('Creating settings...');
        $this->call([
            SettingsSeeder::class,
        ]);

        // Seed clearly labelled, fictional demo listings and editorial content.
        // This is an installer for an empty database, never an upgrade command.
        $this->command->info('Creating fictional demo content...');
        $this->call(SampleContentSeeder::class, false, ['preserveExistingBlogs' => true]);

        $this->command->info('Database seeding completed successfully!');
    }
}

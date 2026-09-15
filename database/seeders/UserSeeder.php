<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('user_role', 'admin')->exists()) {
            $this->command->info('Admin user already exists');

            return;
        }

        $email = trim((string) config('admin.seed.email'));
        $password = (string) config('admin.seed.password');

        if (app()->isProduction() && ($password === '' || strlen($password) < 12)) {
            throw new RuntimeException('Set a unique ADMIN_PASSWORD of at least 12 characters before seeding production.');
        }

        // Convenient only for local development. Production is guarded above.
        $password = $password !== '' ? $password : 'local-development-only';

        User::create([
            'first_name' => (string) config('admin.seed.first_name'),
            'last_name' => (string) config('admin.seed.last_name'),
            'email' => $email,
            'password' => Hash::make($password),
            'avatar' => 'images/demo-avatar.svg',
            'phone' => null,
            'enable_whatsapp' => 'no',
            'user_role' => 'admin',
            'email_verified_at' => Carbon::now(),
        ]);

        $this->command->info("Admin user created: {$email}");
    }
}

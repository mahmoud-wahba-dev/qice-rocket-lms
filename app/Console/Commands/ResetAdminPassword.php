<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\User;
use Illuminate\Console\Command;

class ResetAdminPassword extends Command
{
    protected $signature = 'qiec:reset-admin-password
                            {--email= : Admin email (defaults to ADMIN_EMAIL env or admin@demo.com)}
                            {--password= : New password (defaults to ADMIN_PASSWORD env)}';

    protected $description = 'Reset an admin user password using credentials from options or .env';

    public function handle(): int
    {
        $email = $this->option('email') ?: env('ADMIN_EMAIL', 'admin@demo.com');
        $password = $this->option('password') ?: env('ADMIN_PASSWORD');

        if (empty($password)) {
            $this->error('Password is required. Set ADMIN_PASSWORD in .env or pass --password=');

            return self::FAILURE;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email: ' . $email);

            return self::FAILURE;
        }

        $user = User::query()
            ->where('email', $email)
            ->where('role_name', Role::$admin)
            ->first();

        if (!$user) {
            $user = User::query()->where('email', $email)->first();
        }

        if (!$user) {
            $this->error('No user found with email: ' . $email);

            return self::FAILURE;
        }

        $user->password = User::generatePassword($password);
        $user->save();

        $this->info('Password updated for: ' . $user->email . ' (id: ' . $user->id . ')');

        return self::SUCCESS;
    }
}

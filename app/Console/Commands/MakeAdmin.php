<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email}';
    protected $description = 'Assign admin role to a user by email';

    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error("User with email '{$this->argument('email')}' not found.");
            return Command::FAILURE;
        }

        $user->assignRole('admin');

        $this->info("User '{$user->name}' ({$user->email}) is now an admin!");

        return Command::SUCCESS;
    }
}

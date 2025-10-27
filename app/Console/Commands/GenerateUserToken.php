<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateUserToken extends Command
{
    protected $signature = 'user:token {email?}';
    protected $description = 'Generate an API token for a user (default: admin)';

    public function handle()
    {
        $email = $this->argument('email');

        $user = $email
            ? User::where('email', $email)->first()
            : User::where('role', 'admin')->first();

        if (!$user) {
            $this->error('No user found with that criteria.');
            return;
        }

        $token = $user->createToken('manual-token')->plainTextToken;

        $this->info("✅ Token generated for {$user->username}");
        $this->line("Token: {$token}");
    }
}

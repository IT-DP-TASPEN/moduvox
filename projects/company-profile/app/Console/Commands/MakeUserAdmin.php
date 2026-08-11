<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email : User email to promote}';

    protected $description = 'Promote a user to admin (sets users.is_admin = 1)';

    public function handle()
    {
        $email = (string) $this->argument('email');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found: {$email}");
            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("Admin enabled for: {$email}");

        return self::SUCCESS;
    }
}


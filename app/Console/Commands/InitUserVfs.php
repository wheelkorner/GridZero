<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class InitUserVfs extends Command
{
    protected $signature = 'vfs:init {--force : Overwrite existing VFS if any}';
    protected $description = 'Ensures all players have a default VFS structure in their stats.';

    public function handle(): void
    {
        $this->info("Starting VFS initialization for players...");

        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {
            $stats = $user->stats ?? [];

            if (!isset($stats['vfs']) || $this->option('force')) {
                $stats['vfs'] = $user->generateVfs();
                $user->update(['stats' => $stats]);
                $updated++;
            }
        }

        $this->info("VFS initialization complete. {$updated} users updated.");
    }
}

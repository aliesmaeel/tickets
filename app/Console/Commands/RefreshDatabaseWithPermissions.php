<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshDatabaseWithPermissions extends Command
{
    protected $signature = 'app:refresh-db-permissions';
    protected $description = 'Refresh DB with seeding and sync permissions';

    public function handle()
    {
        $this->info('Refreshing database...');
        $this->call('migrate:fresh', ['--seed' => true]);

        $this->info('Syncing permissions...');
        $this->call('permissions:sync');

        $this->info('✅ Done!');

    }
}

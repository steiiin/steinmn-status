<?php

namespace App\Console\Commands;

use App\Services\StatusService;
use Illuminate\Console\Command;

class UpdateStatus extends Command
{

    protected $signature = 'app:update-status';

    /**
     * Execute the console command.
     */
    public function handle(StatusService $ss)
    {

        $ss->run();
        $this->info('Heartbeat processed.');

    }
}

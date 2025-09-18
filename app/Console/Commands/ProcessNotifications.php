<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class ProcessNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process {--retry : Retry failed notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending notifications and optionally retry failed ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationService = new NotificationService();
        
        if ($this->option('retry')) {
            $this->info('Retrying failed notifications...');
            $retried = $notificationService->retryFailed();
            $this->info("Retried {$retried} failed notifications.");
        }
        
        $this->info('Processing pending notifications...');
        $processed = $notificationService->processPending();
        $this->info("Processed {$processed} pending notifications.");
        
        return 0;
    }
}

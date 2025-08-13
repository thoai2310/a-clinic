<?php

namespace App\Jobs;

use App\Services\BotMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BotSendMessageJob implements ShouldQueue
{
    use Queueable;

    public $dataSend;

    /**
     * Create a new job instance.
     */
    public function __construct(array $dataSend)
    {
        $this->dataSend = $dataSend;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $botMessageService = app(BotMessageService::class);
            foreach ($this->dataSend as $item) {
                $botMessageService->sendMessage(+$item['app_id'], $item['message']);
            }
        } catch (\Exception $exception) {
        }
    }
}

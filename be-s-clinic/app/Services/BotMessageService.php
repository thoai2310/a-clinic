<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BotMessageService
{
    public function sendMessage($chatID, $message)
    {
        try {
            $url = 'https://api.telegram.org/bot8364197852:AAHoK9yXoAHPmy5f_tNyB_x3_4XwcIbm5eI/sendMessage';
            $data = [
                'chat_id' => $chatID,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];
            $result = Http::post($url, $data);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}

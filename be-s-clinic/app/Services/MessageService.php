<?php

namespace App\Services;

use App\Jobs\BotSendMessageJob;
use App\Models\Customer;
use App\Models\CustomerMessage;
use App\Models\FormCustomer;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessageService
{
    public function filters($params)
    {
        $messages = Message::query();
        $pageSize = $params['pageSize'] ?? 10;

        return $messages->paginate($pageSize);
    }

    public function create($data)
    {
        try {
            DB::beginTransaction();

            $message = new Message();
            $message->type = $data['type'];
            $title = $data['title'];
            $content = $data['content'];
            $forms = $data['forms'];

            $message->title = $title;
            $message->content = $content;
            $message->forms = $forms;
            $message->tags = json_encode($data['tags']);

            $message->save();

            $sendMessage = [];

            if ($message->id) {
                $customerIds = $data['customerIds'];

                $customers = Customer::query()
                    ->whereIn('id', $customerIds)
                    ->get();

                foreach ($customers as $customer) {
                    $messageContent = "Title: " . $title . "\nContent: " . $content;

                    if (!empty($forms)) {
                        $customerForm = new FormCustomer();
                        $customerForm->code = Str::random(24);
                        $customerForm->form_id = $forms;
                        $customerForm->customer_id = $customer->id;
                        $customerForm->save();

                        $messageContent .= "\nForm follow link: " . route(
                                'survey.show',
                                [
                                    'survey_code' => $customerForm->code,
                                ]
                            );
                    }

                    $customerMessage = new CustomerMessage();
                    $customerMessage->customer_id = $customer->id;
                    $customerMessage->message_id = $message->id;
                    $customerMessage->message = $messageContent;
                    $customerMessage->save();

                    if (!empty($customer->app_id)) {
                        $sendMessage[] = [
                            'app_id' => $customer->app_id,
                            'message' => $messageContent,
                        ];

                        $customerMessage->last_sent_at = date('Y-m-d H:i:s');
                        $customerMessage->save();
                    }
                }

                DB::commit();

                if (!empty($sendMessage)) {
                    dispatch(new BotSendMessageJob($sendMessage));
                }

                return $message;
            }

            DB::rollBack();
            return false;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
}

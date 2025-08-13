<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAutoTagJob;
use App\Models\Customer;
use App\Models\FormCustomer;
use App\Models\AnswerCustomer;
use App\Models\FormQuestion;
use App\Models\QuestionOption;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebHookController extends Controller
{
    private $workflowService;
    private $botToken;
    private $apiUrl;

    public function __construct(
        WorkflowService $workflowService
    ) {
        $this->workflowService = $workflowService;
        $this->botToken = env('TELE_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function receive(Request $request)
    {
        try {
            Log::info('receive' . json_encode($request->all()));
            $update = $request->all();


            if (isset($update['update_id'])) {
                $updateId = $update['update_id'];
                $cacheKey = "webhook_processed:{$updateId}";

                if (Cache::has($cacheKey)) {
                    Log::warning('Duplicate webhook request detected', ['update_id' => $updateId]);
                    return response()->json(['status' => 'duplicate_skipped']);
                }

                Cache::put($cacheKey, true, 600);
            }

            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }

            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
            return response()->json([
                'success' => true,
                'message' => 'Webhook received successfully'
            ]);
        } catch (\Exception $exception) {
            Log::error('webhook exception: ' . $exception->getMessage());
            return response()->json([
                'success' => true,
                'message' => 'Webhook received Failed'
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    private function isCallbackProcessed($callbackQueryId)
    {
        $key = "callback_processed:{$callbackQueryId}";
        if (Cache::has($key)) {
            return true;
        }
        Cache::put($key, true, 600);
        return false;
    }

    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $customerAppId = $message['from']['id'];
        $customer = Customer::query()->where('app_id', $customerAppId)->first();


        switch (true) {
            case $text === '/start':
                $this->sendMessage(
                    $chatId,
                    "👋 Xin chào! Chào mừng bạn đến với Workflow Bot.\n\n' .
                'Gửi /workflows để xem danh sách workflows available."
                );
                break;

            case $text === '/workflows':
                $this->workflowService->getAvailableWorkflows($customer->id);
                break;

            case $text === '/continue':
                $this->workflowService->resumeWorkflow($customer->id);
                break;

            case strpos($text, '/start_workflow_') === 0:
                $workflowId = str_replace('/start_workflow_', '', $text);
                $this->workflowService->startWorkflow($customer->id, $workflowId);
                break;

            default:
                $this->sendMessage($chatId, "❓ Lệnh không được hỗ trợ. Gửi /workflows để xem workflows available.");
        }
    }

    private function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        $callbackQueryId = $callbackQuery['id'];
        $customerAppId = $callbackQuery['from']['id'];
        $customer = Customer::query()->where('app_id', $customerAppId)->first();

        // Check DUPLICATE CALLBACK !!
        if ($this->isCallbackProcessed($callbackQueryId)) {
            $this->answerCallbackQuery($callbackQueryId, "✅ Đã xử lý");
            return;
        }


        $parts = explode(':', $data);
        $action = $parts[0];

        $success = false;
        $message = null;

        try {
            switch ($action) {
                case 'complete_step':
                    if (count($parts) < 3) {
                        $message = "❌ Dữ liệu callback không hợp lệ";
                        break;
                    }
                    $stepId = $parts[1];
                    $progressId = $parts[2];
                    $success = $this->workflowService->completeStep($stepId, $progressId);
                    $message = $success ? "✅ Đã xử lý" : "❌ Không thể hoàn thành bước này";
                    break;

                case 'pause_workflow':
                    if (count($parts) < 2) {
                        $message = "❌ Dữ liệu callback không hợp lệ";
                        break;
                    }
                    $progressId = $parts[1];
                    $success = $this->workflowService->pauseWorkflow($progressId);
                    $message = $success ? "⏸️ Đã tạm dừng" : "❌ Không thể tạm dừng";
                    break;

                case 'start_workflow':
                    if (count($parts) < 2) {
                        $message = "❌ Dữ liệu callback không hợp lệ";
                        break;
                    }
                    $workflowId = $parts[1];
                    $success = $this->workflowService->startWorkflow($customer->id, $workflowId);
                    $message = $success ? "🚀 Đã bắt đầu workflow" : "❌ Không thể bắt đầu workflow";
                    break;

                case 'restart_workflow':
                    if (count($parts) < 2) {
                        $message = "❌ Dữ liệu callback không hợp lệ";
                        break;
                    }
                    $workflowId = $parts[1];
                    \App\Models\CustomerWorkflowStep::where('customer_id', $customer->id)
                        ->where('workflow_id', $workflowId)
                        ->delete();
                    $success = $this->workflowService->startWorkflow($customer->id, $workflowId);
                    $message = $success ? "🔄 Đã restart workflow" : "❌ Không thể restart workflow";
                    break;

                case 'list_workflows':
                    $this->workflowService->getAvailableWorkflows($customer->id);
                    $success = true;
                    $message = "📋 Danh sách workflows";
                    break;

                default:
                    $message = "❓ Callback không được hỗ trợ: {$data}";
            }
        } catch (\Exception $e) {
            Log::error('Callback query error', [
                'customer_id' => $customer->id,
                'callback_data' => $data,
                'error' => $e->getMessage()
            ]);
            $message = "❌ Có lỗi xảy ra: " . $e->getMessage();
        }

        $this->answerCallbackQuery($callbackQueryId, $message);
    }

    private function sendMessage($chatId, $text)
    {
        return Http::post("{$this->apiUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }

    private function answerCallbackQuery($callbackQueryId, $text = null)
    {
        $data = [
            'callback_query_id' => $callbackQueryId
        ];
        if ($text) {
            $data['text'] = $text;
        }

        return Http::post("{$this->apiUrl}/answerCallbackQuery", $data);
    }
}

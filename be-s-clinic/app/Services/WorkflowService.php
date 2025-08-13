<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerWorkflowStep;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    private $botToken;
    private $apiUrl;

    public function __construct()
    {
        $this->botToken = env('TELE_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    private function isCallbackProcessed($callbackQueryId)
    {
        $key = "callback_processed:{$callbackQueryId}";
        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, 300);
        return false;
    }

    public function startWorkflow($customerId, $workflowId)
    {
        $workflow = Workflow::query()->find($workflowId);

        if (!$workflow || +$workflow->status === -1) {
            return false;
        }

        $progress = CustomerWorkflowStep::query()->where('customer_id', $customerId)
            ->where('workflow_id', $workflowId)
            ->first();

        $customer = Customer::query()->find($customerId);

        if ($progress && $progress->status === 'completed') {
            $this->sendMessage($customer->app_id, "✅ Bạn đã hoàn thành workflow này rồi!");
            return false;
        }

        if (!$progress) {
            $progress = CustomerWorkflowStep::query()->create([
                'customer_id' => $customerId,
                'workflow_id' => $workflowId,
                'current_step_id' => $workflow->getFirstStep()->id,
                'status' => 'started',
                'started_at' => now()
            ]);
        }

        $this->sendCurrentStep($progress);

        return true;
    }

    public function sendCurrentStep($progress)
    {
        $step = $progress->currentStep;

        if (!$step) {
            return false;
        }

        $workflow = $progress->workflow;
        $progressPercentage = $progress->getProgressPercentage();

        $message = "🎯 <b>{$workflow->name}</b>\n\n";
        $message .= "📋 <b>Bước {$step->step_number}: {$step->title}</b>\n\n";
        $message .= "{$step->description}\n\n";
        $message .= "📊 Tiến độ: {$progressPercentage}%";

        $keyboard = [
            [
                [
                    'text' => $step->button_text,
                    'callback_data' => "complete_step:{$step->id}:{$progress->id}"
                ]
            ]
        ];

        if (!$step->isLastStep()) {
            $keyboard[] = [
                [
                    'text' => '⏸️ Tạm dừng',
                    'callback_data' => "pause_workflow:{$progress->id}"
                ]
            ];
        }
        $customer = $progress->customer;

        $this->sendMessageWithKeyboard($customer->app_id, $message, $keyboard);

        return true;
    }

    public function completeStep($stepId, $progressId)
    {
        return DB::transaction(function () use ($stepId, $progressId) {
            $progress = CustomerWorkflowStep::query()->lockForUpdate()->find($progressId);
            $step = WorkflowStep::find($stepId);

            $customer = $progress->customer;

            if (!$progress || !$step) {
                Log::error('Invalid step or progress', [
                    'step_id' => $stepId,
                    'progress_id' => $progressId,
                    'step_found' => !!$step,
                    'progress_found' => !!$progress
                ]);
                return false;
            }

            // Log detailed info for debugging
            Log::info('Processing step completion', [
                'customer_id' => $customer->app_id,
                'workflow_id' => $progress->workflow_id,
                'step_id' => $stepId,
                'step_number' => $step->step_number,
                'current_step_id' => $progress->current_step_id,
                'progress_status' => $progress->status,
                'completed_steps_count' => count($progress->completed_steps ?? [])
            ]);

            if ($progress->current_step_id != $stepId) {
                Log::warning('User trying to complete wrong step', [
                    'customer_id' => $customer->app_id,
                    'current_step_id' => $progress->current_step_id,
                    'requested_step_id' => $stepId
                ]);
                return false;
            }

            if ($progress->isStepCompleted($stepId)) {
                Log::warning('Step already completed', [
                    'customer_id' => $customer->app_id,
                    'step_id' => $stepId,
                    'completed_steps' => $progress->completed_steps
                ]);
                return false;
            }

            $progress->markStepCompleted($stepId);

            if ($step->completion_message) {
                $this->sendMessage($customer->app_id, "✅ " . $step->completion_message);
            } else {
                $this->sendMessage($customer->app_id, "✅ Đã hoàn thành bước {$step->step_number}!");
            }

            // Kiểm tra có bước tiếp theo không
            $nextStep = $step->getNextStep();
            $totalSteps = $progress->workflow->getTotalSteps();
            $currentStepNumber = $step->step_number;

            Log::info('Step progression check', [
                'customer_id' => $customer->app_id,
                'current_step_number' => $currentStepNumber,
                'total_steps' => $totalSteps,
                'next_step_found' => !!$nextStep,
                'next_step_id' => $nextStep ? $nextStep->id : null,
                'is_last_step_by_number' => $currentStepNumber >= $totalSteps,
                'is_last_step_by_method' => $step->isLastStep()
            ]);

            if ($nextStep && $currentStepNumber < $totalSteps) {
                $progress->current_step_id = $nextStep->id;
                $progress->status = 'in_progress';
                $progress->save();

                Log::info('Moving to next step', [
                    'customer_id' => $customer->app_id,
                    'from_step' => $stepId,
                    'to_step' => $nextStep->id,
                    'to_step_number' => $nextStep->step_number
                ]);

                sleep(2);
                $this->sendCurrentStep($progress);
            } else {
                $progress->current_step_id = null;
                $progress->status = 'completed';
                $progress->completed_at = now();
                $progress->save();

                Log::info('Workflow completed', [
                    'customer_id' => $customer->app_id,
                    'workflow_id' => $progress->workflow_id,
                    'completed_steps' => count($progress->completed_steps ?? []),
                    'total_steps' => $totalSteps
                ]);

                $this->sendWorkflowCompleted($progress);
            }

            return true;
        });
    }

    public function pauseWorkflow($progressId)
    {
        $progress = CustomerWorkflowStep::find($progressId);

        if (!$progress) {
            return false;
        }

        $progress->status = 'paused';
        $progress->save();

        $message = "⏸️ <b>Workflow đã tạm dừng</b>\n\n";
        $message .= "Bạn có thể tiếp tục bất cứ lúc nào bằng cách gửi /continue";
        $customer = $progress->customer;

        $this->sendMessage($customer->app_id, $message);

        return true;
    }

    public function resumeWorkflow($customerId, $workflowId = null)
    {
        $customer = Customer::find($customerId);
        $query = CustomerWorkflowStep::query()->where('customer_id', $customerId)
            ->where('status', 'paused');

        if ($workflowId) {
            $query->where('workflow_id', $workflowId);
        }

        $progress = $query->first();

        if (!$progress) {
            $this->sendMessage($customer->app_id, "❌ Không tìm thấy workflow nào đang tạm dừng.");
            return false;
        }

        $progress->status = 'in_progress';
        $progress->save();

        $this->sendMessage($customer->app_id, "▶️ Tiếp tục workflow...");
        $this->sendCurrentStep($progress);

        return true;
    }

    private function sendWorkflowCompleted($progress)
    {
        $workflow = $progress->workflow;
        $totalSteps = $workflow->getTotalSteps();
        $startedAt = $progress->started_at;
        $completeAt = $progress->completed_at;

        $startedAt = Carbon::parse($startedAt);
        $completeAt = Carbon::parse($completeAt);

        $message = "🎉 <b>Chúc mừng!</b>\n\n";
        $message .= "✅ Bạn đã hoàn thành workflow: <b>{$workflow->name}</b>\n";
        $message .= "📊 Tổng cộng: {$totalSteps} bước\n";
        $message .= "⏱️ Thời gian: " . $startedAt->diffForHumans($completeAt, true);

        $keyboard = [
            [
                [
                    'text' => '🔄 Bắt đầu lại',
                    'callback_data' => "restart_workflow:{$workflow->id}"
                ],
                [
                    'text' => '📋 Workflows khác',
                    'callback_data' => "list_workflows"
                ]
            ]
        ];
        $customer = $progress->customer;

        $this->sendMessageWithKeyboard($customer->app_id, $message, $keyboard);
    }

    public function getAvailableWorkflows($customerId)
    {
        $workflows = Workflow::query()->where('status', 1)->get();
        $message = "📋 <b>Danh sách Workflows</b>\n\n";
        $keyboard = [];

        foreach ($workflows as $workflow) {
            $progress = CustomerWorkflowStep::query()
                ->where('workflow_id', $workflow->id)
                ->where('customer_id', $customerId)
                ->first();
            $status = '';
            if ($progress) {
                switch ($progress->status) {
                    case 'completed':
                        $status = ' ✅';
                        break;
                    case 'paused':
                        $status = ' ⏸️';
                        break;
                    case 'in_progress':
                        $status = ' 🔄';
                        break;
                }
            }
            $message .= "• {$workflow->name}{$status}\n";
            $message .= "  {$workflow->description}\n\n";

            $keyboard[] = [
                [
                    'text' => $workflow->name . $status,
                    'callback_data' => "start_workflow:{$workflow->id}"
                ]
            ];
        }
        $this->sendMessageWithKeyboard($customerId, $message, $keyboard);
    }

    public function sendMessage($chatId, $text)
    {
        return Http::post("{$this->apiUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }

    public function sendMessageWithKeyboard($chatId, $text, $keyboard)
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard
                ])
            ]);
            Log::info('sendMessageWithKeyboard==' . $response->json());
        } catch (\Exception $exception) {
        }
    }
}

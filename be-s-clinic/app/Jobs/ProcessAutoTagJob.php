<?php

// app/Jobs/ProcessAutoTagJob.php

namespace App\Jobs;

use App\Models\AutoTagRuleGroup;
use App\Models\CustomerTag;
use App\Models\AnswerCustomer;
use App\Models\FormCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAutoTagJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $formCustomerId;

    public function __construct($formCustomerId)
    {
        $this->formCustomerId = $formCustomerId;
    }

    public function handle()
    {
        try {
            $formCustomer = FormCustomer::with('customer')->find($this->formCustomerId);
            if (!$formCustomer) {
                return;
            }

            $customerId = $formCustomer->customer_id;
            $formId = $formCustomer->form_id;

            $customerAnswers = AnswerCustomer::query()->where('form_customer_id', $this->formCustomerId)
                ->with(['formQuestion.question', 'questionOption'])
                ->get()
                ->map(function ($answer) {
                    return (object)[
                        'question_id' => $answer->formQuestion->question_id,
                        'question_option_id' => $answer->question_option_id,
                        'answer_text' => $answer->answer_text,
                    ];
                });

            $ruleGroups = AutoTagRuleGroup::active()
                ->forForm($formId)
                ->with(['conditions.question', 'conditions.questionOption', 'tag'])
                ->get();

            $tagsToApply = collect();

            foreach ($ruleGroups as $ruleGroup) {
                if ($ruleGroup->evaluateConditions($customerAnswers)) {
                    $tagsToApply->push([
                        'tag_id' => $ruleGroup->tag_id,
                        'tag_name' => $ruleGroup->tag->name,
                        'rule_group_name' => $ruleGroup->name,
                    ]);
                }
            }

            $appliedTags = [];
            foreach ($tagsToApply->unique('tag_id') as $tagData) {
                $exists = CustomerTag::query()->where([
                    'customer_id' => $customerId,
                    'tag_id' => $tagData['tag_id']
                ])->exists();

                if (!$exists) {
                    CustomerTag::query()->create([
                        'customer_id' => $customerId,
                        'tag_id' => $tagData['tag_id']
                    ]);

                    $appliedTags[] = $tagData;
                }
            }

            if (!empty($appliedTags)) {
                Log::info('Auto tags applied via job', [
                    'customer_id' => $customerId,
                    'form_customer_id' => $this->formCustomerId,
                    'applied_tags' => $appliedTags
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Auto tag job failed', [
                'form_customer_id' => $this->formCustomerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

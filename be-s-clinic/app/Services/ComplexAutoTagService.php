<?php

namespace App\Services;

use App\Models\AutoTagRuleGroup;
use App\Models\CustomerTag;
use App\Models\AnswerCustomer;
use App\Models\FormCustomer;
use Illuminate\Support\Facades\Log;

class ComplexAutoTagService
{
    /**
     * Process complex auto tagging với multiple conditions (được gọi từ Job)
     */
    public function processComplexAutoTag($formCustomerId)
    {
        try {
            $formCustomer = FormCustomer::with('customer')->find($formCustomerId);
            if (!$formCustomer) {
                Log::warning('Form customer not found', ['form_customer_id' => $formCustomerId]);
                return false;
            }

            $customerId = $formCustomer->customer_id;
            $formId = $formCustomer->form_id;

            Log::info('Processing auto tags', [
                'form_customer_id' => $formCustomerId,
                'customer_id' => $customerId,
                'form_id' => $formId
            ]);

            $customerAnswers = AnswerCustomer::where('form_customer_id', $formCustomerId)
                ->with(['formQuestion.question', 'questionOption'])
                ->get()
                ->map(function ($answer) {
                    return (object)[
                        'question_id' => $answer->formQuestion->question_id,
                        'question_option_id' => $answer->question_option_id,
                        'answer_text' => $answer->answer_text,
                        'original_answer' => $answer
                    ];
                });

            if ($customerAnswers->isEmpty()) {
                Log::info('No customer answers found', ['form_customer_id' => $formCustomerId]);
                return [];
            }

            $ruleGroups = AutoTagRuleGroup::active()
                ->forForm($formId)
                ->with(['conditions.question', 'conditions.questionOption', 'tag'])
                ->get();

            if ($ruleGroups->isEmpty()) {
                Log::info('No active rule groups found', ['form_id' => $formId]);
                return [];
            }

            $tagsToApply = collect();

            foreach ($ruleGroups as $ruleGroup) {
                try {
                    $evaluationResult = $ruleGroup->evaluateConditions($customerAnswers);

                    Log::debug('Rule group evaluation', [
                        'rule_group_id' => $ruleGroup->id,
                        'rule_group_name' => $ruleGroup->name,
                        'logic_operator' => $ruleGroup->logic_operator,
                        'result' => $evaluationResult
                    ]);

                    if ($evaluationResult) {
                        $tagsToApply->push([
                            'tag_id' => $ruleGroup->tag_id,
                            'tag_name' => $ruleGroup->tag->name,
                            'rule_group_id' => $ruleGroup->id,
                            'rule_group_name' => $ruleGroup->name,
                            'conditions_met' => $this->getMetConditionsDescription($ruleGroup, $customerAnswers)
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error evaluating rule group', [
                        'rule_group_id' => $ruleGroup->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $appliedTags = [];
            foreach ($tagsToApply->unique('tag_id') as $tagData) {
                $exists = CustomerTag::where([
                    'customer_id' => $customerId,
                    'tag_id' => $tagData['tag_id']
                ])->exists();

                if (!$exists) {
                    CustomerTag::create([
                        'customer_id' => $customerId,
                        'tag_id' => $tagData['tag_id']
                    ]);

                    $appliedTags[] = $tagData;

                    Log::info('Applied auto tag', [
                        'customer_id' => $customerId,
                        'tag_id' => $tagData['tag_id'],
                        'tag_name' => $tagData['tag_name'],
                        'rule_group' => $tagData['rule_group_name']
                    ]);
                } else {
                    Log::info('Tag already exists for customer', [
                        'customer_id' => $customerId,
                        'tag_id' => $tagData['tag_id'],
                        'tag_name' => $tagData['tag_name']
                    ]);
                }
            }

            if (!empty($appliedTags)) {
                Log::info('Auto tags processing completed', [
                    'customer_id' => $customerId,
                    'form_customer_id' => $formCustomerId,
                    'total_applied' => count($appliedTags),
                    'applied_tags' => array_column($appliedTags, 'tag_name')
                ]);
            } else {
                Log::info('No tags applied', [
                    'customer_id' => $customerId,
                    'form_customer_id' => $formCustomerId,
                    'reason' => $tagsToApply->isEmpty() ? 'No rules matched' : 'All tags already exist'
                ]);
            }

            return $appliedTags;
        } catch (\Exception $e) {
            Log::error('Complex auto tag processing failed', [
                'form_customer_id' => $formCustomerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get description for conditions matched
     */
    private function getMetConditionsDescription($ruleGroup, $customerAnswers)
    {
        $descriptions = [];

        foreach ($ruleGroup->conditions as $condition) {
            $customerAnswer = $customerAnswers->where('question_id', $condition->question_id)->first();

            if ($customerAnswer && $condition->checkCondition($customerAnswer)) {
                $descriptions[] = [
                    'condition_id' => $condition->id,
                    'question_id' => $condition->question_id,
                    'question_title' => $condition->question->title,
                    'condition_description' => $condition->getConditionDescription(),
                    'customer_answer' => $this->getCustomerAnswerText($customerAnswer),
                    'condition_type' => $condition->condition_type
                ];
            }
        }

        return $descriptions;
    }

    /**
     * Get customer answer text for logging
     */
    private function getCustomerAnswerText($customerAnswer)
    {
        if (!empty($customerAnswer->answer_text)) {
            return $customerAnswer->answer_text;
        }

        if (!empty($customerAnswer->question_option_id) && isset($customerAnswer->original_answer->questionOption)) {
            return $customerAnswer->original_answer->questionOption->text;
        }

        return 'N/A';
    }

    /**
     * Preview complex auto tags
     */
    public function previewComplexAutoTag($formId, $answersData)
    {
        try {
            $simulatedAnswers = collect();

            foreach ($answersData as $questionId => $answerInfo) {
                $simulatedAnswers->push(
                    (object)[
                        'question_id' => $questionId,
                        'question_option_id' => $answerInfo['option_id'] ?? null,
                        'answer_text' => $answerInfo['text'] ?? null
                    ]
                );
            }

            $ruleGroups = AutoTagRuleGroup::active()
                ->forForm($formId)
                ->with(['conditions.question', 'conditions.questionOption', 'tag'])
                ->get();

            $previewTags = collect();

            foreach ($ruleGroups as $ruleGroup) {
                if ($ruleGroup->evaluateConditions($simulatedAnswers)) {
                    $previewTags->push([
                        'tag_id' => $ruleGroup->tag_id,
                        'tag_name' => $ruleGroup->tag->name,
                        'rule_group_id' => $ruleGroup->id,
                        'rule_group_name' => $ruleGroup->name,
                        'logic_operator' => $ruleGroup->logic_operator,
                        'conditions_met' => $this->getMetConditionsDescription($ruleGroup, $simulatedAnswers)
                    ]);
                }
            }

            return $previewTags->unique('tag_id');
        } catch (\Exception $e) {
            Log::error('Preview auto tag failed', [
                'form_id' => $formId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Validate rule group conditions before khi create/update
     */
    public function validateRuleGroup($ruleGroupData)
    {
        $errors = [];

        if (empty($ruleGroupData['conditions'])) {
            $errors[] = 'Rule group must have at least one condition';
            return $errors;
        }

        foreach ($ruleGroupData['conditions'] as $index => $condition) {
            $conditionNum = $index + 1;

            if (empty($condition['question_id'])) {
                $errors[] = "Condition {$conditionNum}: Question is required";
                continue;
            }

            // Validate cho multiple choice questions
            if (!empty($condition['question_option_id'])) {
                continue;
            }

            if (empty($condition['condition_value'])) {
                $errors[] = "Condition {$conditionNum}: Condition value is required for text questions";
                continue;
            }

            $conditionType = $condition['condition_type'] ?? '';
            $conditionValue = $condition['condition_value'] ?? [];

            switch ($conditionType) {
                case 'equals':
                case 'contains':
                case 'starts_with':
                case 'ends_with':
                    if (empty($conditionValue['value'])) {
                        $errors[] = "Condition {$conditionNum}: Value is required for {$conditionType} condition";
                    }
                    break;

                case 'in_range':
                    if (empty($conditionValue['min']) && empty($conditionValue['max'])) {
                        $errors[] = "Condition {$conditionNum}: At least min or "
                            . "max value is required for range condition";
                    }
                    if (
                        !empty($conditionValue['min']) && !empty($conditionValue['max'])
                        && $conditionValue['min'] > $conditionValue['max']
                    ) {
                        $errors[] = "Condition {$conditionNum}: Min value cannot be greater than max value";
                    }
                    break;

                case 'in_list':
                    if (empty($conditionValue['values']) || !is_array($conditionValue['values'])) {
                        $errors[] = "Condition {$conditionNum}: Values array is required for in_list condition";
                    }
                    break;

                default:
                    $errors[] = "Condition {$conditionNum}: Invalid condition type '{$conditionType}'";
            }
        }

        return $errors;
    }

    /**
     * Get statistics auto tag performance
     */
    public function getAutoTagStats($formId = null, $dateFrom = null, $dateTo = null)
    {
        try {
            $query = CustomerTag::query();

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $totalTagsApplied = $query->count();

            $tagStats = $query->with('tag:id,name')
                ->selectRaw('tag_id, count(*) as count')
                ->groupBy('tag_id')
                ->orderByDesc('count')
                ->get()
                ->map(function ($stat) {
                    return [
                        'tag_id' => $stat->tag_id,
                        'tag_name' => $stat->tag->name ?? 'Unknown',
                        'count' => $stat->count
                    ];
                });

            return [
                'total_tags_applied' => $totalTagsApplied,
                'tag_breakdown' => $tagStats,
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get auto tag stats', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function removeAutoTagsForCustomer($customerId, $tagIds = [])
    {
        try {
            $query = CustomerTag::where([
                'customer_id' => $customerId
            ]);

            if (!empty($tagIds)) {
                $query->whereIn('tag_id', $tagIds);
            }

            $removedCount = $query->delete();

            Log::info('Removed auto tags for customer', [
                'customer_id' => $customerId,
                'tag_ids' => $tagIds,
                'removed_count' => $removedCount
            ]);

            return $removedCount;
        } catch (\Exception $e) {
            Log::error('Failed to remove auto tags', [
                'customer_id' => $customerId,
                'tag_ids' => $tagIds,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Re-process auto tags customer
     */
    public function reprocessCustomerTags($customerId)
    {
        try {
            // Get all form submissions của customer
            $formCustomers = FormCustomer::query()->where('customer_id', $customerId)
                ->where('status', 'completed')
                ->get();

            $totalProcessed = 0;
            foreach ($formCustomers as $formCustomer) {
                // Remove existing auto tags
                $this->removeAutoTagsForCustomer($customerId);

                // Re-process
                $result = $this->processComplexAutoTag($formCustomer->id);
                if ($result !== false) {
                    $totalProcessed++;
                }
            }

            Log::info('Re-processed customer tags', [
                'customer_id' => $customerId,
                'form_submissions_processed' => $totalProcessed
            ]);

            return $totalProcessed;
        } catch (\Exception $e) {
            Log::error('Failed to reprocess customer tags', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

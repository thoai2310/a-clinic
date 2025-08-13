<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoTagRuleGroup;
use App\Models\AutoTagRuleCondition;
use App\Models\Question;
use App\Services\ComplexAutoTagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AutoTagRuleGroupController extends Controller
{
    protected $autoTagService;

    public function __construct(ComplexAutoTagService $autoTagService)
    {
        $this->autoTagService = $autoTagService;
    }

    /**
     * GET /api/admin/auto-tag-rule-groups
     * List all rule groups with pagination and filters
     */
    public function index(Request $request)
    {
        try {
            $data = [];
            return response()->json([
                'success' => true,
                'message' => 'Get List Rule Group Success',
                'data' => [
                    'list' => $data['data'] ?? [],
                    'total' => $data['total'] ?? 0,
                    'page' => $data['current_page'] ?? 1,
                    'pageSize' => $data['per_page'] ?? 10,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rule groups',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    /**
     * POST /api/admin/auto-tag-rule-groups
     * Create new rule group with conditions
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'form_id' => 'required|exists:forms,id',
                'tag_id' => 'required|exists:tags,id',
                'logic_operator' => 'required|in:AND,OR',
                'description' => 'nullable|string',
                'conditions' => 'required|array|min:1',
                'conditions.*.question_id' => 'required|exists:questions,id',
                'conditions.*.question_option_id' => 'nullable|exists:question_options,id',
                'conditions.*.condition_type' => 'required|in:equals,contains,starts_with,ends_with,in_range,in_list',
                'conditions.*.condition_value' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validationErrors = $this->autoTagService->validateRuleGroup($request->all());
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rule validation failed',
                    'errors' => $validationErrors
                ], 422);
            }

            $ruleGroup = DB::transaction(function () use ($request) {
                $ruleGroup = AutoTagRuleGroup::create([
                    'name' => $request->name,
                    'form_id' => $request->form_id,
                    'tag_id' => $request->tag_id,
                    'logic_operator' => $request->logic_operator,
                    'description' => $request->description,
                ]);

                // Create conditions
                foreach ($request->conditions as $index => $conditionData) {
                    AutoTagRuleCondition::create([
                        'rule_group_id' => $ruleGroup->id,
                        'question_id' => $conditionData['question_id'],
                        'question_option_id' => $conditionData['question_option_id'] ?? null,
                        'condition_type' => $conditionData['condition_type'],
                        'condition_value' => $conditionData['condition_value'] ?? null,
                        'order' => $index + 1,
                    ]);
                }

                return $ruleGroup->load([
                    'form:id,title,code',
                    'tag:id,name,key',
                    'conditions.question:id,title,type',
                    'conditions.questionOption:id,text,value'
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Rule group created successfully',
                'data' => $ruleGroup
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create rule group',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    /**
     * GET /api/admin/auto-tag-rule-groups/{id}
     * Get specific rule group details
     */
    public function show($id)
    {
        try {
            $ruleGroup = AutoTagRuleGroup::with([
                'form:id,title,code',
                'tag:id,name,key',
                'conditions' => function ($query) {
                    $query->orderBy('order');
                },
                'conditions.question:id,title,type',
                'conditions.questionOption:id,text,value'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $ruleGroup
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rule group not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * PUT /api/admin/auto-tag-rule-groups/{id}
     * Update rule group and conditions
     */
    public function update(Request $request, $id)
    {
        try {
            $ruleGroup = AutoTagRuleGroup::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'form_id' => 'required|exists:forms,id',
                'tag_id' => 'required|exists:tags,id',
                'logic_operator' => 'required|in:AND,OR',
                'description' => 'nullable|string',
                'status' => 'required|in:-1,1',
                'conditions' => 'required|array|min:1',
                'conditions.*.question_id' => 'required|exists:questions,id',
                'conditions.*.question_option_id' => 'nullable|exists:question_options,id',
                'conditions.*.condition_type' => 'required|in:equals,contains,starts_with,ends_with,in_range,in_list',
                'conditions.*.condition_value' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updatedRuleGroup = DB::transaction(function () use ($request, $ruleGroup) {
                // Update rule group
                $ruleGroup->update([
                    'name' => $request->name,
                    'form_id' => $request->form_id,
                    'tag_id' => $request->tag_id,
                    'logic_operator' => $request->logic_operator,
                    'description' => $request->description,
                    'status' => $request->status,
                ]);

                // Delete existing conditions
                $ruleGroup->conditions()->delete();

                // Create new conditions
                foreach ($request->conditions as $index => $conditionData) {
                    AutoTagRuleCondition::create([
                        'rule_group_id' => $ruleGroup->id,
                        'question_id' => $conditionData['question_id'],
                        'question_option_id' => $conditionData['question_option_id'] ?? null,
                        'condition_type' => $conditionData['condition_type'],
                        'condition_value' => $conditionData['condition_value'] ?? null,
                        'order' => $index + 1,
                    ]);
                }

                return $ruleGroup->load([
                    'form:id,title,code',
                    'tag:id,name,key',
                    'conditions.question:id,title,type',
                    'conditions.questionOption:id,text,value'
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Rule group updated successfully',
                'data' => $updatedRuleGroup
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rule group',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    /**
     * DELETE /api/admin/auto-tag-rule-groups/{id}
     * Delete rule group (soft delete)
     */
    public function destroy($id)
    {
        try {
            $ruleGroup = AutoTagRuleGroup::findOrFail($id);
            $ruleGroup->delete(); // Soft delete sẽ cascade delete conditions

            return response()->json([
                'success' => true,
                'message' => 'Rule group deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rule group',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    /**
     * PATCH /api/admin/auto-tag-rule-groups/{id}/status
     * Update rule group status only
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:-1,1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ruleGroup = AutoTagRuleGroup::findOrFail($id);
            $ruleGroup->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Rule group status updated successfully',
                'data' => $ruleGroup
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    /**
     * GET /api/admin/forms/{formId}/questions
     * Get questions for a specific form (helper endpoint)
     */
    public function getFormQuestions($formId)
    {
        try {
            $questions = Question::whereHas('forms', function ($query) use ($formId) {
                $query->where('forms.id', $formId);
            })->with(['options:id,question_id,text,value'])->get();

            return response()->json([
                'success' => true,
                'data' => $questions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch form questions',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    /**
     * GET /api/admin/questions/{questionId}/options
     * Get options for a specific question (helper endpoint)
     */
    public function getQuestionOptions($questionId)
    {
        try {
            $question = Question::with(['options:id,question_id,text,value'])->findOrFail($questionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'question' => $question,
                    'options' => $question->options
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * GET /api/admin/forms/{formId}/tags-preview
     * Preview tags that would be applied for given answers
     */
    public function previewTags(Request $request, $formId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|integer',
                'answers.*.option_id' => 'nullable|integer',
                'answers.*.text' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Convert answers to format expected by service
            $answersData = [];
            foreach ($request->answers as $answer) {
                $answersData[$answer['question_id']] = [
                    'option_id' => $answer['option_id'] ?? null,
                    'text' => $answer['text'] ?? null,
                ];
            }

            $previewTags = $this->autoTagService->previewComplexAutoTag($formId, $answersData);

            return response()->json([
                'success' => true,
                'data' => $previewTags
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview tags',
                'error' => $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }
}

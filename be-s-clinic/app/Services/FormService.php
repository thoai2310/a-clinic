<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Form;
use App\Models\FormCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormService
{
    private $messageService;

    public function __construct(BotMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function filter($filters)
    {
        try {
            $forms = Form::query();
            $forms = $forms->with(['assigned']);

            $pageSize = $filters['pageSize'] ?? 2;

            return $forms->paginate($pageSize);
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    public function create()
    {
        //
    }

    public function detail($id)
    {
        try {
            $form = Form::with([
                'hasManyFormQuestion' => function ($query) {
                    $query->orderBy('order');
                },
                'hasManyFormQuestion.question.options'
            ])->find($id);

            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form not found',
                    'data' => null
                ], 404);
            }
            $questions = [];
            foreach ($form->hasManyFormQuestion as $index => $formQuestion) {
                $question = $formQuestion->question;

                $options = [];
                foreach ($question->options as $optionIndex => $option) {
                    $options[] = [
                        'id' => $option->id,
                        'text' => $option->text,
                        'value' => $option->value,
                        'isOther' => +$option->is_other === 1
                    ];
                }

                $questions[] = [
                    'id' => $question->id,
                    'title' => $formQuestion->custom_title ?: $question->title,
                    'description' => $formQuestion->custom_description ?: $question->description ?: '',
                    'type' => $question->type,
                    'required' => (bool)$formQuestion->required,
                    'hasOtherOption' => +$question->has_other_option === 1,
                    'options' => $options
                ];
            }

            return [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description ?: '',
                'questions' => $questions
            ];
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function assignToCustomers($formId, $customerIds)
    {
        try {
            DB::beginTransaction();
            foreach ($customerIds as $customerId) {
                $check = FormCustomer::query()
                    ->where('form_id', $formId)
                    ->where('customer_id', $customerId)
                    ->first();
                if (!$check) {
                    $formCustomer = new FormCustomer();
                    $formCustomer->code = Str::random(16);
                    $formCustomer->customer_id = $customerId;
                    $formCustomer->form_id = $formId;
                    $formCustomer->status = 'new';
                    $formCustomer->save();
                    $customer = Customer::query()->find($customerId);
                    if ($customer->app_id) {
                        $message = "Mời bạn thực hiện form khảo sát sau: " . route(
                            'survey.show',
                            ['survey_code' => $formCustomer->code]
                        );
                        $userId = $customer->app_id;
                        $this->messageService->sendMessage($userId, $message);
                    }
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function all()
    {
        try {
            $forms = Form::query();
            $forms = $forms->with(['formQuestions']);
            $forms = $forms->get();

            $response = [];
            foreach ($forms as $form) {
                $item = [];
                $item['id'] = $form->id;
                $item['title'] = $form->title;
                $item['description'] = $form->description;

                $formQuestions = $form->formQuestions;
                $questions = [];
                if (!empty($formQuestions)) {
                    foreach ($formQuestions as $formQuestion) {
                        $question = $formQuestion->question;
                        $itemQuestion = [];
                        $itemQuestion['id'] = $question->id;
                        $itemQuestion['code'] = $question->code;
                        $itemQuestion['title'] = $question->title;
                        $itemQuestion['type'] = $question->type;
                        $itemQuestion['required'] = +$formQuestion->required === 1;
                        $itemQuestion['description'] = $question->description;
                        $itemQuestion['hasOtherOption'] = +$formQuestion->has_other_option === 1;

                        $options = [];

                        $questionOptions = $question->options;

                        if (!empty($questionOptions)) {
                            foreach ($questionOptions as $questionOption) {
                                $itemOption = [];
                                $itemOption['id'] = $questionOption->id;
                                $itemOption['text'] = $questionOption->text;
                                $itemOption['value'] = $questionOption->value;
                                $itemOption['isOther'] = +$questionOption->is_other === 1;
                                $itemOption['question_id'] = $questionOption->question_id;
                                $itemOption['order'] = $questionOption->order;
                                $options[] = $itemOption;
                            }
                        }
                        $itemQuestion['options'] = $options;
                        $questions[] = $itemQuestion;
                    }
                }
                $item['questions'] = $questions;
                $response[] = $item;
            }

            return $response;
        } catch (\Exception $exception) {
            return [];
        }
    }
}

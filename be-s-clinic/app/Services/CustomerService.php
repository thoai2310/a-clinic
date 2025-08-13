<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Form;

class CustomerService
{
    public function all()
    {
        return Customer::query()->get();
    }

    public function filter($filters)
    {
        $forms = Form::query();

        $pageSize = $filters['pageSize'] ?? 2;

        return $forms->paginate($pageSize);
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
}

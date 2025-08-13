<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAutoTagJob;
use App\Models\FormCustomer;
use App\Models\AnswerCustomer;
use App\Models\FormQuestion;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormUserSubmitController extends Controller
{
    public function showForm(Request $request)
    {
        try {
            $formCustomer = FormCustomer::query()->where('code', $request->input('survey_code'))->with([
                'form' => function ($query) {
                    $query->select('id', 'title', 'description');
                },
                'form.hasManyFormQuestion' => function ($query) {
                    $query->select(
                        'id',
                        'form_id',
                        'question_id',
                        'required',
                        'order',
                        'custom_title',
                        'custom_description'
                    )
                        ->orderBy('order');
                },
                'form.hasManyFormQuestion.question' => function ($query) {
                    $query->select('id', 'title', 'description', 'type', 'has_other_option');
                },
                'form.hasManyFormQuestion.question.options' => function ($query) {
                    $query->select('id', 'question_id', 'text', 'value', 'is_other', 'order')
                        ->orderBy('order');
                }
            ])->first();

            if (!$formCustomer) {
                return response()->json([
                    'success' => false,
                    'message' => 'FormCustomer not found'
                ], 404);
            }

            if (!$formCustomer->form) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form not found'
                ], 404);
            }

            $formData = [
                'id' => $formCustomer->form->id,
                'title' => $formCustomer->form->title,
                'description' => $formCustomer->form->description,
                'questions' => []
            ];

            foreach ($formCustomer->form->hasManyFormQuestion as $formQuestion) {
                $question = $formQuestion->question;

                $questionData = [
                    'id' => $question->id,
                    'form_question_id' => $formQuestion->id,
                    'title' => $formQuestion->custom_title ?: $question->title,
                    'description' => $formQuestion->custom_description ?: $question->description,
                    'type' => $question->type,
                    'required' => +$formQuestion->required,
                    'hasOtherOption' => +$question->has_other_option,
                    'options' => []
                ];

                foreach ($question->options as $option) {
                    $questionData['options'][] = [
                        'id' => $option->id,
                        'text' => $option->text,
                        'value' => $option->value,
                        'isOther' => +$option->is_other === 1
                    ];
                }

                $formData['questions'][] = $questionData;
            }

            $isSubmitted = $formCustomer->status === 'submitted' && $formCustomer->submitted_at !== null;

            $viewData = [
                'form' => $formData,
                'isSubmitted' => $isSubmitted
            ];

            if ($isSubmitted) {
                $answers = $this->getSubmittedAnswers($formCustomer->id);
                $viewData['answers'] = $answers;
            }

            return view('form_user_submit', $viewData);
        } catch (\Exception $e) {
            abort(403);
        }
    }

    public function submitForm(Request $request)
    {
        try {
            $formCustomer = FormCustomer::query()->where('code', $request->input('survey_code'))->first();

            if (!$formCustomer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã khảo sát'
                ], 404);
            }

            if ($formCustomer->status === 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted this form'
                ], 400);
            }

            $formQuestions = FormQuestion::query()->where('form_id', $formCustomer->form_id)
                ->with(['question', 'question.options'])
                ->orderBy('order')
                ->get();

            DB::beginTransaction();

            AnswerCustomer::query()->where('form_customer_id', $formCustomer->id)->delete();

            foreach ($formQuestions as $formQuestion) {
                $questionId = $formQuestion->question->id;
                $questionKey = 'question_' . $questionId;
                $otherKey = $questionKey . '_other';

                if ($request->has($questionKey)) {
                    $answers = $request->input($questionKey);

                    // exec multiple choice (checkbox)
                    if (is_array($answers)) {
                        foreach ($answers as $answer) {
                            if (!empty($answer)) {
                                // Check if this is an "other" option
                                $option = QuestionOption::find($answer);
                                if ($option && $option->is_other && $request->has($otherKey)) {
                                    $this->saveAnswer(
                                        $formCustomer->id,
                                        $formQuestion->id,
                                        $answer,
                                        $request->input($otherKey)
                                    );
                                } else {
                                    $this->saveAnswer($formCustomer->id, $formQuestion->id, $answer, null);
                                }
                            }
                        }
                    } else {
                        // Single choice (radio, text)
                        if (!empty($answers)) {
                            $answerText = null;
                            $optionId = null;

                            // Check if it's an option ID or direct text
                            if (is_numeric($answers) && $formQuestion->question->type !== 'text') {
                                $option = QuestionOption::find($answers);
                                if ($option) {
                                    $optionId = $answers;

                                    // If it's "Other" option, get the text from other input
                                    if ($option->is_other && $request->has($otherKey)) {
                                        $answerText = $request->input($otherKey);
                                    }
                                } else {
                                    // If option not found, treat as text
                                    $answerText = $answers;
                                }
                            } else {
                                // Direct text input
                                $answerText = $answers;
                            }

                            $this->saveAnswer($formCustomer->id, $formQuestion->id, $optionId, $answerText);
                        }
                    }
                }
            }

            // Update FormCustomer status
            $formCustomer->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            $autoTag = new ProcessAutoTagJob($formCustomer->id);
            dispatch($autoTag);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your submission.!'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong ' . $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    private function saveAnswer($formCustomerId, $formQuestionId, $optionId, $answerText)
    {
        AnswerCustomer::query()->create([
            'form_customer_id' => $formCustomerId,
            'form_question_id' => $formQuestionId,
            'question_option_id' => $optionId,
            'answer_text' => $answerText
        ]);
    }

    private function getSubmittedAnswers($formCustomerId)
    {
        $answers = AnswerCustomer::query()->where('form_customer_id', $formCustomerId)
            ->with(['questionOption', 'formQuestion.question'])
            ->get();

        $formattedAnswers = [];

        foreach ($answers as $answer) {
            $questionId = $answer->formQuestion->question->id;

            $answerText = '';
            if ($answer->question_option_id) {
                $answerText = $answer->questionOption->text;
                if ($answer->answer_text) {
                    $answerText .= ': ' . $answer->answer_text;
                }
            } else {
                $answerText = $answer->answer_text;
            }

            // Handle multiple answers for checkbox questions
            if (!isset($formattedAnswers[$questionId])) {
                $formattedAnswers[$questionId] = [];
            }

            if ($answer->formQuestion->question->type === 'checkbox') {
                $formattedAnswers[$questionId][] = $answerText;
            } else {
                $formattedAnswers[$questionId] = $answerText;
            }
        }
        return $formattedAnswers;
    }

    public function retakeSurvey(Request $request)
    {
        try {
            $formCustomer = FormCustomer::where('code', $request->input('survey_code'))->first();

            if (!$formCustomer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã khảo sát'
                ], 404);
            }

            if ($formCustomer->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey chưa được submit'
                ], 400);
            }

            DB::beginTransaction();

            AnswerCustomer::query()->where('form_customer_id', $formCustomer->id)->delete();

            $formCustomer->update([
                'status' => 'pending',
                'submitted_at' => null,
                'ip_address' => null,
                'user_agent' => null
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Survey đã được reset thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong ' . $e->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }
}

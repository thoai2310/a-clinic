<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Form;
use App\Models\FormQuestion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\FormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function __construct(
        private FormService $formService
    ) {
    }

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $titleForm = $request->input('title');
            $descriptionForm = $request->input('description');

            $form = new Form();
            $form->code = Str::random(6);
            $form->title = $titleForm;
            $form->description = $descriptionForm;
            $form->status = 'draft';
            $form->save();

            $formID = $form->id;
            if ($formID) {
                $questions = $request->input('questions');
                foreach ($questions as $key => $question) {
                    $titleQuestion = $question['title'];
                    $descriptionQuestion = $question['description'];
                    $typeQuestion = $question['type'];
                    $required = $question['required'];
                    $hasOtherOption = $question['hasOtherOption'];

                    $newQuestion = new Question();
                    $newQuestion->code = Str::random(6);
                    $newQuestion->type = $typeQuestion;
                    $newQuestion->title = $titleQuestion;
                    $newQuestion->description = $descriptionQuestion;
                    $newQuestion->has_other_option = $hasOtherOption ? 1 : -1;
                    $newQuestion->save();

                    $questionID = $newQuestion->id;

                    if ($questionID) {
                        $formQuestion = new FormQuestion();
                        $formQuestion->form_id = $formID;
                        $formQuestion->question_id = $questionID;
                        $formQuestion->required = $required ? 1 : -1;
                        $formQuestion->order = $key + 1;
                        $formQuestion->custom_title = $titleQuestion;
                        $formQuestion->custom_description = $descriptionQuestion;
                        $formQuestion->save();
                    }

                    $options = $question['options'];
                    foreach ($options as $keyOption => $option) {
                        $questionOption = new QuestionOption();
                        $questionOption->question_id = $questionID;
                        $questionOption->text = $option['text'];
                        $questionOption->value = $option['value'];
                        $questionOption->is_other = $option['isOther'];
                        $questionOption->order = $keyOption + 1;
                        $questionOption->save();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Create A New Form Successfully',
                'data' => []
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Creating a new form failed',
                'error' => $exception->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    public function show($id)
    {
        try {
            $detail = $this->formService->detail($id);
            if (empty($detail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get a detail form failed',
                ], config('apps.error_code.GENERAL_ERROR'));
            }
            return response()->json([
                'success' => true,
                'message' => 'Get a detail form successfully',
                'data' => $detail
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Get a detail form failed',
                'error' => $exception->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    public function index(Request $request)
    {
//        header('Access-Control-Allow-Origin: http://localhost:5173');
//        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
//        header('Access-Control-Allow-Credentials: true');
        $data = $this->formService->filter($request->all());
        foreach ($data as $value) {
            $assigned = $value->assigned;
            $value->assignCustomerIds = [];
            if ($assigned) {
                $value->assignCustomerIds = collect($assigned)->pluck('customer_id')->toArray();
            }
        }
        $data = $data->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê thành công',
            'data' => [
                'list' => $data['data'] ?? [],
                'total' => $data['total'] ?? 0,
                'page' => $data['current_page'] ?? 1,
                'pageSize' => $data['per_page'] ?? 10,
            ]
        ]);
    }

    public function update()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê thành công',
            'data' => []
        ]);
    }

    public function assignToCustomers(Request $request)
    {
        try {
            $formId = $request->input('form_id');
            $customerIds = $request->input('customer_ids');
            $result = $this->formService->assignToCustomers($formId, $customerIds);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assigned successfully',
                    'data' => []
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Assign to customer failed',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Assign to customer failed',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    public function all()
    {
        try {
            $data = $this->formService->all();


            return response()->json([
                'success' => true,
                'message' => 'Get All Forms Successfully',
                'data' => $data
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Assign to customer failed',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }
}

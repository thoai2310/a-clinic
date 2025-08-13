<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Form;
use App\Models\FormQuestion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\CustomerService;
use App\Services\FormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    ) {
    }

    public function all()
    {
        return response()->json([
            'success' => true,
            'message' => 'Get All Customers Success',
            'data' => $this->customerService->all()
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
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Tag;
use App\Services\TagService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    private $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }


    public function index(Request $request)
    {
        $data = $this->tagService->filter($request->all());
        foreach ($data as $value) {
            $customerTag = $value->customers;
            $value->customerIds = [];
            if ($customerTag) {
                $value->customerIds = collect($customerTag)->pluck('customer_id')->toArray();
            }
        }
        $data = $data->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Get List Tag Success',
            'data' => [
                'list' => $data['data'] ?? [],
                'total' => $data['total'] ?? 0,
                'page' => $data['current_page'] ?? 1,
                'pageSize' => $data['per_page'] ?? 10,
            ]
        ]);
    }

    public function create(Request $request)
    {
        try {
            $data = $request->all();

            $tag = $this->tagService->create($data);
            if ($tag->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lấy list tag thành công',
                    'data' => $tag
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Create tag error',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Create tag error',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }


    public function all()
    {
        try {
            $data = Tag::query()->with('customers')->get();
            $result = [];
            foreach ($data as $value) {
                $item = [];
                $item['id'] = $value->id;
                $item['name'] = $value->name;
                $customers = $value->customers;
                $item['customers'] = [];
                foreach ($customers as $customer) {
                    $customer = $customer->customer;
                    if ($customer) {
                        $item['customers'][] = [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                        ];
                    }
                }
                $result[] = $item;
            }

            return response()->json([
                'success' => true,
                'message' => 'Get All Tags Success',
                'data' => $result
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Get All Tag error',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }
}

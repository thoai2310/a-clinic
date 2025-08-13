<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Tag;
use App\Services\MessageService;
use App\Services\TagService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private $messageService;
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->messageService->filters($request->all());
            $data = $data->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Lấy list message thành công',
                'data' => [
                    'list' => $data['data'] ?? [],
                    'total' => $data['total'] ?? 0,
                    'page' => $data['current_page'] ?? 1,
                    'pageSize' => $data['per_page'] ?? 10,
                ]
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => true,
                'message' => 'Get List Message Failed',
                'data' => []
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }

    public function create(Request $request)
    {
        try {
            $result = $this->messageService->create($request->all());
            if (!empty($result->id)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Create Message Successfully',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Create Message Failed',
            ], config('apps.error_code.GENERAL_ERROR'));
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Create Message Failed',
                'error' => $exception->getMessage()
            ], config('apps.error_code.GENERAL_ERROR'));
        }
    }
}

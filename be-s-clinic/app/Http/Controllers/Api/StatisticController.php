<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function __construct()
    {
    }

    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy thống kê thành công',
            'data' => [
                'users' => [
                    'total' => 1,
                    'active' => 2,
                ],
                'visits' => [
                    'total' => 3,
                    'today' => 4,
                ],
                'downloads' => [
                    'total' => 5,
                    'today' => 6,
                ],
                'usage' => [
                    'total' => 7,
                    'today' => 8,
                ]
            ]
        ]);
    }
}

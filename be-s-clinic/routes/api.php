<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AutoTagRuleGroupController;
use App\Http\Controllers\Api\WorkflowController;

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt.custom');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('jwt.custom');
    Route::get('me', [AuthController::class, 'me'])->middleware('jwt.custom');
});

Route::middleware('jwt.custom')->group(function () {

    // dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [\App\Http\Controllers\Api\StatisticController::class, 'dashboard']);
    });

    // user
    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'dashboard']);
    });

    // form
    Route::prefix('forms')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\FormController::class, 'index']);
        Route::get('/all', [\App\Http\Controllers\Api\FormController::class, 'all']);
        Route::post('/', [\App\Http\Controllers\Api\FormController::class, 'create']);

        Route::get('/{id}', [\App\Http\Controllers\Api\FormController::class, 'show']);


        Route::put('/update', [\App\Http\Controllers\Api\FormController::class, 'update']);

        Route::post('/assign-to-customers', [\App\Http\Controllers\Api\FormController::class, 'assignToCustomers']);
    });
//
//    // customer
    Route::prefix('customers')->group(function () {
        Route::get('/all', [\App\Http\Controllers\Api\CustomerController::class, 'all']);
    });

    // tags
    Route::prefix('tags')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\TagController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\TagController::class, 'create']);
        Route::get('/all', [\App\Http\Controllers\Api\TagController::class, 'all']);
    });

    // message
    Route::prefix('messages')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\MessageController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\MessageController::class, 'create']);
    });

    Route::get('/auto-tag-rule-groups', [AutoTagRuleGroupController::class, 'index']);
    Route::post('/auto-tag-rule-groups', [AutoTagRuleGroupController::class, 'store']);
    Route::get('/auto-tag-rule-groups/{id}', [AutoTagRuleGroupController::class, 'show']);
    Route::put('/auto-tag-rule-groups/{id}', [AutoTagRuleGroupController::class, 'update']);
    Route::delete('/auto-tag-rule-groups/{id}', [AutoTagRuleGroupController::class, 'destroy']);
    Route::patch('/auto-tag-rule-groups/{id}/status', [AutoTagRuleGroupController::class, 'updateStatus']);

    Route::get('/forms/{formId}/questions', [AutoTagRuleGroupController::class, 'getFormQuestions']);
    Route::get('/questions/{questionId}/options', [AutoTagRuleGroupController::class, 'getQuestionOptions']);
    Route::get('/forms/{formId}/tags-preview', [AutoTagRuleGroupController::class, 'previewTags']);

    // Workflow
    Route::prefix('workflows')->group(function () {
        Route::post('/', [WorkflowController::class, 'createWorkflow']);
        Route::get('/', [WorkflowController::class, 'getWorkflows']);
        Route::post('/start', [WorkflowController::class, 'startWorkflowForUser']);
        Route::get('/user/{userId}/progress', [WorkflowController::class, 'getUserProgress']);
    });
});

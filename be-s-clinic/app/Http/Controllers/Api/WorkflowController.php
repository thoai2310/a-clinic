<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerWorkflowStep;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowService;

class WorkflowController extends Controller
{
    private $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function createWorkflow(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.title' => 'required|string',
            'steps.*.description' => 'required|string',
            'steps.*.button_text' => 'nullable|string',
            'steps.*.completion_message' => 'nullable|string'
        ]);

        $workflow = Workflow::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        foreach ($request->steps as $index => $stepData) {
            WorkflowStep::query()->create([
                'workflow_id' => $workflow->id,
                'step_number' => $index + 1,
                'title' => $stepData['title'],
                'description' => $stepData['description'],
                'button_text' => $stepData['button_text'] ?? 'Hoàn thành',
                'completion_message' => $stepData['completion_message'] ?? null
            ]);
        }

        return response()->json([
            'status' => 'success',
            'workflow' => $workflow->load('steps')
        ]);
    }

    public function startWorkflowForUser(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'workflow_id' => 'required|exists:workflows,id'
        ]);

        $success = $this->workflowService->startWorkflow(
            $request->customer_id,
            $request->workflow_id
        );

        return response()->json([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Workflow started' : 'Failed to start workflow'
        ]);
    }

    public function getUserProgress($customerId)
    {
        $progress = CustomerWorkflowStep::with(['workflow', 'currentStep'])
            ->where('customer_id', $customerId)
            ->get();

        return response()->json([
            'status' => 'success',
            'progress' => $progress
        ]);
    }

    public function getWorkflows()
    {
        $workflows = Workflow::with('steps')->where('status', 1)->get();

        return response()->json([
            'status' => 'success',
            'workflows' => $workflows
        ]);
    }
}

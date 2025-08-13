<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerWorkflowStep extends Model
{
    protected $table = 'customer_workflow_progress';

    protected $fillable = [
        'customer_id', 'workflow_id', 'current_step_id',
        'completed_steps', 'status', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'completed_steps' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function markStepCompleted($stepId)
    {
        $completedSteps = $this->completed_steps ?? [];

        if (!in_array($stepId, $completedSteps)) {
            $completedSteps[] = $stepId;
            $this->completed_steps = $completedSteps;
        }

        return $this;
    }

    public function isStepCompleted($stepId)
    {
        return in_array($stepId, $this->completed_steps ?? []);
    }

    public function getProgressPercentage()
    {
        $totalSteps = $this->workflow->getTotalSteps();
        $completedCount = count($this->completed_steps ?? []);

        return $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100, 2) : 0;
    }
}

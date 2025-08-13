<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $table = 'workflow_steps';
    protected $fillable = [
        'workflow_id', 'step_number', 'title', 'description',
        'button_text', 'completion_message', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function getNextStep()
    {
        return WorkflowStep::query()->where('workflow_id', $this->workflow_id)
            ->where('step_number', '>', $this->step_number)
            ->orderBy('step_number')
            ->first();
    }

    public function getPreviousStep()
    {
        return WorkflowStep::query()->where('workflow_id', $this->workflow_id)
            ->where('step_number', '<', $this->step_number)
            ->orderBy('step_number', 'desc')
            ->first();
    }

    public function isLastStep()
    {
        $maxStepNumber = WorkflowStep::query()->where('workflow_id', $this->workflow_id)
            ->max('step_number');

        return $this->step_number >= $maxStepNumber;
    }

    public function isFirstStep()
    {
        return $this->step_number == 1;
    }
}

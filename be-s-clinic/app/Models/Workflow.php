<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $table = 'workflows';

    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_number');
    }

    public function customerProgress()
    {
        return $this->hasMany(CustomerWorkflowStep::class);
    }

    public function getFirstStep()
    {
        return $this->steps()->first();
    }

    public function getTotalSteps()
    {
        return $this->steps()->count();
    }
}

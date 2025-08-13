<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoTagRuleGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'auto_tag_rule_groups';

    protected $fillable = [
        'name', 'form_id', 'tag_id', 'logic_operator', 'status', 'description'
    ];

    protected $casts = ['status' => 'integer'];

    // Relationships
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function conditions()
    {
        return $this->hasMany(AutoTagRuleCondition::class, 'rule_group_id')->orderBy('order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeForForm($query, $formId)
    {
        return $query->where('form_id', $formId);
    }

    public function evaluateConditions($customerAnswers)
    {
        $conditions = $this->conditions;
        if ($conditions->isEmpty()) {
            return false;
        }

        $results = [];

        foreach ($conditions as $condition) {
            $questionId = $condition->question_id;
            $customerAnswer = $customerAnswers->where('question_id', $questionId)->first();

            if (!$customerAnswer) {
                $results[] = false;
                continue;
            }

            $results[] = $condition->checkCondition($customerAnswer);
        }

        // Apply logic operator
        if ($this->logic_operator === 'OR') {
            return in_array(true, $results);
        } else { // AND
            return !in_array(false, $results);
        }
    }
}

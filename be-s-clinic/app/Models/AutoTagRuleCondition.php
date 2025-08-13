<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoTagRuleCondition extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tag_rule_conditions';

    protected $fillable = [
        'rule_group_id',
        'question_id',
        'question_option_id',
        'condition_type',
        'condition_value',
        'order'
    ];

    protected $casts = [
        'condition_value' => 'array',
    ];

    // Relationships
    public function ruleGroup()
    {
        return $this->belongsTo(AutoTagRuleGroup::class, 'rule_group_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function questionOption()
    {
        return $this->belongsTo(QuestionOption::class);
    }

    public function checkCondition($customerAnswer)
    {
        // Multiple choice question
        if (!empty($this->question_option_id)) {
            return $customerAnswer->question_option_id == $this->question_option_id;
        }

        // Text question
        $answerText = $customerAnswer->answer_text;
        if (empty($answerText)) {
            return false;
        }

        switch ($this->condition_type) {
            case 'equals':
                return strtolower(trim($answerText)) === strtolower(trim($this->condition_value['value'] ?? ''));

            case 'contains':
                return str_contains(strtolower($answerText), strtolower($this->condition_value['value'] ?? ''));

            case 'starts_with':
                return str_starts_with(
                    strtolower(trim($answerText)),
                    strtolower(trim($this->condition_value['value'] ?? ''))
                );

            case 'ends_with':
                return str_ends_with(
                    strtolower(trim($answerText)),
                    strtolower(trim($this->condition_value['value'] ?? ''))
                );

            case 'in_range':
                $numericValue = (float)$answerText;
                $min = $this->condition_value['min'] ?? null;
                $max = $this->condition_value['max'] ?? null;

                if ($min !== null && $numericValue < $min) {
                    return false;
                }
                if ($max !== null && $numericValue > $max) {
                    return false;
                }
                return true;

            default:
                return false;
        }
    }

    public function getConditionDescription()
    {
        if (!empty($this->question_option_id)) {
            return "Chọn: " . ($this->questionOption->text ?? 'N/A');
        }

        switch ($this->condition_type) {
            case 'equals':
                return "Bằng: " . ($this->condition_value['value'] ?? '');
            case 'contains':
                return "Chứa: " . ($this->condition_value['value'] ?? '');
            case 'in_range':
                $min = $this->condition_value['min'] ?? '';
                $max = $this->condition_value['max'] ?? '';
                return "Từ {$min} đến {$max}";
            default:
                return "N/A";
        }
    }
}

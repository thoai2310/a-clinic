<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnswerCustomer extends Model
{
    use SoftDeletes;

    protected $table = 'answer_customer';

    protected $fillable = [
        'id',
        'form_customer_id',
        'form_question_id',
        'question_option_id',
        'answer_text',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relationship with FormCustomer
     */
    public function formCustomer()
    {
        return $this->belongsTo(FormCustomer::class, 'form_customer_id', 'id');
    }

    /**
     * Relationship with FormQuestion
     */
    public function formQuestion()
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id', 'id');
    }

    /**
     * Relationship with QuestionOption
     */
    public function questionOption()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id', 'id');
    }

    /**
     * get answer's text
     */
    public function getDisplayTextAttribute()
    {
        if ($this->question_option_id && $this->questionOption) {
            $text = $this->questionOption->text;
            if ($this->answer_text) {
                $text .= ': ' . $this->answer_text;
            }
            return $text;
        }

        return $this->answer_text;
    }

    /**
     * Scope fot getting answers by customer's form
     */
    public function scopeByFormCustomer($query, $formCustomerId)
    {
        return $query->where('form_customer_id', $formCustomerId);
    }

    /**
     * Scope for getting question's answer
     */
    public function scopeByQuestion($query, $questionId)
    {
        return $query->whereHas('formQuestion.question', function ($q) use ($questionId) {
            $q->where('id', $questionId);
        });
    }

    /**
     * Scope for getting the answer that has text
     */
    public function scopeWithText($query)
    {
        return $query->whereNotNull('answer_text');
    }

    /**
     * Scope for getting the answer that has only option and has not text
     */
    public function scopeOptionOnly($query)
    {
        return $query->whereNotNull('question_option_id')->whereNull('answer_text');
    }
}

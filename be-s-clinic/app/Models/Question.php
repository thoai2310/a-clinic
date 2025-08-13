<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = [
        'id',
        'code',
        'status',
        'type',
        'title',
        'description',
        'has_other_option',
        'usage_count',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hasManyFormQuestion()
    {
        return $this->hasMany(FormQuestion::class, 'question_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'id')->orderBy('order');
    }
}

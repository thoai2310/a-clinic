<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $table = 'question_options';

    protected $fillable = [
        'id',
        'question_id',
        'text',
        'value',
        'is_other',
        'order',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function belongsToQuestion()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}

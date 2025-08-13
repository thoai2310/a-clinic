<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormQuestion extends Model
{
    protected $table = 'form_questions';

    protected $fillable = [
        'id',
        'form_id',
        'question_id',
        'required',
        'order',
        'custom_title',
        'custom_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id')->with('options');
    }
}

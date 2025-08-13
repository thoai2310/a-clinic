<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = 'forms';

    protected $fillable = [
        'id',
        'code',
        'title',
        'description',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hasManyFormQuestion()
    {
        return $this->hasMany(FormQuestion::class, 'form_id', 'id');
    }

    public function formQuestions()
    {
        return $this->hasMany(FormQuestion::class, 'form_id', 'id')
            ->with('question');
    }

    public function assigned()
    {
        return $this->hasMany(FormCustomer::class, 'form_id', 'id');
    }
}

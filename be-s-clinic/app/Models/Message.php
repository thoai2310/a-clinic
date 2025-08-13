<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'id',
        'status',
        'type',
        'title',
        'content',
        'forms',
        'tags',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function hasManyFormQuestion()
    {
        return $this->hasMany(FormQuestion::class, 'form_id', 'id');
    }

    public function assigned()
    {
        return $this->hasMany(FormCustomer::class, 'form_id', 'id');
    }
}

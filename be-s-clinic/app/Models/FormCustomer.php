<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormCustomer extends Model
{
    use SoftDeletes;

    protected $table = 'customer_form';

    protected $fillable = [
        'id',
        'code',
        'form_id',
        'customer_id',
        'status',
        'ip_address',
        'user_agent',
        'submitted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(AnswerCustomer::class, 'form_customer_id', 'id');
    }

    public function answersWithDetails()
    {
        return $this->hasMany(AnswerCustomer::class, 'form_customer_id', 'id')
            ->with(['formQuestion.question', 'questionOption']);
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted' && !is_null($this->submitted_at);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted')->whereNotNull('submitted_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}

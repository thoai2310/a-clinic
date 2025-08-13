<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'id',
        'code',
        'name',
        'phone',
        'email',
        'app_id',
        'source',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function tags()
    {
        return $this->hasMany(CustomerTag::class, 'customer_id', 'id');
    }
}

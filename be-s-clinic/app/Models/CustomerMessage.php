<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMessage extends Model
{
    protected $table = 'customer_message';

    protected $fillable = [
        'id',
        'status',
        'customer_id',
        'message_id',
        'message',
        'last_sent_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tags';

    protected $fillable = [
        'id',
        'status',
        'name',
        'key',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function customers()
    {
        return $this->hasMany(CustomerTag::class, 'tag_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Source extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'publisher_id',
        'name',
        'alias',
        'url',
        'logo'
    ];
}

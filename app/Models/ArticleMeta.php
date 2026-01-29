<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleMeta extends Model
{
    protected $table = 'article_meta';
    public $timestamps = false; 
    public $incrementing = false;

    protected $fillable = [
        'article_id',
        'meta_type',
        'meta_id'
    ];
}

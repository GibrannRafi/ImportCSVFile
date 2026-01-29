<?php

namespace App\Models;

use Illuminate\Cache\TagSet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasUuids, SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'article_id',
        'user_id',
        'publisher_id',
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'status',
        'is_public',
        'show_ads',
        'published_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'article_meta', 'article_id', 'meta_id')
            ->where('meta_type', 'tag');
    }


    public function reporters()
    {
        return $this->belongsToMany(Reporter::class, 'article_meta', 'article_id', 'meta_id')
            ->where('meta_type', 'reporter');
    }
}

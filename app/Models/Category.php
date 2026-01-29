<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = [
        'id',
        'publisher_id',
        'parent_id',
        'name',
        'slug',
        'description'
    ];
    // Relasi ke dirinya sendiri (Induk)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relasi ke anak-anak kategori
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relasi ke banyak Artikel
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}

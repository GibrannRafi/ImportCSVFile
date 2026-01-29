<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Publisher extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'name',
        'settings'
    ];


    protected $casts = [
        'settings' => 'array',
    ];


    public function articles()
    {
        return $this->hasMany(Article::class);
    }


    public function users()
    {
        return $this->hasMany(User::class);
    }
}

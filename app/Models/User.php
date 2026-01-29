<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;
     public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'publisher_id',
        'name',
        'email',
        'password',
        'access_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }


    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}

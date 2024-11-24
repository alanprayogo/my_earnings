<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'category_id', 'id');
    }

    use SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($category) {
            if (!$category->user_id) {
                $category->user_id = auth()->id();
            }
        });
    }
}

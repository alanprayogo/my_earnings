<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Balance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'transaction_id', 'id');
    }

    use SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($balance) {
            if (!$balance->user_id) {
                $balance->user_id = auth()->id();
            }
        });
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
        protected $fillable = ['description', 'category', 'amount', 'group_id', 'paid_by', 'paid_at'];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}


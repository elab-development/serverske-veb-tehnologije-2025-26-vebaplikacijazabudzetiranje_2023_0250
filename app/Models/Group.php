<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
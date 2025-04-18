<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}

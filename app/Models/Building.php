<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = ['branch_id', 'name'];

    public function shifts()
    {
        return $this->hasMany(\App\Models\Shift::class);
    }
}

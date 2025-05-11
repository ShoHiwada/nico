<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NightShiftAssignment extends Model
{
    protected $fillable = ['building_id', 'user_id', 'date'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

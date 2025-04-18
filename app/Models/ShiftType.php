<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftType extends Model
{
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class);
    }
    public function shiftRequests()
    {
        return $this->hasMany(ShiftRequest::class);
    }
}

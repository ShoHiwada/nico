<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftType extends Model
{
    use HasFactory;
    
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

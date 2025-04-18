<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftLog extends Model
{
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}

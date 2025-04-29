<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'date',
        'shift_type_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class);
    }
}

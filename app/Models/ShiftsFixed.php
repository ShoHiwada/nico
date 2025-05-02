<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftsFixed extends Model
{
    protected $table = 'shifts_fixed';

    protected $fillable = [
        'user_id',
        'shift_type_id',
        'weekday',
        'start_date',
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

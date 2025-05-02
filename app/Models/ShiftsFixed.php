<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftsFixed extends Model
{
    protected $table = 'shifts_fixed';

    protected $fillable = [
        'user_id',
        'shift_type_id',
        'week_patterns',
        'start_date',
        'end_date',
        'note',
    ];    

    protected $casts = [
        'week_patterns' => 'array',
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

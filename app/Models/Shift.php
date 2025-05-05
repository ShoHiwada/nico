<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'shift_type_id',
        'status',
        'building',
    ];

    protected $casts = [
        'shift_type_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class);
    }
    public function logs()
    {
        return $this->hasMany(ShiftLog::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{

    protected $table = 'shifts_requests';
    
    protected $fillable = [
        'user_id',
        'month',
        'date',
        'week_patterns',
        'status',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ShiftNight extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'building_id', 'date', 'status', 'shift_type_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

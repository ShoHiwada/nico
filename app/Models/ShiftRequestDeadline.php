<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequestDeadline extends Model
{
    protected $table = 'shifts_request_deadlines';

    protected $fillable = ['month', 'deadline_date'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}

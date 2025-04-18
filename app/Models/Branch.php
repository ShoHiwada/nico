<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;
    
    public function departments()
    {
        return $this->hasMany(Department::class);
    }
    public function buildings()
    {
        return $this->hasMany(Building::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}

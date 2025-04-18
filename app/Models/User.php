<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * 管理者かどうかを判定する
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class);
    }
    public function shiftRequests()
    {
        return $this->hasMany(ShiftRequest::class);
    }
    public function actualRecords()
    {
        return $this->hasMany(ActualRecord::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class, 'admin_user_id');
    }
}

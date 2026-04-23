<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'phone',
        'name',
        'email',
        'password',
        'login_code',
    ];

    protected $hidden = [
        'password',
        'login_code',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
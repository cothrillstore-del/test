<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // Check if user is admin
    public function isAdmin()
    {
        return $this->is_admin === true || $this->role === 'admin';
    }

    // Check if user is editor
    public function isEditor()
    {
        return $this->role === 'editor';
    }

    // Check if user is reviewer
    public function isReviewer()
    {
        return $this->role === 'reviewer';
    }

    // Check if user has backend access
    public function hasBackendAccess()
    {
        return in_array($this->role, ['admin', 'editor', 'reviewer']);
    }

    // Update last login info
    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }
}
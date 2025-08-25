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
        'is_active',
        'is_banned',
        'banned_at',
        'banned_reason',
        'phone',
        'avatar',
        'bio',
        'location',
        'handicap',
        'skill_level',
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
        'banned_at' => 'datetime',
        'is_active' => 'boolean',
        'is_banned' => 'boolean',
        'handicap' => 'decimal:1',
    ];

    // Relationships
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public function equipment()
    {
        return $this->hasMany(UserEquipment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_banned', false);
    }

    public function scopeBanned($query)
    {
        return $query->where('is_banned', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeRecentlyActive($query, $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    public function scopeWithStatistics($query)
    {
        return $query->withCount(['reviews' => function($q) {
                $q->where('status', 'approved');
            }])
            ->withAvg(['reviews' => function($q) {
                $q->where('status', 'approved');
            }], 'rating')
            ->withSum(['reviews' => function($q) {
                $q->where('status', 'approved');
            }], 'helpful_count');
    }

    // Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEditor()
    {
        return $this->role === 'editor';
    }

    public function isReviewer()
    {
        return $this->role === 'reviewer';
    }

    public function hasBackendAccess()
    {
        return in_array($this->role, ['admin', 'editor', 'reviewer']);
    }

    public function ban($reason = null)
    {
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'banned_reason' => $reason,
            'is_active' => false
        ]);

        // Log activity
        $this->logActivity('user_banned', 'User was banned: ' . $reason);
    }

    public function unban()
    {
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'banned_reason' => null,
            'is_active' => true
        ]);

        // Log activity
        $this->logActivity('user_unbanned', 'User ban was lifted');
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
        $this->logActivity('user_activated', 'User account activated');
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
        $this->logActivity('user_deactivated', 'User account deactivated');
    }

    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);
    }

    public function logActivity($action, $description = null, $model = null)
    {
        $this->activities()->create([
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=10b981&color=fff';
    }

    public function getStatusAttribute()
    {
        if ($this->is_banned) return 'banned';
        if (!$this->is_active) return 'inactive';
        if (!$this->email_verified_at) return 'unverified';
        return 'active';
    }

    public function getStatusColorAttribute()
    {
        return [
            'banned' => 'red',
            'inactive' => 'gray',
            'unverified' => 'yellow',
            'active' => 'green'
        ][$this->status];
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}
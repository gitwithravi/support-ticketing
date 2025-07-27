<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Users\UserType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\WelcomeNotification\ReceivesWelcomeNotification;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable, ReceivesWelcomeNotification, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'user_type',
        'password',
        'is_active',
    ];

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
            'user_type' => UserType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * A user belongs to many groups.
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class)
            ->withTimestamps();
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->user_type === UserType::ADMIN;
    }

    /**
     * Check if user can supervise
     */
    public function canSupervise(): bool
    {
        return $this->user_type?->canSupervise() ?? false;
    }

    /**
     * Check if user handles tickets directly
     */
    public function handlesTickets(): bool
    {
        return $this->user_type?->handlesTickets() ?? false;
    }

    /**
     * Scope to filter users by type
     */
    public function scopeOfType($query, UserType $userType)
    {
        return $query->where('user_type', $userType->value);
    }

    /**
     * Scope to get admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_type', UserType::ADMIN->value);
    }

    /**
     * Scope to get agent users
     */
    public function scopeAgents($query)
    {
        return $query->where('user_type', UserType::AGENT->value);
    }

    /**
     * Scope to get supervisor users
     */
    public function scopeSupervisors($query)
    {
        return $query->whereIn('user_type', [
            UserType::CATEGORY_SUPERVISOR->value,
            UserType::BUILDING_SUPERVISOR->value,
        ]);
    }
}

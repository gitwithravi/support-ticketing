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
use Spatie\Permission\Traits\HasRoles;
use Spatie\WelcomeNotification\ReceivesWelcomeNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUlids, Notifiable, ReceivesWelcomeNotification;

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
        'prf_api_access_key',
        'prf_api_access_secret',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'prf_api_access_key',
        'prf_api_access_secret',
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
            'prf_api_access_key' => 'encrypted',
            'prf_api_access_secret' => 'encrypted',
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

    /**
     * Get categories that this user supervises
     */
    public function supervisedCategories()
    {
        return $this->hasMany(Category::class, 'category_supervisor_id');
    }

    /**
     * Get buildings that this user supervises
     */
    public function supervisedBuildings()
    {
        return $this->belongsToMany(Building::class, 'building_supervisor')
            ->withTimestamps();
    }

    /**
     * Check if user is a category supervisor
     */
    public function isCategorySupervisor(): bool
    {
        return $this->user_type === UserType::CATEGORY_SUPERVISOR;
    }

    /**
     * Check if user is a building supervisor
     */
    public function isBuildingSupervisor(): bool
    {
        return $this->user_type === UserType::BUILDING_SUPERVISOR;
    }

    /**
     * Check if user is an agent
     */
    public function isAgent(): bool
    {
        return $this->user_type === UserType::AGENT;
    }

    /**
     * Check if user has PRF API credentials configured
     */
    public function hasPrfApiCredentials(): bool
    {
        return ! empty($this->prf_api_access_key) && ! empty($this->prf_api_access_secret);
    }

    /**
     * Get PRF API credentials for this user
     *
     * @return array{access_key: string|null, access_secret: string|null}
     */
    public function getPrfApiCredentials(): array
    {
        return [
            'access_key' => $this->prf_api_access_key,
            'access_secret' => $this->prf_api_access_secret,
        ];
    }

    /**
     * Set PRF API credentials for this user
     */
    public function setPrfApiCredentials(?string $accessKey, ?string $accessSecret): void
    {
        $this->prf_api_access_key = $accessKey;
        $this->prf_api_access_secret = $accessSecret;
    }
}

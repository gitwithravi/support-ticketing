<?php

namespace App\Models;

use App\Filament\AvatarProviders\GravatarProvider;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Carbon;
use Spatie\Tags\HasTags;

class Client extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, HasNotes, HasTags, HasUlids, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'unique_id',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'locale',
        'timezone',
        'is_active',
        'otp_code',
        'otp_expires_at',
        'google_id',
        'provider',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * A client can have many tickets.
     *
     * @return HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    /**
     * A client belongs to many groups.
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class)
            ->withTimestamps();
    }

    /**
     * Retrieve the client's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return app(GravatarProvider::class)->get($this);
    }

    /**
     * Generate and save a new OTP code for the client.
     */
    public function generateOtp(): string
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        return $otp;
    }

    /**
     * Verify the provided OTP code.
     */
    public function verifyOtp(string $otp): bool
    {
        if (!$this->otp_code || !$this->otp_expires_at) {
            return false;
        }

        if ($this->otp_expires_at->isPast()) {
            return false;
        }

        return $this->otp_code === $otp;
    }

    /**
     * Clear the OTP code and mark email as verified.
     */
    public function markEmailAsVerified(): void
    {
        $this->update([
            'email_verified_at' => Carbon::now(),
            'otp_code' => null,
            'otp_expires_at' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Check if the client's email is verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if the client is using OAuth authentication.
     */
    public function isOAuthUser(): bool
    {
        return $this->provider !== 'local';
    }

    /**
     * Check if the client is using Google OAuth.
     */
    public function isGoogleUser(): bool
    {
        return $this->provider === 'google';
    }

    /**
     * Check if the client has a password set.
     */
    public function hasPassword(): bool
    {
        return !is_null($this->password);
    }

    /**
     * Check if the client has Google account linked.
     */
    public function hasGoogleAccount(): bool
    {
        return !is_null($this->google_id);
    }

}

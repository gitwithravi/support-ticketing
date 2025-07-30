<?php

namespace App\Models;

use App\Enums\Buildings\BuildingType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'building_type',
        'floors',
        'total_rooms',
        'construction_year',
        'is_active',
        'contact_info',
        'latitude',
        'longitude',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'building_type' => BuildingType::class,
            'floors' => 'integer',
            'total_rooms' => 'integer',
            'construction_year' => 'integer',
            'is_active' => 'boolean',
            'contact_info' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * A building has many supervisors (users).
     */
    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'building_supervisor')
            ->withTimestamps();
    }

    /**
     * Legacy method for backward compatibility - returns the first supervisor
     *
     * @deprecated Use supervisors() instead
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'building_supervisor_id');
    }

    /**
     * A building has many tickets.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Scope to get only active buildings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by building type.
     */
    public function scopeOfType($query, BuildingType $buildingType)
    {
        return $query->where('building_type', $buildingType->value);
    }

    /**
     * Scope to order by building code.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('code')->orderBy('name');
    }

    /**
     * Get the building's full identifier (code + name).
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Check if building has location coordinates.
     */
    public function hasLocation(): bool
    {
        return ! is_null($this->latitude) && ! is_null($this->longitude);
    }

    /**
     * Get building age in years.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->construction_year ? now()->year - $this->construction_year : null;
    }
}

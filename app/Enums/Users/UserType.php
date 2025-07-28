<?php

namespace App\Enums\Users;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasColor, HasIcon, HasLabel
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case CATEGORY_SUPERVISOR = 'category_supervisor';
    case BUILDING_SUPERVISOR = 'building_supervisor';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::AGENT => 'Agent',
            self::CATEGORY_SUPERVISOR => 'Category Supervisor',
            self::BUILDING_SUPERVISOR => 'Building Supervisor',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::AGENT => 'primary',
            self::CATEGORY_SUPERVISOR => 'warning',
            self::BUILDING_SUPERVISOR => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ADMIN => 'heroicon-o-shield-check',
            self::AGENT => 'heroicon-o-user',
            self::CATEGORY_SUPERVISOR => 'heroicon-o-folder-open',
            self::BUILDING_SUPERVISOR => 'heroicon-o-building-office',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Full system access and user management',
            self::AGENT => 'Handle tickets and customer support',
            self::CATEGORY_SUPERVISOR => 'Supervise specific categories and their tickets',
            self::BUILDING_SUPERVISOR => 'Manage building-specific maintenance issues',
        };
    }

    /**
     * Get all user types as an array for forms
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    /**
     * Check if user type has administrative privileges
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if user type can supervise
     */
    public function canSupervise(): bool
    {
        return in_array($this, [
            self::ADMIN,
            self::CATEGORY_SUPERVISOR,
            self::BUILDING_SUPERVISOR,
        ]);
    }

    /**
     * Check if user type handles tickets directly
     */
    public function handlesTickets(): bool
    {
        return in_array($this, [
            self::AGENT,
            self::CATEGORY_SUPERVISOR,
            self::BUILDING_SUPERVISOR,
        ]);
    }
}

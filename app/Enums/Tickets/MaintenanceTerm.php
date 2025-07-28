<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MaintenanceTerm: string implements HasColor, HasLabel
{
    case MAINTENANCE = 'maintenance';
    case DAMAGES = 'damages';
    case BREAKAGES = 'breakages';
    case MISSING = 'missing';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MAINTENANCE => 'info',
            self::DAMAGES => 'warning',
            self::BREAKAGES => 'danger',
            self::MISSING => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MAINTENANCE => 'Maintenance',
            self::DAMAGES => 'Damages',
            self::BREAKAGES => 'Breakages',
            self::MISSING => 'Missing',
        };
    }
}
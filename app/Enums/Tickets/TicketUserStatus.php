<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketUserStatus: string implements HasLabel, HasColor, HasIcon
{
    case OPEN = 'open';
    case CLOSE = 'close';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::CLOSE => 'Close',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::OPEN => 'warning',
            self::CLOSE => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::OPEN => 'heroicon-o-lock-open',
            self::CLOSE => 'heroicon-o-lock-closed',
        };
    }

    /**
     * Get all statuses as an array for forms
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
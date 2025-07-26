<?php

namespace App\Enums\Buildings;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BuildingType: string implements HasLabel, HasColor, HasIcon
{
    case ACADEMIC_BLOCK = 'academic_block';
    case BOYS_HOSTEL = 'boys_hostel';
    case GIRLS_HOSTEL = 'girls_hostel';
    case STAFF_QUARTERS = 'staff_quarters';
    case PARKING = 'parking';
    case OTHERS = 'others';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACADEMIC_BLOCK => 'Academic Block',
            self::BOYS_HOSTEL => 'Boys Hostel',
            self::GIRLS_HOSTEL => 'Girls Hostel',
            self::STAFF_QUARTERS => 'Staff Quarters',
            self::PARKING => 'Parking',
            self::OTHERS => 'Others',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACADEMIC_BLOCK => 'primary',
            self::BOYS_HOSTEL => 'blue',
            self::GIRLS_HOSTEL => 'pink',
            self::STAFF_QUARTERS => 'success',
            self::PARKING => 'gray',
            self::OTHERS => 'slate',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACADEMIC_BLOCK => 'heroicon-o-academic-cap',
            self::BOYS_HOSTEL => 'heroicon-o-home',
            self::GIRLS_HOSTEL => 'heroicon-o-home-modern',
            self::STAFF_QUARTERS => 'heroicon-o-building-office',
            self::PARKING => 'heroicon-o-truck',
            self::OTHERS => 'heroicon-o-building-office-2',
        };
    }

    /**
     * Get all building types as an array for forms
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
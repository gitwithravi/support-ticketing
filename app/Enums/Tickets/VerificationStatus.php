<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum VerificationStatus: string implements HasColor, HasLabel
{
    case WORK_DONE = 'work_done';
    case WORK_NOT_DONE = 'work_not_done';
    case WORK_IN_PROGRESS = 'work_in_progress';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WORK_DONE => 'success',
            self::WORK_NOT_DONE => 'danger',
            self::WORK_IN_PROGRESS => 'warning',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WORK_DONE => 'Work Done',
            self::WORK_NOT_DONE => 'Work Not Done',
            self::WORK_IN_PROGRESS => 'Work In Progress',
        };
    }
}

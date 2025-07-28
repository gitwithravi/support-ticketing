<?php

namespace App\Enums\MaterialRequests;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MaterialRequestStatus: string implements HasColor, HasLabel
{
    case CREATED = 'created';
    case ACKNOWLEDGED = 'acknowledged';
    case PRF_CREATED = 'prf_created';
    case PRF_PROCESSED = 'prf_processed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CREATED => 'info',
            self::ACKNOWLEDGED => 'warning',
            self::PRF_CREATED => 'primary',
            self::PRF_PROCESSED => 'success',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::ACKNOWLEDGED => 'Acknowledged',
            self::PRF_CREATED => 'PRF Created',
            self::PRF_PROCESSED => 'PRF Processed',
        };
    }
}

<?php

namespace App\Filament\Resources\BreakageResource\Pages;

use App\Filament\Resources\BreakageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBreakage extends ViewRecord
{
    protected static string $resource = BreakageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
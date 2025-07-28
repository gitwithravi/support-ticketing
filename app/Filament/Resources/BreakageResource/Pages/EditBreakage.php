<?php

namespace App\Filament\Resources\BreakageResource\Pages;

use App\Filament\Resources\BreakageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBreakage extends EditRecord
{
    protected static string $resource = BreakageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
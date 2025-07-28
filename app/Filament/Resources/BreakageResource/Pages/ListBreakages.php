<?php

namespace App\Filament\Resources\BreakageResource\Pages;

use App\Filament\Resources\BreakageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBreakages extends ListRecords
{
    protected static string $resource = BreakageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }
}
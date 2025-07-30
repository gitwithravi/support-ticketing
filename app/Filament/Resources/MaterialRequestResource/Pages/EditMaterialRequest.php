<?php

namespace App\Filament\Resources\MaterialRequestResource\Pages;

use App\Filament\Resources\MaterialRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialRequest extends EditRecord
{
    protected static string $resource = MaterialRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Eager load relationships to avoid lazy loading issues
        $this->record->load(['createdBy', 'processedBy', 'items']);

        return $data;
    }
}

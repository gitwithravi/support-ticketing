<?php

namespace App\Filament\Resources\MaterialRequestResource\Pages;

use App\Filament\Resources\MaterialRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialRequest extends ViewRecord
{
    protected static string $resource = MaterialRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Eager load relationships to avoid lazy loading issues
        $this->record->load(['createdBy', 'processedBy', 'items']);
        
        return $data;
    }
}
